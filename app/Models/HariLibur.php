<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class HariLibur extends Model
{
    protected $fillable = [
        'tahun',
        'nama',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan',
        'warna',
        'is_active',
    ];

    protected $casts = [
        'tahun'           => 'integer',
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'is_active'       => 'boolean',
    ];

    /**
     * Compute Gantt band position for this holiday within its year.
     */
    public function getBandPositionAttribute(): ?array
    {
        if (!$this->tanggal_mulai || !$this->tanggal_selesai) {
            return null;
        }

        return Pelatihan::calculatePosition($this->tanggal_mulai, $this->tanggal_selesai, $this->tahun);
    }

    /**
     * Display period string in Indonesian short format.
     */
    public function getPeriodeDisplayAttribute(): string
    {
        if (!$this->tanggal_mulai || !$this->tanggal_selesai) {
            return '-';
        }

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $m1 = $months[$this->tanggal_mulai->month] ?? '';
        $m2 = $months[$this->tanggal_selesai->month] ?? '';

        if ($this->tanggal_mulai->equalTo($this->tanggal_selesai)) {
            return $this->tanggal_mulai->day . ' ' . $m1;
        }

        if ($this->tanggal_mulai->month === $this->tanggal_selesai->month) {
            return $this->tanggal_mulai->day . ' - ' . $this->tanggal_selesai->day . ' ' . $m1;
        }

        return $this->tanggal_mulai->day . ' ' . $m1 . ' - ' . $this->tanggal_selesai->day . ' ' . $m2;
    }
}
