@if($showModal)
<div class="modal-overlay">
    <div class="modal-box" style="max-width:720px;">
        <div class="modal-header">
            <h3>{{ $modalMode === 'create' ? 'Tambah Data Pelatihan' : 'Edit Data Pelatihan' }}</h3>
            <button type="button" wire:click="closeModal" class="modal-close">&times;</button>
        </div>
        <form wire:submit.prevent="save">
            <div class="modal-body">
                <div class="form-row">
                    <div>
                        <label class="form-label">Tahun Anggaran <span style="color:#dc2626;">*</span></label>
                        <input type="number" wire:model="formTahun" class="form-control" min="2000" max="2100" required>
                    </div>
                    <div>
                        <label class="form-label">Kategori Pelatihan <span style="color:#dc2626;">*</span></label>
                        <select wire:model="kategori" class="form-control" required>
                            @foreach($activeKategoris as $kat)
                                <option value="{{ $kat }}">{{ $kat }}</option>
                            @endforeach
                        </select>
                        @error('kategori') <div style="color:#dc2626; font-size:11.5px;">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <label class="form-label">Kode Pelatihan</label>
                        <input type="text" wire:model="kode_pelatihan" class="form-control" placeholder="Contoh: JF-PK-111">
                    </div>
                    <div>
                        <label class="form-label">Angkatan / Gelombang</label>
                        <input type="text" wire:model="angkatan" class="form-control" placeholder="Contoh: Angkatan 111">
                    </div>
                </div>

                <div class="form-group-full">
                    <label class="form-label">Nama / Jenis Pelatihan <span style="color:#dc2626;">*</span></label>
                    <input type="text" wire:model="nama_pelatihan" class="form-control" required>
                    @error('nama_pelatihan') <div style="color:#dc2626; font-size:11.5px;">{{ $message }}</div> @enderror
                </div>

                <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:10px; margin-bottom:12px;">
                    <div>
                        <label class="form-label">Target Peserta <span style="color:#dc2626;">*</span></label>
                        <input type="number" wire:model="jumlah_peserta" class="form-control" min="1" required>
                        @error('jumlah_peserta') <div style="color:#dc2626; font-size:11.5px;">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="form-label">Terdaftar</label>
                        <input type="number" wire:model="peserta_terdaftar" class="form-control" min="0">
                    </div>
                    <div>
                        <label class="form-label">Realisasi Lulus</label>
                        <input type="number" wire:model="peserta_lulus" class="form-control" min="0">
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <label class="form-label">Durasi Pelatihan</label>
                        <input type="text" wire:model="durasi" class="form-control" placeholder="Otomatis jika kosong">
                    </div>
                    <div>
                        <label class="form-label">Total Jam Pelajaran (JP)</label>
                        <input type="number" wire:model="jam_pelajaran" class="form-control" min="0">
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <label class="form-label">Tanggal Mulai <span style="color:#dc2626;">*</span></label>
                        <input type="date" wire:model="tanggal_mulai" class="form-control" required>
                        @error('tanggal_mulai') <div style="color:#dc2626; font-size:11.5px;">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="form-label">Tanggal Selesai <span style="color:#dc2626;">*</span></label>
                        <input type="date" wire:model="tanggal_selesai" class="form-control" required>
                        @error('tanggal_selesai') <div style="color:#dc2626; font-size:11.5px;">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <label class="form-label">Metode Utama</label>
                        <select wire:model="metode" class="form-control">
                            @foreach($metodes as $met)
                                <option value="{{ $met->nama }}">{{ $met->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Sumber Dana &amp; Tempat</label>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                            <input type="text" wire:model="sumber_dana" class="form-control" placeholder="RM / SWA / PNBP">
                            <input type="text" wire:model="tempat_pelaksanaan" class="form-control" placeholder="PPSDM / LUAR">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <label class="form-label">Unit Penyelenggara</label>
                        <input type="text" wire:model="penyelenggara" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">PIC &amp; Kontak</label>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                            <input type="text" wire:model="pic_nama" class="form-control" placeholder="Nama PIC">
                            <input type="text" wire:model="pic_kontak" class="form-control" placeholder="No. HP / Ext">
                        </div>
                    </div>
                </div>

                <div class="form-group-full">
                    <label class="form-label">Link Pendaftaran / LMS</label>
                    <input type="url" wire:model="link_pendaftaran" class="form-control" placeholder="https://...">
                    @error('link_pendaftaran') <div style="color:#dc2626; font-size:11.5px;">{{ $message }}</div> @enderror
                </div>

                <div class="form-group-full">
                    <label class="form-label">Deskripsi / Syarat Peserta</label>
                    <textarea wire:model="deskripsi" class="form-control" rows="2"></textarea>
                </div>

                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:12px; margin-bottom:12px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                        <strong style="font-size:12.5px; color:#0f172a;">Rincian Tahapan Kalender (Opsional)</strong>
                        <button type="button" wire:click="addTahapan" class="btn-secondary" style="padding:4px 10px; font-size:11.5px;">+ Tambah Tahap</button>
                    </div>

                    @forelse($tahapanList as $idx => $tahapItem)
                        <div style="display:grid; grid-template-columns:1.3fr 1fr 1fr auto; gap:8px; align-items:center; margin-bottom:6px;">
                            <select wire:model="tahapanList.{{ $idx }}.tahap" class="form-control" style="padding:5px 8px; font-size:12px;">
                                @foreach($metodes as $met)
                                    <option value="{{ $met->nama }}">{{ $met->nama }}</option>
                                @endforeach
                            </select>
                            <input type="date" wire:model="tahapanList.{{ $idx }}.mulai" class="form-control" style="padding:5px 8px; font-size:12px;">
                            <input type="date" wire:model="tahapanList.{{ $idx }}.selesai" class="form-control" style="padding:5px 8px; font-size:12px;">
                            <button type="button" wire:click="removeTahapan({{ $idx }})" class="btn-action-del">&times;</button>
                        </div>
                    @empty
                        <div style="font-size:11.5px; color:#64748b;">Kosongkan jika hanya menggunakan 1 metode utama.</div>
                    @endforelse
                </div>

                <div style="background:#f8fafc; padding:10px 12px; border-radius:6px; border:1px solid #e2e8f0;">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                        <label class="form-check">
                            <input type="checkbox" wire:model.live="auto_status">
                            <span style="font-weight:600; font-size:12.5px;">Hitung Status Otomatis</span>
                        </label>
                        <div style="display:flex; align-items:center; gap:6px; font-size:12px;">
                            <span>Masa Seleksi: H-</span>
                            <input type="number" wire:model="seleksi_hari" min="0" max="180" class="form-control" style="width:65px; padding:4px 6px;">
                            <span>hari</span>
                        </div>
                    </div>

                    <div style="margin-top:8px; padding-top:8px; border-top:1px solid #e2e8f0;">
                        <label class="form-check">
                            <input type="checkbox" wire:model="is_published">
                            <span style="font-weight:600; font-size:12.5px; color:#0f172a;">Tampilkan di Dashboard &amp; Kalender Publik</span>
                            <span style="font-size:11.5px; color:#64748b;">(kosongkan jika masih Draft/Rencana)</span>
                        </label>
                    </div>

                    @if(!$auto_status)
                    <div style="margin-top:8px;">
                        <label class="form-label">Status Manual:</label>
                        <select wire:model="status" class="form-control">
                            <option value="Belum Dimulai">Belum Dimulai</option>
                            <option value="Tahap Seleksi">Tahap Seleksi</option>
                            <option value="Sedang Berlangsung">Sedang Berlangsung</option>
                            <option value="Selesai">Selesai</option>
                        </select>
                    </div>
                    @endif
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" wire:click="closeModal" class="btn-secondary">Batal</button>
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove>{{ $modalMode === 'create' ? 'Simpan' : 'Perbarui' }}</span>
                    <span wire:loading>Menyimpan...</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endif
