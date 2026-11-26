<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Pelatihan extends Model
{
    protected $fillable = [
        'tahun',
        'kategori',
        'kode_pelatihan',
        'nama_pelatihan',
        'angkatan',
        'jumlah_peserta',
        'peserta_terdaftar',
        'peserta_lulus',
        'durasi',
        'jam_pelajaran',
        'tanggal_mulai',
        'tanggal_selesai',
        'sumber_dana',
        'tempat_pelaksanaan',
        'penyelenggara',
        'pic_nama',
        'pic_kontak',
        'link_pendaftaran',
        'deskripsi',
        'metode',
        'jadwal_tahapan',
        'status',
        'auto_status',
        'is_published',
        'seleksi_hari',
    ];

    protected $casts = [
        'tahun'             => 'integer',
        'jumlah_peserta'    => 'integer',
        'peserta_terdaftar' => 'integer',
        'peserta_lulus'     => 'integer',
        'jam_pelajaran'     => 'integer',
        'tanggal_mulai'     => 'date',
        'tanggal_selesai'   => 'date',
        'auto_status'       => 'boolean',
        'is_published'      => 'boolean',
        'seleksi_hari'      => 'integer',
        'jadwal_tahapan'    => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (Pelatihan $pelatihan) {
            if (empty($pelatihan->tahun) && $pelatihan->tanggal_mulai) {
                $pelatihan->tahun = (int)$pelatihan->tanggal_mulai->year;
            }
            if (empty($pelatihan->tahun)) {
                $pelatihan->tahun = (int)Carbon::now()->year;
            }
            if ($pelatihan->seleksi_hari === null) {
                $pelatihan->seleksi_hari = 7;
            }
        });
    }

    /**
     * Scope only published trainings visible on the public dashboard.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * Detect overlapping holiday periods for this training or its stages.
     */
    public function getHolidayConflicts($hariLiburs): array
    {
        if (empty($hariLiburs)) {
            return [];
        }

        $ranges = [];
        if (!empty($this->jadwal_tahapan) && is_array($this->jadwal_tahapan)) {
            foreach ($this->jadwal_tahapan as $tahap) {
                if (!empty($tahap['mulai']) && !empty($tahap['selesai'])) {
                    $ranges[] = [
                        'label' => $tahap['tahap'] ?? 'Tahap',
                        'start' => Carbon::parse($tahap['mulai'])->startOfDay(),
                        'end'   => Carbon::parse($tahap['selesai'])->startOfDay(),
                    ];
                }
            }
        }

        if (empty($ranges) && $this->tanggal_mulai && $this->tanggal_selesai) {
            $ranges[] = [
                'label' => $this->metode ?: 'Jadwal',
                'start' => $this->tanggal_mulai->copy()->startOfDay(),
                'end'   => $this->tanggal_selesai->copy()->startOfDay(),
            ];
        }

        $conflicts = [];
        foreach ($hariLiburs as $libur) {
            if (!$libur->tanggal_mulai || !$libur->tanggal_selesai) {
                continue;
            }
            $hStart = $libur->tanggal_mulai->copy()->startOfDay();
            $hEnd   = $libur->tanggal_selesai->copy()->startOfDay();

            foreach ($ranges as $r) {
                if ($r['start']->lte($hEnd) && $r['end']->gte($hStart)) {
                    $conflicts[] = "{$r['label']} bentrok {$libur->nama} ({$libur->periode_display})";
                }
            }
        }

        return array_values(array_unique($conflicts));
    }

    /**
     * Scope trainings occurring within the given calendar year (including cross-year).
     */
    public function scopeForYear(Builder $query, int $year): Builder
    {
        $yearStart = sprintf('%04d-01-01', $year);
        $yearEnd   = sprintf('%04d-12-31', $year);

        return $query->where(function (Builder $q) use ($year, $yearStart, $yearEnd) {
            $q->where('tahun', $year)
              ->orWhere(function (Builder $sub) use ($yearStart, $yearEnd) {
                  $sub->whereDate('tanggal_mulai', '<=', $yearEnd)
                      ->whereDate('tanggal_selesai', '>=', $yearStart);
              });
        });
    }

    /**
     * List distinct available years from data plus current and next year.
     */
    public static function getAvailableYears(): array
    {
        $currentYear = (int)Carbon::now()->year;
        $years       = collect([$currentYear, $currentYear + 1]);

        try {
            $dbYears = static::query()
                ->select('tahun')
                ->distinct()
                ->pluck('tahun')
                ->filter()
                ->map(fn($y) => (int)$y);
            $years = $years->merge($dbYears);

            $holidayYears = HariLibur::query()
                ->select('tahun')
                ->distinct()
                ->pluck('tahun')
                ->filter()
                ->map(fn($y) => (int)$y);
            $years = $years->merge($holidayYears);
        } catch (\Throwable $e) {
            // Fallback during initial setup
        }

        return $years->unique()->sortDesc()->values()->all();
    }

    /**
     * Get computed status based on current date and configurable seleksi_hari.
     */
    public function getComputedStatusAttribute(): string
    {
        if (!$this->auto_status) {
            return $this->status ?: 'Belum Dimulai';
        }

        $today = Carbon::today();
        $start = $this->tanggal_mulai;
        $end   = $this->tanggal_selesai;

        if (!$start || !$end) {
            return 'Belum Dimulai';
        }

        $seleksiDays  = max(0, (int)($this->seleksi_hari ?? 7));
        $seleksiStart = $start->copy()->subDays($seleksiDays);

        if ($today->gt($end)) {
            return 'Selesai';
        } elseif ($today->gte($start) && $today->lte($end)) {
            return 'Sedang Berlangsung';
        } elseif ($seleksiDays > 0 && $today->gte($seleksiStart) && $today->lt($start)) {
            return 'Tahap Seleksi';
        }

        return 'Belum Dimulai';
    }

    /**
     * Format period for display (e.g., "7 Okt - 11 Nov").
     */
    public function getPeriodeDisplayAttribute(): string
    {
        if (!$this->tanggal_mulai || !$this->tanggal_selesai) {
            return '-';
        }

        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agt',
            9 => 'Sept', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $start = $this->tanggal_mulai->day . ' ' . ($months[$this->tanggal_mulai->month] ?? '');
        $end   = $this->tanggal_selesai->day . ' ' . ($months[$this->tanggal_selesai->month] ?? '');

        if ($this->tanggal_mulai->year !== $this->tanggal_selesai->year) {
            $start .= ' ' . $this->tanggal_mulai->year;
            $end   .= ' ' . $this->tanggal_selesai->year;
        }

        return $start . ' - ' . $end;
    }

    /**
     * Get duration display string (e.g. "26 hr").
     */
    public function getDurasiDisplayAttribute(): string
    {
        if (!empty($this->durasi)) {
            return str_contains($this->durasi, 'hr') ? $this->durasi : $this->durasi . ' hr';
        }

        if ($this->tanggal_mulai && $this->tanggal_selesai) {
            $days = $this->tanggal_mulai->diffInDays($this->tanggal_selesai) + 1;
            return $days . ' hr';
        }

        return '-';
    }

    /**
     * Realization percentage against target participants.
     */
    public function getPersentaseRealisasiAttribute(): float
    {
        if ($this->jumlah_peserta <= 0) {
            return 0.0;
        }

        return round(min(100, ($this->peserta_lulus / $this->jumlah_peserta) * 100), 1);
    }

    /**
     * Get badge color class based on status.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->computed_status) {
            'Belum Dimulai'      => 'status-belum',
            'Sedang Berlangsung' => 'status-berlangsung',
            'Tahap Seleksi'      => 'status-seleksi',
            'Selesai'            => 'status-selesai',
            default              => 'status-belum',
        };
    }

    /**
     * Resolve method colors from database master_metodes first, with keyword fallback.
     */
    public static function getMetodeColor(string $metode): string
    {
        $normalized = strtolower(trim($metode));

        foreach (MasterMetode::getActiveCached() as $master) {
            $masterNorm = strtolower(trim($master->nama));
            if ($masterNorm === $normalized || str_contains($normalized, $masterNorm) || str_contains($masterNorm, $normalized)) {
                return $master->kode_warna;
            }
        }

        if (str_contains($normalized, 'libur') || str_contains($normalized, 'nyepi') || str_contains($normalized, 'idul')) {
            return '#b91c1c';
        } elseif (str_contains($normalized, 'daring') || str_contains($normalized, 'online')) {
            return '#38bdf8';
        } elseif (str_contains($normalized, 'klasikal')) {
            return '#fb923c';
        } elseif (str_contains($normalized, 'pkl') || str_contains($normalized, 'bimbingan')) {
            return '#c084fc';
        } elseif (str_contains($normalized, 'seminar') || str_contains($normalized, 'penutupan')) {
            return '#facc15';
        } elseif (str_contains($normalized, 'mooc') || str_contains($normalized, 'mandiri')) {
            return '#4ade80';
        } elseif (str_contains($normalized, 'assessment') || str_contains($normalized, 'asesmen')) {
            return '#f472b6';
        }

        return '#60a5fa';
    }

    /**
     * Resolve method text color from database master_metodes first, with keyword fallback.
     */
    public static function getMetodeTextColor(string $metode): string
    {
        $normalized = strtolower(trim($metode));

        foreach (MasterMetode::getActiveCached() as $master) {
            $masterNorm = strtolower(trim($master->nama));
            if ($masterNorm === $normalized || str_contains($normalized, $masterNorm) || str_contains($masterNorm, $normalized)) {
                return $master->warna_teks;
            }
        }

        if (str_contains($normalized, 'seminar') || str_contains($normalized, 'penutupan')) {
            return '#713f12';
        }

        return '#ffffff';
    }

    /**
     * Calculate horizontal percentage position for a date range, optionally clamped to $targetYear.
     */
    public static function calculatePosition(Carbon $mulai, Carbon $selesai, ?int $targetYear = null): ?array
    {
        $start = $mulai->copy()->startOfDay();
        $end   = $selesai->copy()->startOfDay();

        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        if ($targetYear !== null) {
            $yearStart = Carbon::create($targetYear, 1, 1)->startOfDay();
            $yearEnd   = Carbon::create($targetYear, 12, 31)->startOfDay();

            if ($end->lt($yearStart) || $start->gt($yearEnd)) {
                return null;
            }

            if ($start->lt($yearStart)) {
                $start = $yearStart;
            }
            if ($end->gt($yearEnd)) {
                $end = $yearEnd;
            }
        }

        $monthWidth = 100.0 / 12.0;

        $startMonth       = (int)$start->month;
        $startDaysInMonth = (int)$start->daysInMonth;
        $startDay         = (int)$start->day;

        $left = (($startMonth - 1) * $monthWidth) + (($startDay - 1) / $startDaysInMonth) * $monthWidth;

        $endMonth       = (int)$end->month;
        $endDaysInMonth = (int)$end->daysInMonth;
        $endDay         = (int)$end->day;

        $right = (($endMonth - 1) * $monthWidth) + ($endDay / $endDaysInMonth) * $monthWidth;

        $width = max(2.4, $right - $left);
        $left  = max(0.0, min(97.6, $left));
        $width = min(100.0 - $left, $width);

        return [
            'left'  => number_format($left, 2, '.', '') . '%',
            'width' => number_format($width, 2, '.', '') . '%',
        ];
    }

    /**
     * Calculate horizontal percentage position for today's vertical marker line if in $year.
     */
    public static function calculateTodayMarkerPosition(int $year): ?string
    {
        $today = Carbon::today();
        if ((int)$today->year !== $year) {
            return null;
        }

        $monthWidth = 100.0 / 12.0;
        $left       = (($today->month - 1) * $monthWidth) + (($today->day - 0.5) / $today->daysInMonth) * $monthWidth;
        $left       = max(0.0, min(99.8, $left));

        return number_format($left, 2, '.', '') . '%';
    }

    /**
     * Get computed Gantt timeline bars for this training's primary year.
     */
    public function getTimelineBarsAttribute(): array
    {
        $year = $this->tahun ?: ($this->tanggal_mulai?->year ?? (int)Carbon::now()->year);
        return $this->getTimelineBarsForYear((int)$year);
    }

    /**
     * Get computed Gantt timeline bars clamped to a specific calendar year.
     */
    public function getTimelineBarsForYear(int $year): array
    {
        $bars = [];

        if (!empty($this->jadwal_tahapan) && is_array($this->jadwal_tahapan)) {
            foreach ($this->jadwal_tahapan as $tahap) {
                $metode  = $tahap['tahap'] ?? $tahap['metode'] ?? $this->metode ?? 'Klasikal';
                $mulai   = isset($tahap['mulai']) ? Carbon::parse($tahap['mulai']) : null;
                $selesai = isset($tahap['selesai']) ? Carbon::parse($tahap['selesai']) : null;

                if ($mulai && $selesai) {
                    $pos = self::calculatePosition($mulai, $selesai, $year);
                    if ($pos === null) {
                        continue;
                    }

                    $bars[] = [
                        'metode'    => $metode,
                        'color'     => self::getMetodeColor($metode),
                        'textColor' => self::getMetodeTextColor($metode),
                        'mulai'     => $mulai,
                        'selesai'   => $selesai,
                        'label'     => $mulai->day . ' ' . $selesai->day,
                        'left'      => $pos['left'],
                        'width'     => $pos['width'],
                        'tooltip'   => $metode . ' (' . $mulai->format('d M Y') . ' - ' . $selesai->format('d M Y') . ')',
                    ];
                }
            }
        }

        if (empty($bars) && $this->tanggal_mulai && $this->tanggal_selesai) {
            $pos = self::calculatePosition($this->tanggal_mulai, $this->tanggal_selesai, $year);
            if ($pos !== null) {
                $metode = $this->metode ?: 'Klasikal';
                $bars[] = [
                    'metode'    => $metode,
                    'color'     => self::getMetodeColor($metode),
                    'textColor' => self::getMetodeTextColor($metode),
                    'mulai'     => $this->tanggal_mulai,
                    'selesai'   => $this->tanggal_selesai,
                    'label'     => $this->tanggal_mulai->day . ' ' . $this->tanggal_selesai->day,
                    'left'      => $pos['left'],
                    'width'     => $pos['width'],
                    'tooltip'   => $metode . ' (' . $this->periode_display . ')',
                ];
            }
        }

        return $bars;
    }
}
