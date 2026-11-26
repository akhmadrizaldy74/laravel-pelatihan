{{-- Header Khusus Menu Kalender Gantt --}}
<div class="tab-header-card">
    <div>
        <h1 class="tab-header-title">Kalender Pelatihan — Tahun {{ $tahun }}</h1>
        <p class="tab-header-sub">Klik nama pelatihan atau batang jadwal untuk mengubah tahapan.</p>
    </div>
    <div class="tab-header-actions">
        <select wire:model.live="tahun" class="form-control" style="width:auto; font-weight:600;" aria-label="Pilih Tahun">
            @foreach($availableYears as $yr)
                <option value="{{ $yr }}">Tahun {{ $yr }}</option>
            @endforeach
        </select>
        <a href="{{ route('public.kalender.print', ['tahun' => $tahun]) }}" target="_blank" class="btn-secondary">Cetak PDF</a>
        @if($canManage)
            <button type="button" wire:click="togglePublicKalender"
                    class="{{ $showPublicKalender ? 'btn-success' : 'btn-danger' }}">
                Kalender Publik: {{ $showPublicKalender ? 'Tampil' : 'Disembunyikan' }}
            </button>
        @endif
    </div>
</div>

<div class="panel-card">

    <div style="overflow-x:auto; border:1px solid #94a3b8; border-radius:4px;">
        <table style="border-collapse:separate; border-spacing:0; width:100%; min-width:1680px; font-size:11px; table-layout:fixed;">
            <thead>
                <tr style="background:#e2e8f0;">
                    <th rowspan="2" style="position:sticky; left:0; z-index:40; background:#e2e8f0; border-right:1px solid #94a3b8; border-bottom:1px solid #94a3b8; padding:6px 8px; width:295px; text-align:left;">JENIS PELATIHAN</th>
                    <th rowspan="2" style="position:sticky; left:295px; z-index:40; background:#e2e8f0; border-right:1px solid #94a3b8; border-bottom:1px solid #94a3b8; padding:6px 4px; width:68px; text-align:center;">TARGET</th>
                    <th rowspan="2" style="position:sticky; left:363px; z-index:40; background:#e2e8f0; border-right:2px solid #64748b; border-bottom:1px solid #94a3b8; padding:6px 4px; width:58px; text-align:center; box-shadow:3px 0 6px -2px rgba(15,23,42,0.12);">DURASI</th>
                    @php $adminMonths = ['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGS', 'SEP', 'OKT', 'NOV', 'DES']; @endphp
                    @foreach($adminMonths as $am)
                        <th colspan="5" style="border-right:1px solid #94a3b8; border-bottom:1px solid #94a3b8; padding:4px 2px; text-align:center; font-size:10.5px; background:#cbd5e1;">{{ $am }}</th>
                    @endforeach
                    <th rowspan="2" style="border-right:1px solid #94a3b8; border-bottom:1px solid #94a3b8; padding:6px 4px; width:64px; text-align:center;">DANA</th>
                    <th rowspan="2" style="border-bottom:1px solid #94a3b8; padding:6px 4px; width:72px; text-align:center;">TEMPAT</th>
                </tr>
                <tr>
                    @foreach($adminMonths as $am)
                        @for($w = 1; $w <= 5; $w++)
                            <th style="border-right:1px solid #cbd5e1; border-bottom:1px solid #94a3b8; padding:2px 0; width:19px; text-align:center; font-size:9.5px; color:#475569; background:#f8fafc;">{{ $w }}</th>
                        @endfor
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($kalenderGroups as $catName => $items)
                    <tr style="background:#f1f5f9; font-weight:700;">
                        <td colspan="65" style="padding:5px 10px; border-top:1px solid #94a3b8; border-bottom:1px solid #94a3b8; color:#0f172a;">
                            <span style="position:sticky; left:10px; display:inline-block; z-index:31;">
                                ▼ {{ strtoupper($catName) }} ({{ $items->count() }})
                            </span>
                        </td>
                    </tr>
                    @foreach($items as $idx => $item)
                        @php $rowBg = ($idx % 2 === 0) ? '#ffffff' : '#f8fafc'; @endphp
                        <tr style="background:{{ $rowBg }};">
                            <td @if($canManage) wire:click="openEdit({{ $item->id }})" @endif
                                style="position:sticky; left:0; z-index:30; background:{{ $rowBg }}; padding:4px 8px; border-right:1px solid #cbd5e1; border-bottom:1px solid #cbd5e1; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; {{ $canManage ? 'cursor:pointer;' : '' }}"
                                title="{{ $canManage ? 'Klik untuk edit: ' : '' }}{{ $item->nama_pelatihan }}">
                                @if(!$item->is_published)
                                    <span style="font-size:9.5px; background:#e2e8f0; color:#475569; padding:1px 4px; border-radius:2px; margin-right:4px;">Draft</span>
                                @endif
                                {{ $item->nama_pelatihan }}
                            </td>
                            <td style="position:sticky; left:295px; z-index:30; background:{{ $rowBg }}; padding:4px; text-align:center; border-right:1px solid #cbd5e1; border-bottom:1px solid #cbd5e1; font-weight:700;">{{ $item->jumlah_peserta }}</td>
                            <td style="position:sticky; left:363px; z-index:30; background:{{ $rowBg }}; padding:4px; text-align:center; border-right:2px solid #64748b; border-bottom:1px solid #cbd5e1; font-weight:600; color:#475569; box-shadow:3px 0 6px -2px rgba(15,23,42,0.12);">{{ $item->durasi_display }}</td>

                            <td colspan="60" style="position:relative; height:26px; padding:0; overflow:hidden; border-right:1px solid #cbd5e1; border-bottom:1px solid #cbd5e1; background:repeating-linear-gradient(90deg, transparent, transparent calc(100%/12 - 1px), #cbd5e1 calc(100%/12 - 1px), #cbd5e1 calc(100%/12));">
                                @foreach($hariLibursYear as $libur)
                                    @if($band = $libur->band_position)
                                        <div style="position:absolute; top:0; bottom:0; left:{{ $band['left'] }}; width:{{ $band['width'] }}; background:{{ $libur->warna }}22; border-left:1px dashed {{ $libur->warna }}88; border-right:1px dashed {{ $libur->warna }}88; pointer-events:none; z-index:1;"
                                             title="{{ $libur->periode_display }}: {{ $libur->nama }}"></div>
                                    @endif
                                @endforeach

                                @if($todayMarkerLeft)
                                    <div style="position:absolute; top:0; bottom:0; left:{{ $todayMarkerLeft }}; width:2px; border-left:2px dashed #dc2626; z-index:3; pointer-events:none;" title="Hari Ini"></div>
                                @endif

                                @foreach($item->getTimelineBarsForYear($tahun) as $bar)
                                    <div @if($canManage) wire:click="openEdit({{ $item->id }})" @endif
                                         style="position:absolute; top:3px; bottom:3px; min-width:max-content; left:{{ $bar['left'] }}; width:{{ $bar['width'] }}; background:{{ $bar['color'] }}; color:{{ $bar['textColor'] }}; border-radius:2px; display:inline-flex; align-items:center; justify-content:center; font-size:9.5px; font-weight:700; white-space:nowrap; padding:0 5px; z-index:5; cursor:pointer;"
                                         title="{{ $item->nama_pelatihan }} | {{ $bar['tooltip'] }}">
                                        {{ $bar['label'] }}
                                    </div>
                                @endforeach
                            </td>

                            <td style="padding:4px; text-align:center; border-right:1px solid #cbd5e1; border-bottom:1px solid #cbd5e1; font-weight:700;">{{ $item->sumber_dana ?: 'RM' }}</td>
                            <td style="padding:4px; text-align:center; border-bottom:1px solid #cbd5e1; font-weight:700;">{{ $item->tempat_pelaksanaan ?: 'PPSDM' }}</td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="65" style="padding:26px; text-align:center; color:#94a3b8;">Belum ada jadwal pada Tahun {{ $tahun }}.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:12px; display:flex; flex-wrap:wrap; gap:8px;">
        @foreach($metodes as $m)
            <span style="font-size:11px; font-weight:600; padding:2px 8px; border-radius:3px; background:{{ $m->kode_warna }}; color:{{ $m->warna_teks }};">
                {{ $m->nama }}
            </span>
        @endforeach
    </div>
</div>
