<?php

namespace App\Livewire\Admin\Concerns;

use App\Models\AuditLog;
use App\Models\HariLibur;
use App\Models\MasterKategori;
use App\Models\MasterMetode;

trait ManagesMasterData
{
    public function addKategori(): void
    {
        if (!$this->canManage()) return;

        $this->validate(['newKategoriNama' => 'required|string|max:100|unique:master_kategoris,nama']);
        $nama = strtoupper(trim($this->newKategoriNama));
        $kat  = MasterKategori::create([
            'nama'      => $nama,
            'urutan'    => (int)MasterKategori::max('urutan') + 1,
            'is_active' => true,
        ]);
        AuditLog::record('settings', "Menambah kategori: {$nama}", 'MasterKategori', $kat->id);
        $this->newKategoriNama = '';
        $this->flashSuccess("Kategori '{$nama}' ditambahkan.");
    }

    public function toggleKategori(int $id): void
    {
        if (!$this->canManage()) return;
        $kat = MasterKategori::findOrFail($id);
        $kat->update(['is_active' => !$kat->is_active]);
        $this->flashSuccess('Status kategori diperbarui.');
    }

    public function deleteKategori(int $id): void
    {
        if (!$this->canManage()) return;
        $kat  = MasterKategori::findOrFail($id);
        $name = $kat->nama;
        $kat->delete();
        AuditLog::record('settings', "Menghapus kategori: {$name}", 'MasterKategori', $id);
        $this->flashSuccess("Kategori '{$name}' dihapus.");
    }

    public function addMetode(): void
    {
        if (!$this->canManage()) return;

        $this->validate([
            'newMetodeNama'  => 'required|string|max:100|unique:master_metodes,nama',
            'newMetodeWarna' => 'required|string|max:20',
            'newMetodeTeks'  => 'required|string|max:20',
        ]);

        $m = MasterMetode::create([
            'nama'       => trim($this->newMetodeNama),
            'kode_warna' => $this->newMetodeWarna,
            'warna_teks' => $this->newMetodeTeks,
            'urutan'     => (int)MasterMetode::max('urutan') + 1,
            'is_active'  => true,
        ]);
        AuditLog::record('settings', "Menambah metode: {$m->nama}", 'MasterMetode', $m->id);
        $this->newMetodeNama = '';
        $this->flashSuccess("Metode '{$m->nama}' ditambahkan.");
    }

    public function toggleMetode(int $id): void
    {
        if (!$this->canManage()) return;
        $m = MasterMetode::findOrFail($id);
        $m->update(['is_active' => !$m->is_active]);
        $this->flashSuccess('Status metode diperbarui.');
    }

    public function deleteMetode(int $id): void
    {
        if (!$this->canManage()) return;
        $m    = MasterMetode::findOrFail($id);
        $name = $m->nama;
        $m->delete();
        AuditLog::record('settings', "Menghapus metode: {$name}", 'MasterMetode', $id);
        $this->flashSuccess("Metode '{$name}' dihapus.");
    }

    public function addHariLibur(): void
    {
        if (!$this->canManage()) return;

        $this->validate([
            'newLiburTahun'   => 'required|integer|min:2000|max:2100',
            'newLiburNama'    => 'required|string|max:150',
            'newLiburMulai'   => 'required|date',
            'newLiburSelesai' => 'required|date|after_or_equal:newLiburMulai',
            'newLiburKet'     => 'nullable|string|max:255',
            'newLiburWarna'   => 'required|string|max:20',
        ]);

        $libur = HariLibur::create([
            'tahun'           => $this->newLiburTahun,
            'nama'            => trim($this->newLiburNama),
            'tanggal_mulai'   => $this->newLiburMulai,
            'tanggal_selesai' => $this->newLiburSelesai,
            'keterangan'      => trim($this->newLiburKet) ?: null,
            'warna'           => $this->newLiburWarna,
            'is_active'       => true,
        ]);
        AuditLog::record('settings', "Menambah hari libur {$libur->tahun}: {$libur->nama}", 'HariLibur', $libur->id);
        $this->newLiburNama    = '';
        $this->newLiburMulai   = '';
        $this->newLiburSelesai = '';
        $this->newLiburKet     = '';
        $this->flashSuccess('Hari libur kalender berhasil ditambahkan.');
    }

    public function deleteHariLibur(int $id): void
    {
        if (!$this->canManage()) return;
        $libur = HariLibur::findOrFail($id);
        $name  = $libur->nama;
        $libur->delete();
        AuditLog::record('settings', "Menghapus hari libur: {$name}", 'HariLibur', $id);
        $this->flashSuccess("Hari libur '{$name}' dihapus.");
    }
}
