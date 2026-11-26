{{-- Header Khusus Menu Master Data & Hari Libur --}}
<div class="tab-header-card">
    <div>
        <h1 class="tab-header-title">Master Data &amp; Hari Libur</h1>
        <p class="tab-header-sub">Kelola daftar kategori pelatihan, metode &amp; warna kalender, serta jadwal hari libur nasional.</p>
    </div>
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:14px;">
    <div class="panel-card">
        <h3 style="font-size:14px; font-weight:700; margin-bottom:10px;">Kategori Pelatihan</h3>
        @if($canManage)
            <form wire:submit.prevent="addKategori" style="display:flex; gap:8px; margin-bottom:12px;">
                <input type="text" wire:model="newKategoriNama" class="form-control" placeholder="Nama kategori baru" required>
                <button type="submit" class="btn-primary">Tambah</button>
            </form>
            @error('newKategoriNama') <div style="color:#dc2626; font-size:12px; margin-bottom:8px;">{{ $message }}</div> @enderror
        @endif
        @foreach($kategoris as $kat)
            <div style="display:flex; justify-content:space-between; align-items:center; padding:7px 4px; border-bottom:1px solid #f1f5f9; gap:8px;">
                <span style="font-size:12.5px; font-weight:600; color:{{ $kat->is_active ? '#1e293b' : '#94a3b8' }};">{{ $kat->nama }}</span>
                @if($canManage)
                    <div style="display:flex; gap:4px;">
                        <button type="button" wire:click="toggleKategori({{ $kat->id }})" class="btn-action-edit">{{ $kat->is_active ? 'Aktif' : 'Nonaktif' }}</button>
                        <button type="button" wire:click="deleteKategori({{ $kat->id }})" class="btn-action-del">Hapus</button>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="panel-card">
        <h3 style="font-size:14px; font-weight:700; margin-bottom:10px;">Metode &amp; Warna Kalender</h3>
        @if($canManage)
            <form wire:submit.prevent="addMetode" style="display:flex; flex-direction:column; gap:8px; margin-bottom:12px; background:#f8fafc; padding:10px; border:1px solid #e2e8f0; border-radius:5px;">
                <input type="text" wire:model="newMetodeNama" class="form-control" placeholder="Nama metode" required>
                <div style="display:flex; gap:10px; align-items:center;">
                    <label style="font-size:12px;">Warna Bar: <input type="color" wire:model="newMetodeWarna"></label>
                    <label style="font-size:12px;">Warna Teks: <input type="color" wire:model="newMetodeTeks"></label>
                    <button type="submit" class="btn-primary" style="margin-left:auto;">Tambah</button>
                </div>
            </form>
            @error('newMetodeNama') <div style="color:#dc2626; font-size:12px; margin-bottom:8px;">{{ $message }}</div> @enderror
        @endif
        @foreach($metodes as $met)
            <div style="display:flex; justify-content:space-between; align-items:center; padding:7px 4px; border-bottom:1px solid #f1f5f9; gap:8px;">
                <span style="font-size:11.5px; font-weight:600; padding:2px 8px; border-radius:3px; background:{{ $met->kode_warna }}; color:{{ $met->warna_teks }}; opacity:{{ $met->is_active ? '1' : '0.45' }};">
                    {{ $met->nama }}
                </span>
                @if($canManage)
                    <div style="display:flex; gap:4px;">
                        <button type="button" wire:click="toggleMetode({{ $met->id }})" class="btn-action-edit">{{ $met->is_active ? 'Aktif' : 'Nonaktif' }}</button>
                        <button type="button" wire:click="deleteMetode({{ $met->id }})" class="btn-action-del">Hapus</button>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="panel-card" style="grid-column:1 / -1;">
        <h3 style="font-size:14px; font-weight:700; margin-bottom:10px;">Hari Libur &amp; Catatan Kalender</h3>
        @if($canManage)
            <form wire:submit.prevent="addHariLibur" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:8px; margin-bottom:12px; background:#f8fafc; padding:12px; border:1px solid #e2e8f0; border-radius:5px;">
                <div>
                    <label class="form-label">Tahun</label>
                    <input type="number" wire:model="newLiburTahun" class="form-control" required>
                </div>
                <div>
                    <label class="form-label">Nama Libur</label>
                    <input type="text" wire:model="newLiburNama" class="form-control" placeholder="Cuti Bersama" required>
                </div>
                <div>
                    <label class="form-label">Tgl Mulai</label>
                    <input type="date" wire:model="newLiburMulai" class="form-control" required>
                </div>
                <div>
                    <label class="form-label">Tgl Selesai</label>
                    <input type="date" wire:model="newLiburSelesai" class="form-control" required>
                </div>
                <div>
                    <label class="form-label">Catatan Kalender</label>
                    <input type="text" wire:model="newLiburKet" class="form-control" placeholder="Keterangan di bawah kalender">
                </div>
                <div style="display:flex; align-items:flex-end; gap:6px;">
                    <input type="color" wire:model="newLiburWarna" style="height:34px;">
                    <button type="submit" class="btn-primary" style="width:100%; justify-content:center;">Tambah</button>
                </div>
            </form>
        @endif

        @forelse($hariLibursAll as $libur)
            <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 4px; border-bottom:1px solid #f1f5f9; gap:10px; flex-wrap:wrap;">
                <div>
                    <span style="background:{{ $libur->warna }}; color:white; font-size:11px; font-weight:700; padding:1px 6px; border-radius:3px;">{{ $libur->tahun }}</span>
                    <strong style="margin-left:6px; font-size:13px;">{{ $libur->nama }}</strong>
                    <span style="font-size:12px; color:#64748b; margin-left:6px;">({{ $libur->periode_display }})</span>
                    @if($libur->keterangan)
                        <div style="font-size:12px; color:#475569; margin-top:2px;">{{ $libur->keterangan }}</div>
                    @endif
                </div>
                @if($canManage)
                    <button type="button" wire:click="deleteHariLibur({{ $libur->id }})" class="btn-action-del">Hapus</button>
                @endif
            </div>
        @empty
            <div style="color:#94a3b8; font-size:12px;">Belum ada data hari libur.</div>
        @endforelse
    </div>
</div>
