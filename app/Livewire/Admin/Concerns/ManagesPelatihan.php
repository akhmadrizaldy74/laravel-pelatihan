<?php

namespace App\Livewire\Admin\Concerns;

use App\Models\AppSetting;
use App\Models\AuditLog;
use App\Models\MasterKategori;
use App\Models\Pelatihan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait ManagesPelatihan
{
    public function openCreate(): void
    {
        if (!$this->canManage()) return;

        $this->resetValidation();
        $this->editId             = null;
        $this->modalMode          = 'create';
        $this->formTahun          = $this->tahun;
        $this->kategori           = MasterKategori::getActiveNames()[0] ?? 'PELATIHAN FUNGSIONAL';
        $this->kode_pelatihan     = '';
        $this->nama_pelatihan     = '';
        $this->angkatan           = '';
        $this->jumlah_peserta     = '';
        $this->peserta_terdaftar  = '0';
        $this->peserta_lulus      = '0';
        $this->durasi             = '';
        $this->jam_pelajaran      = '';
        $this->tanggal_mulai      = '';
        $this->tanggal_selesai    = '';
        $this->sumber_dana        = 'RM';
        $this->tempat_pelaksanaan = 'PPSDM';
        $this->penyelenggara      = '';
        $this->pic_nama           = '';
        $this->pic_kontak         = '';
        $this->link_pendaftaran   = '';
        $this->deskripsi          = '';
        $this->metode             = 'Klasikal';
        $this->auto_status        = true;
        $this->is_published       = true;
        $this->seleksi_hari       = 7;
        $this->status             = 'Belum Dimulai';
        $this->tahapanList        = [];
        $this->showModal          = true;
    }

    public function openEdit(int $id): void
    {
        if (!$this->canManage()) return;

        $this->resetValidation();
        $p = Pelatihan::findOrFail($id);
        $this->editId             = $p->id;
        $this->modalMode          = 'edit';
        $this->formTahun          = (int)($p->tahun ?: ($p->tanggal_mulai?->year ?? $this->tahun));
        $this->kategori           = $p->kategori ?: 'PELATIHAN FUNGSIONAL';
        $this->kode_pelatihan     = $p->kode_pelatihan ?: '';
        $this->nama_pelatihan     = $p->nama_pelatihan;
        $this->angkatan           = $p->angkatan ?: '';
        $this->jumlah_peserta     = (string)$p->jumlah_peserta;
        $this->peserta_terdaftar  = (string)($p->peserta_terdaftar ?? 0);
        $this->peserta_lulus      = (string)($p->peserta_lulus ?? 0);
        $this->durasi             = $p->durasi ?: '';
        $this->jam_pelajaran      = $p->jam_pelajaran ? (string)$p->jam_pelajaran : '';
        $this->tanggal_mulai      = $p->tanggal_mulai ? $p->tanggal_mulai->format('Y-m-d') : '';
        $this->tanggal_selesai    = $p->tanggal_selesai ? $p->tanggal_selesai->format('Y-m-d') : '';
        $this->sumber_dana        = $p->sumber_dana ?: 'RM';
        $this->tempat_pelaksanaan = $p->tempat_pelaksanaan ?: 'PPSDM';
        $this->penyelenggara      = $p->penyelenggara ?: '';
        $this->pic_nama           = $p->pic_nama ?: '';
        $this->pic_kontak         = $p->pic_kontak ?: '';
        $this->link_pendaftaran   = $p->link_pendaftaran ?: '';
        $this->deskripsi          = $p->deskripsi ?: '';
        $this->metode             = $p->metode ?: 'Klasikal';
        $this->auto_status        = (bool)$p->auto_status;
        $this->is_published       = (bool)$p->is_published;
        $this->seleksi_hari       = (int)($p->seleksi_hari ?? 7);
        $this->status             = $p->status;
        $this->tahapanList        = is_array($p->jadwal_tahapan) ? array_values($p->jadwal_tahapan) : [];
        $this->showModal          = true;
    }

    public function addTahapan(): void
    {
        $this->tahapanList[] = [
            'tahap'   => $this->metode ?: 'Online/Daring',
            'mulai'   => $this->tanggal_mulai ?: '',
            'selesai' => $this->tanggal_selesai ?: '',
        ];
    }

    public function removeTahapan(int $index): void
    {
        unset($this->tahapanList[$index]);
        $this->tahapanList = array_values($this->tahapanList);
    }

    public function save(): void
    {
        if (!$this->canManage()) return;

        $this->validate();

        $mulai   = Carbon::parse($this->tanggal_mulai);
        $selesai = Carbon::parse($this->tanggal_selesai);

        $durasiVal = trim($this->durasi);
        if ($durasiVal === '') {
            $days      = $mulai->diffInDays($selesai) + 1;
            $durasiVal = $days . ' hr';
        }

        $cleanTahapan = [];
        foreach ($this->tahapanList as $row) {
            $tName  = trim((string)($row['tahap'] ?? ''));
            $tStart = trim((string)($row['mulai'] ?? ''));
            $tEnd   = trim((string)($row['selesai'] ?? ''));
            if ($tName === '' || $tStart === '' || $tEnd === '') {
                continue;
            }
            $cStart = Carbon::parse($tStart);
            $cEnd   = Carbon::parse($tEnd);
            if ($cEnd->lt($cStart)) {
                [$cStart, $cEnd] = [$cEnd, $cStart];
            }
            $cleanTahapan[] = [
                'tahap'   => $tName,
                'mulai'   => $cStart->format('Y-m-d'),
                'selesai' => $cEnd->format('Y-m-d'),
            ];
        }

        $data = [
            'tahun'              => (int)$this->formTahun,
            'kategori'           => strtoupper(trim($this->kategori)),
            'kode_pelatihan'     => trim($this->kode_pelatihan) ?: null,
            'nama_pelatihan'     => trim($this->nama_pelatihan),
            'angkatan'           => trim($this->angkatan) ?: null,
            'jumlah_peserta'     => (int)$this->jumlah_peserta,
            'peserta_terdaftar'  => max(0, (int)$this->peserta_terdaftar),
            'peserta_lulus'      => max(0, (int)$this->peserta_lulus),
            'durasi'             => $durasiVal,
            'jam_pelajaran'      => $this->jam_pelajaran !== '' ? (int)$this->jam_pelajaran : null,
            'tanggal_mulai'      => $this->tanggal_mulai,
            'tanggal_selesai'    => $this->tanggal_selesai,
            'sumber_dana'        => strtoupper(trim($this->sumber_dana ?: 'RM')),
            'tempat_pelaksanaan' => strtoupper(trim($this->tempat_pelaksanaan ?: 'PPSDM')),
            'penyelenggara'      => trim($this->penyelenggara) ?: null,
            'pic_nama'           => trim($this->pic_nama) ?: null,
            'pic_kontak'         => trim($this->pic_kontak) ?: null,
            'link_pendaftaran'   => trim($this->link_pendaftaran) ?: null,
            'deskripsi'          => trim($this->deskripsi) ?: null,
            'metode'             => $this->metode ?: 'Klasikal',
            'jadwal_tahapan'     => !empty($cleanTahapan) ? $cleanTahapan : null,
            'auto_status'        => $this->auto_status,
            'is_published'       => $this->is_published,
            'seleksi_hari'       => max(0, (int)$this->seleksi_hari),
            'status'             => $this->status,
        ];

        if ($this->modalMode === 'create') {
            $created = Pelatihan::create($data);
            AuditLog::record('create', "Menambahkan pelatihan: {$created->nama_pelatihan}", 'Pelatihan', $created->id);
            $this->flashSuccess('Pelatihan baru berhasil ditambahkan!');
        } else {
            $model = Pelatihan::findOrFail($this->editId);
            $model->update($data);
            AuditLog::record('update', "Memperbarui pelatihan: {$model->nama_pelatihan}", 'Pelatihan', $model->id);
            $this->flashSuccess('Data pelatihan berhasil diperbarui!');
        }

        $this->tahun     = (int)$this->formTahun;
        $this->showModal = false;
    }

    public function confirmDelete(int $id): void
    {
        if (!$this->canManage()) return;
        $this->deleteId          = $id;
        $this->showDeleteConfirm = true;
    }

    public function doDelete(): void
    {
        if (!$this->canManage()) return;

        if ($this->deleteId) {
            $item = Pelatihan::find($this->deleteId);
            if ($item) {
                $name = $item->nama_pelatihan;
                $item->delete();
                AuditLog::record('delete', "Menghapus pelatihan: {$name}", 'Pelatihan', $this->deleteId);
            }
            $this->deleteId          = null;
            $this->showDeleteConfirm = false;
            $this->flashSuccess('Data pelatihan berhasil dihapus.');
        }
    }

    public function closeModal(): void
    {
        $this->showModal         = false;
        $this->showDeleteConfirm = false;
        $this->deleteId          = null;
    }

    public function togglePublish(int $id): void
    {
        if (!$this->canManage()) return;
        $item = Pelatihan::findOrFail($id);
        $item->update(['is_published' => !$item->is_published]);
        $state = $item->is_published ? 'Ditampilkan di publik' : 'Disembunyikan (Draft)';
        AuditLog::record('update', "{$state}: {$item->nama_pelatihan}", 'Pelatihan', $item->id);
        $this->flashSuccess("{$item->nama_pelatihan}: {$state}.");
    }

    public function publishAllYear(): void
    {
        if (!$this->canManage()) return;
        $count = Pelatihan::where('tahun', $this->tahun)->where('is_published', false)->update(['is_published' => true]);
        AuditLog::record('update', "Menerbitkan {$count} jadwal draft tahun {$this->tahun}", 'Pelatihan');
        $this->flashSuccess("Seluruh jadwal Tahun {$this->tahun} sekarang tampil di publik ({$count} diterbitkan).");
    }

    public function hideAllYear(): void
    {
        if (!$this->canManage()) return;
        $count = Pelatihan::where('tahun', $this->tahun)->where('is_published', true)->update(['is_published' => false]);
        AuditLog::record('update', "Menjadikan {$count} jadwal tahun {$this->tahun} sebagai Draft", 'Pelatihan');
        $this->flashSuccess("Seluruh jadwal Tahun {$this->tahun} disembunyikan sebagai Draft ({$count} diubah).");
    }

    public function togglePublicKalender(): void
    {
        if (!$this->canManage()) return;
        $current = AppSetting::getBool('show_public_kalender', true);
        $next    = !$current;
        AppSetting::setBool('show_public_kalender', $next);
        AuditLog::record('settings', $next ? 'Menampilkan Kalender Gantt di halaman publik.' : 'Menyembunyikan Kalender Gantt dari halaman publik.', 'AppSetting');
        $this->flashSuccess($next ? 'Kalender Gantt sekarang TAMPIL di dashboard publik.' : 'Kalender Gantt sekarang DISEMBUNYIKAN dari dashboard publik.');
    }

    public function cloneFromPreviousYear(): void
    {
        if (!$this->canManage()) return;

        $prevYear = $this->tahun - 1;
        $sources  = Pelatihan::where('tahun', $prevYear)->orderBy('id')->get();

        if ($sources->isEmpty()) {
            $this->flashError("Tidak ada data pelatihan pada Tahun {$prevYear} untuk disalin.");
            return;
        }

        $createdCount = 0;
        DB::transaction(function () use ($sources, &$createdCount) {
            foreach ($sources as $src) {
                $newMulai   = $src->tanggal_mulai?->copy()->addYear();
                $newSelesai = $src->tanggal_selesai?->copy()->addYear();

                $newTahapan = null;
                if (!empty($src->jadwal_tahapan) && is_array($src->jadwal_tahapan)) {
                    $newTahapan = [];
                    foreach ($src->jadwal_tahapan as $t) {
                        $tMulai   = !empty($t['mulai']) ? Carbon::parse($t['mulai'])->addYear()->format('Y-m-d') : null;
                        $tSelesai = !empty($t['selesai']) ? Carbon::parse($t['selesai'])->addYear()->format('Y-m-d') : null;
                        if ($tMulai && $tSelesai) {
                            $newTahapan[] = [
                                'tahap'   => $t['tahap'] ?? 'Tahap',
                                'mulai'   => $tMulai,
                                'selesai' => $tSelesai,
                            ];
                        }
                    }
                }

                Pelatihan::create([
                    'tahun'              => $this->tahun,
                    'kategori'           => $src->kategori,
                    'kode_pelatihan'     => $src->kode_pelatihan,
                    'nama_pelatihan'     => $src->nama_pelatihan,
                    'angkatan'           => $src->angkatan,
                    'jumlah_peserta'     => $src->jumlah_peserta,
                    'peserta_terdaftar'  => 0,
                    'peserta_lulus'      => 0,
                    'durasi'             => $src->durasi,
                    'jam_pelajaran'      => $src->jam_pelajaran,
                    'tanggal_mulai'      => $newMulai?->format('Y-m-d'),
                    'tanggal_selesai'    => $newSelesai?->format('Y-m-d'),
                    'sumber_dana'        => $src->sumber_dana,
                    'tempat_pelaksanaan' => $src->tempat_pelaksanaan,
                    'penyelenggara'      => $src->penyelenggara,
                    'pic_nama'           => $src->pic_nama,
                    'pic_kontak'         => $src->pic_kontak,
                    'link_pendaftaran'   => $src->link_pendaftaran,
                    'deskripsi'          => $src->deskripsi,
                    'metode'             => $src->metode,
                    'jadwal_tahapan'     => $newTahapan,
                    'status'             => 'Belum Dimulai',
                    'auto_status'        => $src->auto_status,
                    'is_published'       => false,
                    'seleksi_hari'       => $src->seleksi_hari ?? 7,
                ]);
                $createdCount++;
            }
        });

        AuditLog::record('create', "Menyalin {$createdCount} rencana pelatihan dari {$prevYear} ke {$this->tahun} (Draft).", 'Pelatihan');
        $this->flashSuccess("{$createdCount} rencana pelatihan dari Tahun {$prevYear} disalin ke Tahun {$this->tahun} sebagai Draft.");
    }
}
