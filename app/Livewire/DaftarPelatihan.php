<?php

namespace App\Livewire;

use App\Models\AppSetting;
use App\Models\HariLibur;
use App\Models\MasterKategori;
use App\Models\MasterMetode;
use App\Models\Pelatihan;
use App\Services\PelatihanExportService;
use Carbon\Carbon;
use Livewire\Component;

class DaftarPelatihan extends Component
{
    public int $tahun;
    public string $search = '';
    public string $filterStatus = '';
    public string $filterKategori = '';
    public string $filterSumberDana = '';
    public string $filterTempat = '';
    public string $lastUpdated = '';
    public ?int $detailId = null;
    public int $page = 1;
    public int $perPage = 10;

    public function mount(): void
    {
        $available          = Pelatihan::getAvailableYears();
        $currentYear        = (int)Carbon::now()->year;
        $this->tahun        = in_array($currentYear, $available, true) ? $currentYear : ($available[0] ?? $currentYear);
        $this->lastUpdated  = Carbon::now()->format('H:i:s');
    }

    public function refresh(): void
    {
        $this->lastUpdated = Carbon::now()->format('H:i:s');
    }

    public function resetFilters(): void
    {
        $this->search           = '';
        $this->filterStatus     = '';
        $this->filterKategori   = '';
        $this->filterSumberDana = '';
        $this->filterTempat     = '';
        $this->page             = 1;
    }

    public function updated($property): void
    {
        if (in_array($property, ['tahun', 'search', 'filterStatus', 'filterKategori', 'filterSumberDana', 'filterTempat', 'perPage'], true)) {
            $this->page = 1;
        }
    }

    public function prevPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    public function nextPage(int $lastPage): void
    {
        if ($this->page < $lastPage) {
            $this->page++;
        }
    }

    public function gotoPage(int $page): void
    {
        $this->page = max(1, $page);
    }

    public function openDetail(int $id): void
    {
        $this->detailId = $id;
    }

    public function closeDetail(): void
    {
        $this->detailId = null;
    }

    public function getDetailPelatihanProperty(): ?Pelatihan
    {
        if (!$this->detailId) {
            return null;
        }

        return Pelatihan::published()->find($this->detailId);
    }

    public function exportExcel()
    {
        $filtered = $this->getFilteredCollection(
            Pelatihan::published()->forYear($this->tahun)->orderBy('tanggal_mulai')->get()
        );

        $path     = PelatihanExportService::exportToXlsx($filtered, $this->tahun);
        $filename = "Jadwal_Pelatihan_PPSDMK_{$this->tahun}.xlsx";

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }

    protected function getFilteredCollection($yearRecords)
    {
        return $yearRecords->filter(function (Pelatihan $p) {
            if ($this->search !== '') {
                $needle = strtolower($this->search);
                $match  = str_contains(strtolower($p->nama_pelatihan), $needle)
                    || str_contains(strtolower((string)$p->kode_pelatihan), $needle)
                    || str_contains(strtolower((string)$p->angkatan), $needle)
                    || str_contains(strtolower((string)$p->penyelenggara), $needle);
                if (!$match) {
                    return false;
                }
            }

            if ($this->filterKategori !== '' && strtoupper((string)$p->kategori) !== strtoupper($this->filterKategori)) {
                return false;
            }

            if ($this->filterSumberDana !== '' && strtoupper((string)$p->sumber_dana) !== strtoupper($this->filterSumberDana)) {
                return false;
            }

            if ($this->filterTempat !== '' && strtoupper((string)$p->tempat_pelaksanaan) !== strtoupper($this->filterTempat)) {
                return false;
            }

            if ($this->filterStatus !== '' && $p->computed_status !== $this->filterStatus) {
                return false;
            }

            return true;
        })->values();
    }

    public function render()
    {
        $this->lastUpdated = Carbon::now()->format('H:i:s');

        $yearRecords = Pelatihan::published()->forYear($this->tahun)->orderBy('tanggal_mulai')->get();

        $statusCounts = [
            'Belum Dimulai'      => 0,
            'Sedang Berlangsung' => 0,
            'Tahap Seleksi'      => 0,
            'Selesai'            => 0,
        ];

        foreach ($yearRecords as $p) {
            $status = $p->computed_status;
            if (isset($statusCounts[$status])) {
                $statusCounts[$status]++;
            }
        }

        $pelatihans = $this->getFilteredCollection($yearRecords);

        $totalFiltered = $pelatihans->count();
        $lastPage      = max(1, (int)ceil($totalFiltered / max(1, $this->perPage)));
        $currentPage   = min(max(1, $this->page), $lastPage);
        $this->page    = $currentPage;
        $paginatedPelatihans = $pelatihans->forPage($currentPage, $this->perPage);

        $kalenderGroups = $pelatihans
            ->sortBy('id')
            ->groupBy(fn($item) => strtoupper($item->kategori ?: 'PELATIHAN FUNGSIONAL'));

        $kategorisFromMaster = collect(MasterKategori::getActiveNames());
        $kategorisFromData   = $yearRecords->pluck('kategori')->filter()->map(fn($k) => strtoupper($k));
        $availableKategoris  = $kategorisFromMaster->merge($kategorisFromData)->unique()->values()->all();

        $availableSumberDana = $yearRecords->pluck('sumber_dana')->filter()->map(fn($s) => strtoupper($s))->unique()->sort()->values()->all();
        $availableTempat     = $yearRecords->pluck('tempat_pelaksanaan')->filter()->map(fn($t) => strtoupper($t))->unique()->sort()->values()->all();

        $hariLiburs = HariLibur::where('tahun', $this->tahun)
            ->where('is_active', true)
            ->orderBy('tanggal_mulai')
            ->get();

        $masterMetodes = MasterMetode::getActiveCached();

        return view('livewire.daftar-pelatihan', [
            'pelatihans'          => $paginatedPelatihans,
            'totalFiltered'       => $totalFiltered,
            'currentPage'         => $currentPage,
            'lastPage'            => $lastPage,
            'kalenderGroups'      => $kalenderGroups,
            'availableYears'      => Pelatihan::getAvailableYears(),
            'availableKategoris'  => $availableKategoris,
            'availableSumberDana' => $availableSumberDana,
            'availableTempat'     => $availableTempat,
            'statusCounts'        => $statusCounts,
            'totalTarget'         => $pelatihans->sum('jumlah_peserta'),
            'totalTerdaftar'      => $pelatihans->sum('peserta_terdaftar'),
            'totalLulus'          => $pelatihans->sum('peserta_lulus'),
            'hariLiburs'          => $hariLiburs,
            'masterMetodes'       => $masterMetodes,
            'todayMarkerLeft'     => Pelatihan::calculateTodayMarkerPosition($this->tahun),
            'detailPelatihan'     => $this->detailPelatihan,
            'showPublicKalender'  => AppSetting::getBool('show_public_kalender', true),
        ]);
    }
}
