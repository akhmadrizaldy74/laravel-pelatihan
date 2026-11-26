@if($showImportModal)
<div class="modal-overlay">
    <div class="modal-box" style="max-width:650px;">
        <div class="modal-header">
            <h3>Upload Jadwal dari Excel</h3>
            <button type="button" wire:click="closeImportModal" class="modal-close">&times;</button>
        </div>

        <form wire:submit.prevent="importExcel">
            <div class="modal-body">
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:11px 13px; margin-bottom:14px; display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
                    <div>
                        <div style="font-weight:700; font-size:12.5px; color:#0f172a;">1. Gunakan Template Excel Resmi</div>
                        <div style="font-size:12px; color:#475569;">Isi 5 kolom wajib bertanda (*) pada sheet ISI_DATA_PELATIHAN.</div>
                    </div>
                    <a href="{{ route('admin.template.kalender.download', ['tahun' => $tahun]) }}" class="btn-secondary">
                        Unduh Template (.xlsx)
                    </a>
                </div>

                <div class="form-group-full">
                    <label class="form-label">2. Pilih File Excel / CSV <span style="color:#dc2626;">*</span></label>
                    <div style="border:1.5px dashed #94a3b8; border-radius:6px; padding:14px; text-align:center; background:#f8fafc;">
                        <input type="file" id="excelFileInput" wire:model="fileExcel" accept=".xlsx,.xls,.csv" style="width:100%; font-size:13px; cursor:pointer;">
                        <div style="font-size:11.5px; color:#64748b; margin-top:5px;">Format: .xlsx, .xls, atau .csv (Maks 10MB)</div>
                    </div>

                    <div wire:loading wire:target="fileExcel" style="font-size:12px; color:#15803d; margin-top:6px; font-weight:600;">
                        Memeriksa isi file...
                    </div>

                    @error('fileExcel')
                        <div style="color:#dc2626; font-size:12px; margin-top:6px;">{{ $message }}</div>
                    @enderror

                    @if(!empty($importErrors))
                        <div class="alert-inline-error" style="margin-top:8px;">
                            @foreach($importErrors as $err)
                                <div>{{ $err }}</div>
                            @endforeach
                        </div>
                    @endif

                    @if($importPreview)
                        <div style="margin-top:10px; border:1px solid #bbf7d0; background:#f0fdf4; border-radius:6px; padding:10px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; font-size:12.5px;">
                                <strong style="color:#166534;">Siap import: {{ $importPreview['valid_count'] }} data valid</strong>
                                @if($importPreview['skipped'] > 0)
                                    <span style="color:#b45309; font-weight:600;">{{ $importPreview['skipped'] }} baris dilewati</span>
                                @endif
                            </div>

                            @if(!empty($importPreview['sample']))
                                <div style="overflow-x:auto; background:#ffffff; border:1px solid #cbd5e1; border-radius:4px;">
                                    <table style="width:100%; border-collapse:collapse; font-size:11.5px;">
                                        <thead>
                                            <tr style="background:#f1f5f9; text-align:left;">
                                                <th style="padding:5px 8px; border-bottom:1px solid #cbd5e1;">Kategori</th>
                                                <th style="padding:5px 8px; border-bottom:1px solid #cbd5e1;">Nama Pelatihan</th>
                                                <th style="padding:5px 8px; border-bottom:1px solid #cbd5e1;">Peserta</th>
                                                <th style="padding:5px 8px; border-bottom:1px solid #cbd5e1;">Tanggal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($importPreview['sample'] as $row)
                                                <tr style="border-bottom:1px solid #f1f5f9;">
                                                    <td style="padding:4px 8px;">{{ $row['kategori'] }}</td>
                                                    <td style="padding:4px 8px; font-weight:600;">{{ $row['nama_pelatihan'] }}</td>
                                                    <td style="padding:4px 8px;">{{ $row['jumlah_peserta'] }} org</td>
                                                    <td style="padding:4px 8px; white-space:nowrap;">{{ $row['tanggal_mulai'] }} s/d {{ $row['tanggal_selesai'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:11px 13px;">
                    <label class="form-label">3. Opsi Penyimpanan</label>
                    <label style="display:flex; align-items:flex-start; gap:8px; margin-bottom:6px; cursor:pointer; font-size:12.5px;">
                        <input type="radio" wire:model="importMode" value="replace_year" style="margin-top:3px;">
                        <span><strong>Ganti Jadwal Tahun di File Ini Saja (Rekomendasi)</strong> — Mengganti jadwal pada tahun yang ada di file Excel; arsip tahun lain tetap aman.</span>
                    </label>
                    <label style="display:flex; align-items:flex-start; gap:8px; margin-bottom:6px; cursor:pointer; font-size:12.5px;">
                        <input type="radio" wire:model="importMode" value="append" style="margin-top:3px;">
                        <span><strong>Tambahkan</strong> — Pertahankan semua data lama dan tambah baris baru.</span>
                    </label>
                    <label style="display:flex; align-items:flex-start; gap:8px; cursor:pointer; font-size:12.5px; margin-bottom:10px;">
                        <input type="radio" wire:model="importMode" value="replace" style="margin-top:3px;">
                        <span><strong>Reset Total Semua Tahun</strong> — Hapus seluruh data di semua tahun dan ganti dengan file ini.</span>
                    </label>
                    <div style="border-top:1px solid #e2e8f0; padding-top:8px;">
                        <label class="form-check" style="font-size:12.5px;">
                            <input type="checkbox" wire:model="importAsDraft">
                            <span><strong>Simpan sebagai Draft (Rencana)</strong> — Tidak langsung tampil di publik sebelum diterbitkan.</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="justify-content:space-between;">
                <button type="button" wire:click="closeImportModal" class="btn-secondary">Batal</button>
                <button type="submit" class="btn-success" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="importExcel">Simpan &amp; Terapkan</span>
                    <span wire:loading wire:target="importExcel">Memproses...</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endif
