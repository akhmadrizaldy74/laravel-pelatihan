{{-- Header Khusus Menu Rekap & Analitik --}}
<div class="tab-header-card">
    <div>
        <h1 class="tab-header-title">Rekap &amp; Analitik PPSDMK — {{ $tahun }}</h1>
        <p class="tab-header-sub">Ringkasan peserta, kepadatan jadwal bulanan, dan perencanaan tahun berikutnya.</p>
    </div>
    <div class="tab-header-actions">
        <select wire:model.live="tahun" class="form-control" style="width:auto; font-weight:600;" aria-label="Pilih Tahun">
            @foreach($availableYears as $yr)
                <option value="{{ $yr }}">Tahun {{ $yr }}</option>
            @endforeach
        </select>
        @if($canManage)
            <button type="button" wire:click="cloneFromPreviousYear" class="btn-secondary" title="Salin semua jadwal dari tahun {{ $tahun - 1 }} ke {{ $tahun }} sebagai Draft">
                Salin Rencana dari {{ $tahun - 1 }}
            </button>
        @endif
        <button type="button" wire:click="exportExcel" class="btn-primary">Export Excel</button>
    </div>
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px; margin-bottom:16px;">
    <div class="panel-card">
        <div style="font-size:11.5px; color:#64748b; font-weight:600;">Target Peserta ({{ $tahun }})</div>
        <div style="font-size:24px; font-weight:700; color:#0f172a; margin-top:4px;">{{ number_format($yearRecords->sum('jumlah_peserta'), 0, ',', '.') }}</div>
    </div>
    <div class="panel-card">
        <div style="font-size:11.5px; color:#64748b; font-weight:600;">Terdaftar</div>
        <div style="font-size:24px; font-weight:700; color:#2563eb; margin-top:4px;">{{ number_format($yearRecords->sum('peserta_terdaftar'), 0, ',', '.') }}</div>
    </div>
    <div class="panel-card">
        <div style="font-size:11.5px; color:#64748b; font-weight:600;">Realisasi Lulus</div>
        <div style="font-size:24px; font-weight:700; color:#16a34a; margin-top:4px;">{{ number_format($yearRecords->sum('peserta_lulus'), 0, ',', '.') }}</div>
    </div>
    <div class="panel-card">
        <div style="font-size:11.5px; color:#64748b; font-weight:600;">Total Jam Pelajaran</div>
        <div style="font-size:24px; font-weight:700; color:#334155; margin-top:4px;">{{ number_format($yearRecords->sum('jam_pelajaran'), 0, ',', '.') }} JP</div>
    </div>
</div>

<div class="panel-card" style="margin-bottom:16px;">
    <h3 style="font-size:14px; font-weight:700; color:#0f172a; margin-bottom:12px;">Beban Pelatihan Aktif per Bulan ({{ $tahun }})</h3>
    @php $maxMonthly = max(1, collect($monthlyLoad)->max('jumlah')); @endphp
    <div style="display:grid; grid-template-columns:repeat(12, 1fr); gap:8px; align-items:end; height:150px; padding-top:12px; border-bottom:1px solid #e2e8f0;">
        @foreach($monthlyLoad as $m)
            @php $pct = round(($m['jumlah'] / $maxMonthly) * 100); @endphp
            <div style="display:flex; flex-direction:column; align-items:center; justify-content:flex-end; height:100%;">
                <div style="font-size:11px; font-weight:700; color:#1d4ed8; margin-bottom:3px;">{{ $m['jumlah'] }}</div>
                <div style="width:100%; max-width:34px; height:{{ max(6, $pct) }}%; background:#2563eb; border-radius:3px 3px 0 0;"
                     title="{{ $m['bulan'] }}: {{ $m['jumlah'] }} pelatihan ({{ $m['peserta'] }} peserta)"></div>
            </div>
        @endforeach
    </div>
    <div style="display:grid; grid-template-columns:repeat(12, 1fr); gap:8px; margin-top:6px;">
        @foreach($monthlyLoad as $m)
            <div style="text-align:center; font-size:11px; font-weight:600; color:#475569;">
                {{ $m['bulan'] }}
                <div style="font-size:10px; color:#94a3b8;">{{ $m['peserta'] }} org</div>
            </div>
        @endforeach
    </div>
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:14px;">
    <div class="panel-card">
        <h4 style="font-size:13px; font-weight:700; margin-bottom:8px;">Rekap per Kategori</h4>
        @forelse($byKategori as $kat => $items)
            <div style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f1f5f9; font-size:12.5px;">
                <span>{{ $kat }}</span>
                <strong>{{ $items->count() }} keg ({{ $items->sum('jumlah_peserta') }} org)</strong>
            </div>
        @empty
            <div style="color:#94a3b8; font-size:12px;">Belum ada data.</div>
        @endforelse
    </div>
    <div class="panel-card">
        <h4 style="font-size:13px; font-weight:700; margin-bottom:8px;">Rekap per Sumber Dana</h4>
        @forelse($bySumberDana as $sd => $items)
            <div style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f1f5f9; font-size:12.5px;">
                <span>{{ $sd }}</span>
                <strong>{{ $items->count() }} keg ({{ $items->sum('jumlah_peserta') }} org)</strong>
            </div>
        @empty
            <div style="color:#94a3b8; font-size:12px;">Belum ada data.</div>
        @endforelse
    </div>
    <div class="panel-card">
        <h4 style="font-size:13px; font-weight:700; margin-bottom:8px;">Rekap per Lokasi</h4>
        @forelse($byTempat as $tp => $items)
            <div style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f1f5f9; font-size:12.5px;">
                <span>{{ $tp }}</span>
                <strong>{{ $items->count() }} keg ({{ $items->sum('jumlah_peserta') }} org)</strong>
            </div>
        @empty
            <div style="color:#94a3b8; font-size:12px;">Belum ada data.</div>
        @endforelse
    </div>
</div>
