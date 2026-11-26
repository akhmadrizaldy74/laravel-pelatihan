<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\ManagesExcelImportExport;
use App\Livewire\Admin\Concerns\ManagesMasterData;
use App\Livewire\Admin\Concerns\ManagesPelatihan;
use App\Livewire\Admin\Concerns\ManagesUsersAndSecurity;
use App\Models\AppSetting;
use App\Models\AuditLog;
use App\Models\HariLibur;
use App\Models\MasterKategori;
use App\Models\MasterMetode;
use App\Models\Pelatihan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class KelolaAdmin extends Component
{
    use WithFileUploads;
    use ManagesPelatihan;
    use ManagesExcelImportExport;
    use ManagesMasterData;
    use ManagesUsersAndSecurity;

    // ── View Mode & Filters ───────────────────────────────────
    public string $adminTab = 'daftar';
    public int    $tahun;
    public string $search = '';
    public string $filterStatus = '';
    public string $filterKategori = '';
    public string $filterPublish = '';
    public string $lastUpdated = '';
    public int    $page = 1;
    public int    $perPage = 10;

    // ── Modal States ──────────────────────────────────────────
    public bool   $showModal         = false;
    public bool   $showDeleteConfirm = false;
    public string $modalMode         = 'create';
    public bool   $showImportModal   = false;
    public $fileExcel = null;
    public string $importMode = 'replace_year';
    public bool   $importAsDraft = false;
    public array  $importErrors = [];
    public array  $importWarnings = [];
    public ?array $importPreview = null;

    // ── Form Fields Pelatihan ─────────────────────────────────
    public ?int   $editId             = null;
    public int    $formTahun          = 2026;
    public string $kategori           = 'PELATIHAN FUNGSIONAL';
    public string $kode_pelatihan     = '';
    public string $nama_pelatihan     = '';
    public string $angkatan           = '';
    public string $jumlah_peserta     = '';
    public string $peserta_terdaftar  = '0';
    public string $peserta_lulus      = '0';
    public string $durasi             = '';
    public string $jam_pelajaran      = '';
    public string $tanggal_mulai      = '';
    public string $tanggal_selesai    = '';
    public string $sumber_dana        = 'RM';
    public string $tempat_pelaksanaan = 'PPSDM';
    public string $penyelenggara      = '';
    public string $pic_nama           = '';
    public string $pic_kontak         = '';
    public string $link_pendaftaran   = '';
    public string $deskripsi          = '';
    public string $metode             = 'Klasikal';
    public bool   $auto_status        = true;
    public bool   $is_published       = true;
    public string $status             = 'Belum Dimulai';
    public int    $seleksi_hari       = 7;
    public array  $tahapanList        = [];

    // ── Master Data Form States ───────────────────────────────
    public string $newKategoriNama = '';
    public string $newMetodeNama   = '';
    public string $newMetodeWarna  = '#38bdf8';
    public string $newMetodeTeks   = '#ffffff';
    public string $newLiburNama    = '';
    public int    $newLiburTahun   = 2026;
    public string $newLiburMulai   = '';
    public string $newLiburSelesai = '';
    public string $newLiburKet     = '';
    public string $newLiburWarna   = '#b91c1c';

    // ── Account Management States ─────────────────────────────
    public string $currentPassword = '';
    public string $newPassword     = '';
    public string $confirmPassword = '';
    public string $newUserName     = '';
    public string $newUserEmail    = '';
    public string $newUserPassword = '';
    public string $newUserRole     = User::ROLE_OPERATOR;

    // ── Delete & Notification ─────────────────────────────────
    public ?int   $deleteId  = null;
    public string $notif     = '';
    public string $notifType = 'success';

    protected function rules(): array
    {
        return [
            'formTahun'          => 'required|integer|min:2000|max:2100',
            'kategori'           => 'required|string|max:100',
            'kode_pelatihan'     => 'nullable|string|max:50',
            'nama_pelatihan'     => 'required|string|max:255',
            'angkatan'           => 'nullable|string|max:80',
            'jumlah_peserta'     => 'required|integer|min:1',
            'peserta_terdaftar'  => 'nullable|integer|min:0',
            'peserta_lulus'      => 'nullable|integer|min:0',
            'jam_pelajaran'      => 'nullable|integer|min:0',
            'tanggal_mulai'      => 'required|date',
            'tanggal_selesai'    => 'required|date|after_or_equal:tanggal_mulai',
            'sumber_dana'        => 'nullable|string|max:50',
            'tempat_pelaksanaan' => 'nullable|string|max:100',
            'penyelenggara'      => 'nullable|string|max:120',
            'pic_nama'           => 'nullable|string|max:100',
            'pic_kontak'         => 'nullable|string|max:60',
            'link_pendaftaran'   => 'nullable|url|max:500',
            'deskripsi'          => 'nullable|string|max:2000',
            'metode'             => 'nullable|string|max:50',
            'auto_status'        => 'boolean',
            'seleksi_hari'       => 'required|integer|min:0|max:180',
            'status'             => 'required|in:Belum Dimulai,Sedang Berlangsung,Tahap Seleksi,Selesai',
        ];
    }

    public function mount(): void
    {
        $available           = Pelatihan::getAvailableYears();
        $currentYear         = (int)Carbon::now()->year;
        $this->tahun         = in_array($currentYear, $available, true) ? $currentYear : ($available[0] ?? $currentYear);
        $this->formTahun     = $this->tahun;
        $this->newLiburTahun = $this->tahun;
        $this->lastUpdated   = Carbon::now()->format('H:i:s');
    }

    protected function canManage(): bool
    {
        $user = Auth::user();
        if (!$user || !$user->canManageTraining()) {
            $this->flashError('Akses ditolak: peran Viewer hanya memiliki akses lihat.');
            return false;
        }
        return true;
    }

    protected function isSuperAdmin(): bool
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            $this->flashError('Akses ditolak: fitur ini khusus Administrator.');
            return false;
        }
        return true;
    }

    protected function flashSuccess(string $message): void
    {
        $this->notif       = $message;
        $this->notifType   = 'success';
        $this->lastUpdated = Carbon::now()->format('H:i:s');
    }

    protected function flashError(string $message): void
    {
        $this->notif     = $message;
        $this->notifType = 'error';
    }

    public function updated($property): void
    {
        if (in_array($property, ['tahun', 'search', 'filterStatus', 'filterKategori', 'filterPublish', 'perPage'], true)) {
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

    public function clearNotif(): void
    {
        $this->notif = '';
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

            if ($this->filterStatus !== '' && $p->computed_status !== $this->filterStatus) {
                return false;
            }

            if ($this->filterPublish === 'published' && !$p->is_published) {
                return false;
            }

            if ($this->filterPublish === 'draft' && $p->is_published) {
                return false;
            }

            return true;
        })->values();
    }

    public function render()
    {
        $yearRecords = Pelatihan::forYear($this->tahun)->orderBy('tanggal_mulai')->get();

        $statusCounts = [
            'Belum Dimulai'      => 0,
            'Sedang Berlangsung' => 0,
            'Tahap Seleksi'      => 0,
            'Selesai'            => 0,
        ];

        foreach ($yearRecords as $p) {
            $s = $p->computed_status;
            if (isset($statusCounts[$s])) {
                $statusCounts[$s]++;
            }
        }

        $pelatihans          = $this->getFilteredCollection($yearRecords);
        $totalFiltered       = $pelatihans->count();
        $lastPage            = max(1, (int)ceil($totalFiltered / max(1, $this->perPage)));
        $currentPage         = min(max(1, $this->page), $lastPage);
        $this->page          = $currentPage;
        $paginatedPelatihans = $pelatihans->forPage($currentPage, $this->perPage);

        $kalenderGroups = $pelatihans
            ->sortBy('id')
            ->groupBy(fn($item) => strtoupper($item->kategori ?: 'PELATIHAN FUNGSIONAL'));

        $monthlyLoad = [];
        $monthNames  = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
        for ($m = 1; $m <= 12; $m++) {
            $mStart = Carbon::create($this->tahun, $m, 1)->startOfMonth();
            $mEnd   = $mStart->copy()->endOfMonth();
            $activeInMonth = $yearRecords->filter(fn($p) =>
                $p->tanggal_mulai && $p->tanggal_selesai
                && $p->tanggal_mulai->lte($mEnd)
                && $p->tanggal_selesai->gte($mStart)
            );
            $monthlyLoad[] = [
                'bulan'   => $monthNames[$m - 1],
                'jumlah'  => $activeInMonth->count(),
                'peserta' => $activeInMonth->sum('jumlah_peserta'),
            ];
        }

        $hariLibursAll = HariLibur::orderByDesc('tahun')->orderBy('tanggal_mulai')->get();
        $currentUser   = Auth::user();

        return view('livewire.admin.kelola-admin', [
            'pelatihans'      => $paginatedPelatihans,
            'totalFiltered'   => $totalFiltered,
            'currentPage'     => $currentPage,
            'lastPage'        => $lastPage,
            'yearRecords'     => $yearRecords,
            'kalenderGroups'  => $kalenderGroups,
            'availableYears'  => Pelatihan::getAvailableYears(),
            'statusCounts'    => $statusCounts,
            'monthlyLoad'     => $monthlyLoad,
            'byKategori'      => $yearRecords->groupBy(fn($p) => strtoupper($p->kategori ?: 'LAINNYA')),
            'bySumberDana'    => $yearRecords->groupBy(fn($p) => strtoupper($p->sumber_dana ?: 'RM')),
            'byTempat'        => $yearRecords->groupBy(fn($p) => strtoupper($p->tempat_pelaksanaan ?: 'PPSDM')),
            'kategoris'       => MasterKategori::orderBy('urutan')->orderBy('nama')->get(),
            'activeKategoris' => MasterKategori::getActiveNames(),
            'metodes'         => MasterMetode::orderBy('urutan')->orderBy('nama')->get(),
            'hariLibursAll'   => $hariLibursAll,
            'hariLibursYear'  => $hariLibursAll->where('tahun', $this->tahun)->where('is_active', true)->values(),
            'todayMarkerLeft' => Pelatihan::calculateTodayMarkerPosition($this->tahun),
            'users'              => ($currentUser && $currentUser->isAdmin()) ? User::orderBy('id')->get() : collect(),
            'auditLogs'          => AuditLog::orderByDesc('id')->limit(50)->get(),
            'canManage'          => $currentUser?->canManageTraining() ?? false,
            'isSuperAdmin'       => $currentUser?->isAdmin() ?? false,
            'showPublicKalender' => AppSetting::getBool('show_public_kalender', true),
            'draftCount'         => $yearRecords->where('is_published', false)->count(),
        ])->layout('layouts.admin');
    }
}
