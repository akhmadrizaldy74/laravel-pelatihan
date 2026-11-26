<div class="kalender-section" id="kalender-pelatihan">
    <div class="kalender-title-row">
        <div class="kalender-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="color:#1565c0;">
                <rect x="3" y="4" width="18" height="18" rx="2" />
                <line x1="16" y1="2" x2="16" y2="6" />
                <line x1="8" y1="2" x2="8" y2="6" />
                <line x1="3" y1="10" x2="21" y2="10" />
            </svg>
            <span>KALENDER PELATIHAN TAHUN {{ $tahun }}</span>
        </div>
        <div class="kalender-scroll-hint">
            Geser tabel ke kanan untuk melihat bulan lengkap (Jan–Des)
        </div>
    </div>

    <div class="kalender-table-wrapper">
        <table class="kalender-table">
            <thead>
                <tr>
                    <th rowspan="2" class="k-sticky-jenis k-th-header-cell">JENIS PELATIHAN</th>
                    <th rowspan="2" class="k-sticky-target k-th-header-cell">TARGET<br>(ORG)</th>
                    <th rowspan="2" class="k-sticky-durasi k-th-header-cell">DURASI</th>

                    @php
                        $months = ['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGS', 'SEP', 'OKT', 'NOV', 'DES'];
                    @endphp
                    @foreach($months as $mName)
                        <th colspan="5" class="k-th-month">{{ $mName }}</th>
                    @endforeach

                    <th rowspan="2" class="k-th-dana">DANA</th>
                    <th rowspan="2" class="k-th-tempat">TEMPAT</th>
                </tr>
                <tr>
                    @foreach($months as $mName)
                        @for($w = 1; $w <= 5; $w++)
                            <th class="k-th-week">{{ $w }}</th>
                        @endfor
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($kalenderGroups as $kategoriName => $items)
                    <tr class="kalender-cat-row">
                        <td colspan="65">
                            <span class="k-cat-sticky">
                                <span style="color:#1565c0;">▼</span> <strong>{{ strtoupper($kategoriName) }}</strong>
                                <span style="font-size:11.5px; font-weight:600; color:#475569; margin-left:6px;">({{ $items->count() }})</span>
                            </span>
                        </td>
                    </tr>

                    @foreach($items as $item)
                        <tr class="kalender-row">
                            <td class="k-sticky-jenis">
                                <div class="k-nama-text"
                                     wire:click="openDetail({{ $item->id }})"
                                     style="cursor:pointer;"
                                     title="Klik untuk detail: {{ $item->nama_pelatihan }}">
                                    {{ $item->nama_pelatihan }}
                                </div>
                            </td>
                            <td class="k-sticky-target">{{ $item->jumlah_peserta }}</td>
                            <td class="k-sticky-durasi">{{ $item->durasi_display }}</td>

                            <td colspan="60" class="kalender-timeline-track">
                                @foreach($hariLiburs as $libur)
                                    @if($band = $libur->band_position)
                                        <div class="k-holiday-band"
                                             style="left: {{ $band['left'] }}; width: {{ $band['width'] }}; background: {{ $libur->warna }}22; border-left: 1px dashed {{ $libur->warna }}88; border-right: 1px dashed {{ $libur->warna }}88;"
                                             title="{{ $libur->periode_display }}: {{ $libur->nama }}"></div>
                                    @endif
                                @endforeach

                                @if($todayMarkerLeft)
                                    <div class="k-today-line" style="left: {{ $todayMarkerLeft }};" title="Hari Ini ({{ now()->format('d M Y') }})"></div>
                                @endif

                                @foreach($item->getTimelineBarsForYear($tahun) as $bar)
                                    <div class="kalender-bar"
                                         wire:click="openDetail({{ $item->id }})"
                                         style="left: {{ $bar['left'] }}; width: {{ $bar['width'] }}; background-color: {{ $bar['color'] }}; color: {{ $bar['textColor'] }};"
                                         title="{{ $item->nama_pelatihan }} — {{ $bar['tooltip'] }}">
                                        {{ $bar['label'] }}
                                    </div>
                                @endforeach
                            </td>

                            <td class="k-cell-dana">{{ $item->sumber_dana ?: 'RM' }}</td>
                            <td class="k-cell-tempat">{{ $item->tempat_pelaksanaan ?: 'PPSDM' }}</td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="65" style="text-align:center; padding:26px; color:#94a3b8;">
                            Belum ada data jadwal pelatihan yang cocok untuk Tahun {{ $tahun }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="kalender-bottom">
        @foreach($hariLiburs as $libur)
            <div class="kalender-catatan" style="color: {{ $libur->warna }};">
                <strong>Catatan: {{ $libur->keterangan ?: ($libur->periode_display . ' — ' . $libur->nama) }}</strong>
            </div>
        @endforeach

        @if($todayMarkerLeft)
            <div style="font-size:11px; font-weight:600; color:#dc2626; margin-bottom:6px; display:flex; align-items:center; gap:6px;">
                <span style="display:inline-block; width:14px; height:2px; background:#dc2626;"></span>
                Garis Merah Putus-putus: Posisi Tanggal Hari Ini ({{ now()->format('d M Y') }})
            </div>
        @endif

        <div class="kalender-legend-title">Keterangan Metode / Tahapan Pembelajaran:</div>
        <div class="kalender-legend-grid">
            @foreach($masterMetodes as $metode)
                <span class="kalender-legend-chip" style="background:{{ $metode->kode_warna }}; color:{{ $metode->warna_teks }};">
                    {{ $metode->nama }}
                </span>
            @endforeach
        </div>
    </div>
</div>
