@if($detailPelatihan)
<div class="detail-modal-overlay" wire:click.self="closeDetail">
    <div class="detail-modal-box">
        <div class="detail-modal-header">
            <div>
                <div style="font-size:0.72rem; font-weight:700; opacity:0.88; text-transform:uppercase; letter-spacing:0.6px;">
                    {{ $detailPelatihan->kategori }}
                    @if($detailPelatihan->kode_pelatihan)
                        &middot; Kode: {{ $detailPelatihan->kode_pelatihan }}
                    @endif
                </div>
                <h3 style="font-size:1.15rem; font-weight:800; margin-top:4px; line-height:1.3;">
                    {{ $detailPelatihan->nama_pelatihan }}
                </h3>
            </div>
            <button type="button" wire:click="closeDetail" class="detail-modal-close">&times;</button>
        </div>

        <div class="detail-modal-body">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:14px;">
                <span class="status-pill {{ $detailPelatihan->status_color }}">
                    {{ $detailPelatihan->computed_status }}
                </span>
                <span style="font-size:0.78rem; color:#64748b; font-weight:600;">
                    Seleksi otomatis: H-{{ $detailPelatihan->seleksi_hari ?? 7 }} sebelum mulai
                </span>
            </div>

            @if($detailPelatihan->deskripsi)
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 14px; font-size:0.84rem; color:#334155; line-height:1.5; margin-bottom:14px;">
                    {{ $detailPelatihan->deskripsi }}
                </div>
            @endif

            <div class="detail-grid">
                <div class="detail-info-item">
                    <span class="detail-label">Periode Pelaksanaan</span>
                    <span class="detail-value">
                        {{ $detailPelatihan->tanggal_mulai?->format('d M Y') }} – {{ $detailPelatihan->tanggal_selesai?->format('d M Y') }}
                    </span>
                </div>
                <div class="detail-info-item">
                    <span class="detail-label">Durasi &amp; Jam Pelajaran</span>
                    <span class="detail-value">
                        {{ $detailPelatihan->durasi_display }}
                        @if($detailPelatihan->jam_pelajaran)
                            ({{ $detailPelatihan->jam_pelajaran }} JP)
                        @endif
                    </span>
                </div>
                <div class="detail-info-item">
                    <span class="detail-label">Kuota / Terdaftar / Lulus</span>
                    <span class="detail-value">
                        Target {{ $detailPelatihan->jumlah_peserta }} org &middot;
                        Daftar {{ $detailPelatihan->peserta_terdaftar }} org &middot;
                        Lulus {{ $detailPelatihan->peserta_lulus }} org
                    </span>
                </div>
                <div class="detail-info-item">
                    <span class="detail-label">Sumber Dana &amp; Lokasi</span>
                    <span class="detail-value">
                        {{ $detailPelatihan->sumber_dana ?: 'RM' }} &middot; {{ $detailPelatihan->tempat_pelaksanaan ?: 'PPSDM' }}
                    </span>
                </div>
                @if($detailPelatihan->penyelenggara || $detailPelatihan->angkatan)
                    <div class="detail-info-item">
                        <span class="detail-label">Penyelenggara / Angkatan</span>
                        <span class="detail-value">
                            {{ $detailPelatihan->penyelenggara ?: '-' }}
                            @if($detailPelatihan->angkatan)
                                ({{ $detailPelatihan->angkatan }})
                            @endif
                        </span>
                    </div>
                @endif
                @if($detailPelatihan->pic_nama || $detailPelatihan->pic_kontak)
                    <div class="detail-info-item">
                        <span class="detail-label">Narahubung / PIC</span>
                        <span class="detail-value">
                            {{ $detailPelatihan->pic_nama ?: '-' }}
                            @if($detailPelatihan->pic_kontak)
                                ({{ $detailPelatihan->pic_kontak }})
                            @endif
                        </span>
                    </div>
                @endif
            </div>

            @if(!empty($detailPelatihan->jadwal_tahapan) && is_array($detailPelatihan->jadwal_tahapan))
                <div style="margin-top:16px;">
                    <div style="font-size:0.8rem; font-weight:800; color:#334155; text-transform:uppercase; margin-bottom:8px;">
                        Rincian Tahapan Pembelajaran
                    </div>
                    <div style="display:flex; flex-direction:column; gap:6px;">
                        @foreach($detailPelatihan->jadwal_tahapan as $tahap)
                            @php
                                $tMetode = $tahap['tahap'] ?? $tahap['metode'] ?? 'Tahap';
                                $tColor  = \App\Models\Pelatihan::getMetodeColor($tMetode);
                                $tText   = \App\Models\Pelatihan::getMetodeTextColor($tMetode);
                            @endphp
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:7px 10px; border-radius:8px; background:#f8fafc; border:1px solid #e2e8f0; gap:10px; flex-wrap:wrap;">
                                <span style="font-size:0.76rem; font-weight:800; padding:3px 10px; border-radius:4px; background:{{ $tColor }}; color:{{ $tText }};">
                                    {{ $tMetode }}
                                </span>
                                <span style="font-size:0.82rem; font-weight:700; color:#334155;">
                                    {{ \Carbon\Carbon::parse($tahap['mulai'])->format('d M Y') }} – {{ \Carbon\Carbon::parse($tahap['selesai'])->format('d M Y') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="detail-modal-footer">
            @if($detailPelatihan->link_pendaftaran)
                <a href="{{ $detailPelatihan->link_pendaftaran }}" target="_blank" rel="noopener noreferrer" class="btn-admin-link" style="text-decoration:none;">
                    <span>Buka Tautan Pendaftaran / LMS</span>
                </a>
            @endif
            <button type="button" wire:click="closeDetail" class="btn-reset-filter" style="padding:8px 18px;">
                Tutup
            </button>
        </div>
    </div>
</div>
@endif
