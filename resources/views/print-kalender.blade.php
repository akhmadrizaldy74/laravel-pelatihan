<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kalender Pelatihan Tahun {{ $tahun }} — PPSDMK</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #0f172a;
            background: #ffffff;
            padding: 20px;
            font-size: 11px;
        }
        .print-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding: 12px 16px;
            background: #f1f5f9;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
        }
        .btn-print {
            background: #1565c0;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
            font-size: 12px;
        }
        .btn-back {
            background: #e2e8f0;
            color: #1e293b;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 12px;
        }
        .doc-header {
            border-bottom: 3px solid #1565c0;
            padding-bottom: 10px;
            margin-bottom: 14px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .doc-title { font-size: 16px; font-weight: 800; color: #0d47a1; }
        .doc-sub { font-size: 11px; color: #475569; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #cbd5e1; padding: 5px 6px; text-align: left; vertical-align: middle; }
        th { background: #1565c0; color: #ffffff; font-weight: 700; font-size: 10px; text-transform: uppercase; text-align: center; }
        .cat-row td { background: #e2e8f0; font-weight: 800; font-size: 10.5px; color: #0f172a; }
        .text-center { text-align: center; }
        .track-cell { position: relative; height: 22px; padding: 0; background: #ffffff; }
        .gantt-bar {
            position: absolute;
            top: 3px;
            bottom: 3px;
            min-width: max-content;
            padding: 0 4px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }
        .legend-wrap { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
        .legend-chip { padding: 3px 8px; border-radius: 4px; font-size: 9.5px; font-weight: 700; color: #fff; }
        @media print {
            .print-toolbar { display: none; }
            body { padding: 0; }
            @page { size: A4 landscape; margin: 10mm; }
        }
    </style>
</head>
<body>
    <div class="print-toolbar">
        <div>
            <strong>Mode Cetak / Simpan PDF</strong> — Gunakan dialog cetak browser lalu pilih <em>Save as PDF</em> (disarankan orientasi Landscape).
        </div>
        <div style="display:flex; gap:8px;">
            <a href="{{ url('/') }}" class="btn-back">← Kembali</a>
            <button type="button" onclick="window.print()" class="btn-print">🖨️ Cetak / Simpan PDF</button>
        </div>
    </div>

    <div class="doc-header">
        <div>
            <div class="doc-title">KALENDER & JADWAL PELATIHAN PPSDMK TAHUN {{ $tahun }}</div>
            <div class="doc-sub">Kemnaker Corporate University — Total {{ $pelatihans->count() }} Pelatihan ({{ number_format($pelatihans->sum('jumlah_peserta'), 0, ',', '.') }} Target Peserta)</div>
        </div>
        <div class="doc-sub">Dicetak: {{ now()->format('d/m/Y H:i') }} WIB</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:24px;">No</th>
                <th style="width:210px;">Nama Pelatihan</th>
                <th style="width:52px;">Target</th>
                <th style="width:45px;">Durasi</th>
                @foreach(['JAN','FEB','MAR','APR','MEI','JUN','JUL','AGS','SEP','OKT','NOV','DES'] as $m)
                    <th>{{ $m }}</th>
                @endforeach
                <th style="width:42px;">Dana</th>
                <th style="width:55px;">Lokasi</th>
                <th style="width:82px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($kalenderGroups as $kat => $items)
                <tr class="cat-row">
                    <td colspan="19">▼ {{ strtoupper($kat) }} ({{ $items->count() }})</td>
                </tr>
                @foreach($items as $item)
                    <tr>
                        <td class="text-center">{{ $no++ }}</td>
                        <td>
                            <strong>{{ $item->nama_pelatihan }}</strong>
                            @if($item->angkatan)
                                <br><span style="color:#475569; font-size:9px;">{{ $item->angkatan }}</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->jumlah_peserta }}</td>
                        <td class="text-center">{{ $item->durasi_display }}</td>
                        <td colspan="12" class="track-cell">
                            @foreach($item->getTimelineBarsForYear($tahun) as $bar)
                                <div class="gantt-bar"
                                     style="left:{{ $bar['left'] }}; width:{{ $bar['width'] }}; background:{{ $bar['color'] }}; color:{{ $bar['textColor'] }};">
                                    {{ $bar['label'] }}
                                </div>
                            @endforeach
                        </td>
                        <td class="text-center">{{ $item->sumber_dana ?: 'RM' }}</td>
                        <td class="text-center">{{ $item->tempat_pelaksanaan ?: 'PPSDM' }}</td>
                        <td class="text-center">{{ $item->computed_status }}</td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="19" class="text-center" style="padding:20px;">Tidak ada data pelatihan pada tahun {{ $tahun }}.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($hariLiburs->isNotEmpty())
        <div style="margin-bottom:8px; color:#b91c1c; font-weight:700;">
            @foreach($hariLiburs as $libur)
                <div>📌 {{ $libur->keterangan ?: ($libur->periode_display . ' — ' . $libur->nama) }}</div>
            @endforeach
        </div>
    @endif

    <div class="legend-wrap">
        @foreach($masterMetodes as $metode)
            <span class="legend-chip" style="background:{{ $metode->kode_warna }}; color:{{ $metode->warna_teks }};">
                ■ {{ $metode->nama }}
            </span>
        @endforeach
    </div>
</body>
</html>
