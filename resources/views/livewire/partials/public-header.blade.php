<div class="dashboard-header">
    <div class="header-left">
        <img src="{{ asset('images/logo-kemnaker.jpg') }}" alt="Logo Kemnaker Corpu" class="header-logo">
        <div class="header-titles">
            <span class="title-eyebrow">PPSDMK &mdash; Kemnaker Corporate University</span>
            <h1 class="title-main">Jadwal Pelatihan Tahun {{ $tahun }}</h1>
            <p class="title-sub">Informasi jadwal, kuota peserta, tahapan pembelajaran, dan kalender pelatihan tahunan.</p>
        </div>
    </div>
    <div class="header-right">
        <div class="header-controls">
            <select wire:model.live="tahun" class="select-year" aria-label="Pilih Tahun Pelatihan">
                @foreach($availableYears as $yr)
                    <option value="{{ $yr }}">Tahun {{ $yr }}</option>
                @endforeach
            </select>

            <div class="update-info-badge" title="Waktu Indonesia Barat (Real-Time)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#64748b;">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 16 14" />
                </svg>
                <span wire:ignore class="realtime-wib-clock">{{ $lastUpdated }} WIB</span>
            </div>

            <a href="{{ route('admin.login') }}" class="btn-admin-link">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                </svg>
                <span>Login Admin</span>
            </a>
        </div>
    </div>
</div>

<div class="stats-row">
    <div class="stat-card stat-total {{ $filterStatus === '' ? 'active-filter' : '' }}" wire:click="$set('filterStatus', '')">
        <div class="stat-top">
            <span class="stat-dot" style="background:#334155;"></span>
            <span class="stat-label">Total Pelatihan</span>
        </div>
        <div class="stat-number">{{ array_sum($statusCounts) }}</div>
    </div>
    <div class="stat-card stat-berlangsung {{ $filterStatus === 'Sedang Berlangsung' ? 'active-filter' : '' }}" wire:click="$set('filterStatus', '{{ $filterStatus === 'Sedang Berlangsung' ? '' : 'Sedang Berlangsung' }}')">
        <div class="stat-top">
            <span class="stat-dot" style="background:#2563eb;"></span>
            <span class="stat-label">Sedang Berlangsung</span>
        </div>
        <div class="stat-number">{{ $statusCounts['Sedang Berlangsung'] }}</div>
    </div>
    <div class="stat-card stat-seleksi {{ $filterStatus === 'Tahap Seleksi' ? 'active-filter' : '' }}" wire:click="$set('filterStatus', '{{ $filterStatus === 'Tahap Seleksi' ? '' : 'Tahap Seleksi' }}')">
        <div class="stat-top">
            <span class="stat-dot" style="background:#d97706;"></span>
            <span class="stat-label">Tahap Seleksi</span>
        </div>
        <div class="stat-number">{{ $statusCounts['Tahap Seleksi'] }}</div>
    </div>
    <div class="stat-card stat-selesai {{ $filterStatus === 'Selesai' ? 'active-filter' : '' }}" wire:click="$set('filterStatus', '{{ $filterStatus === 'Selesai' ? '' : 'Selesai' }}')">
        <div class="stat-top">
            <span class="stat-dot" style="background:#059669;"></span>
            <span class="stat-label">Selesai</span>
        </div>
        <div class="stat-number">{{ $statusCounts['Selesai'] }}</div>
    </div>
    <div class="stat-card stat-belum {{ $filterStatus === 'Belum Dimulai' ? 'active-filter' : '' }}" wire:click="$set('filterStatus', '{{ $filterStatus === 'Belum Dimulai' ? '' : 'Belum Dimulai' }}')">
        <div class="stat-top">
            <span class="stat-dot" style="background:#64748b;"></span>
            <span class="stat-label">Belum Dimulai</span>
        </div>
        <div class="stat-number">{{ $statusCounts['Belum Dimulai'] }}</div>
    </div>
</div>

<div class="kpi-summary-bar">
    <div class="kpi-metrics">
        <div class="kpi-inline-item">
            <span class="kpi-inline-label">Target Peserta</span>
            <strong class="kpi-inline-val">{{ number_format($totalTarget, 0, ',', '.') }} org</strong>
        </div>
        <span class="kpi-sep">&middot;</span>
        <div class="kpi-inline-item">
            <span class="kpi-inline-label">Terdaftar</span>
            <strong class="kpi-inline-val" style="color:#0284c7;">{{ number_format($totalTerdaftar, 0, ',', '.') }} org</strong>
        </div>
        <span class="kpi-sep">&middot;</span>
        <div class="kpi-inline-item">
            <span class="kpi-inline-label">Realisasi Lulus</span>
            <strong class="kpi-inline-val" style="color:#059669;">
                {{ number_format($totalLulus, 0, ',', '.') }} org
                @if($totalTarget > 0)
                    <span style="font-weight:600; color:#64748b;">({{ round(($totalLulus / $totalTarget) * 100, 1) }}%)</span>
                @endif
            </strong>
        </div>
    </div>
    <div class="kpi-hint">
        Klik baris tabel atau batang kalender untuk melihat rincian pelatihan
    </div>
</div>
