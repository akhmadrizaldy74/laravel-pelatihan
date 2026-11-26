<div class="panel-card" style="margin-bottom:14px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
    <div>
        <h1 style="font-size:18px; font-weight:700; color:#0f172a;">
            Panel Kelola Pelatihan PPSDMK — {{ $tahun }}
        </h1>
        <p style="font-size:12px; color:#64748b; margin-top:2px;">
            Manajemen jadwal, template Excel, kalender Gantt, dan laporan pelatihan.
        </p>
    </div>

    <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
        <select wire:model.live="tahun" class="form-control" style="width:auto; font-weight:600;" aria-label="Pilih Tahun">
            @foreach($availableYears as $yr)
                <option value="{{ $yr }}">Tahun {{ $yr }}</option>
            @endforeach
        </select>

        <button type="button" wire:click="exportExcel" class="btn-secondary">Export Excel</button>
        <a href="{{ route('public.kalender.print', ['tahun' => $tahun]) }}" target="_blank" class="btn-secondary">Cetak PDF</a>

        @if($canManage)
            <button type="button" wire:click="togglePublicKalender"
                    class="btn-secondary"
                    style="border-color:{{ $showPublicKalender ? '#16a34a' : '#dc2626' }}; color:{{ $showPublicKalender ? '#15803d' : '#b91c1c' }};"
                    title="Tampilkan atau sembunyikan bagian Kalender Gantt di halaman publik">
                Kalender Publik: {{ $showPublicKalender ? 'Tampil' : 'Disembunyikan' }}
            </button>
            <button type="button" wire:click="cloneFromPreviousYear" class="btn-secondary" title="Salin semua jadwal dari tahun {{ $tahun - 1 }} ke {{ $tahun }} sebagai Draft">
                Salin dari {{ $tahun - 1 }}
            </button>
            <a href="{{ route('admin.template.kalender.download', ['tahun' => $tahun]) }}" class="btn-secondary" title="Unduh template Excel siap isi">Template Excel</a>
            <button type="button" wire:click="openImportModal" class="btn-success">Upload Excel</button>
            <button type="button" wire:click="openCreate" class="btn-primary">+ Tambah Pelatihan</button>
        @endif
    </div>
</div>

@if($canManage && array_sum($statusCounts) > 0)
    <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:6px; padding:8px 12px; margin-bottom:12px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; font-size:12px;">
        <div>
            Status Publikasi Tahun <strong>{{ $tahun }}</strong>:
            <strong style="color:#16a34a;">{{ array_sum($statusCounts) - $draftCount }} Tampil</strong>
            &middot;
            <strong style="color:#64748b;">{{ $draftCount }} Draft (Disembunyikan)</strong>
        </div>
        <div style="display:flex; gap:6px; flex-wrap:wrap;">
            @if($draftCount > 0)
                <button type="button" wire:click="publishAllYear" class="btn-action-edit" style="border-color:#86efac; color:#15803d;">
                    Tampilkan Semua ({{ $tahun }})
                </button>
            @endif
            @if((array_sum($statusCounts) - $draftCount) > 0)
                <button type="button" wire:click="hideAllYear" class="btn-action-del" style="border-color:#cbd5e1; color:#475569;">
                    Jadikan Draft Semua
                </button>
            @endif
        </div>
    </div>
@endif

@if(!empty($importWarnings))
    <div class="alert-inline">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
            <strong style="color:#92400e; font-size:12.5px;">Catatan Import ({{ count($importWarnings) }} baris dilewati)</strong>
            <button type="button" wire:click="clearImportWarnings" class="notif-close" aria-label="Tutup catatan">&times;</button>
        </div>
        <ul style="margin-left:18px; font-size:12px; color:#475569; max-height:120px; overflow-y:auto;">
            @foreach($importWarnings as $warn)
                <li>{{ $warn }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="admin-stats-row">
    <div class="admin-stat-card {{ $filterStatus === '' ? 'active-filter' : '' }}" wire:click="$set('filterStatus', '')">
        <span class="admin-stat-label">Total</span>
        <span class="admin-stat-number">{{ array_sum($statusCounts) }}</span>
    </div>
    <div class="admin-stat-card {{ $filterStatus === 'Sedang Berlangsung' ? 'active-filter' : '' }}" wire:click="$set('filterStatus', '{{ $filterStatus === 'Sedang Berlangsung' ? '' : 'Sedang Berlangsung' }}')">
        <span class="admin-stat-label">Berlangsung</span>
        <span class="admin-stat-number" style="color:#2563eb;">{{ $statusCounts['Sedang Berlangsung'] }}</span>
    </div>
    <div class="admin-stat-card {{ $filterStatus === 'Tahap Seleksi' ? 'active-filter' : '' }}" wire:click="$set('filterStatus', '{{ $filterStatus === 'Tahap Seleksi' ? '' : 'Tahap Seleksi' }}')">
        <span class="admin-stat-label">Seleksi</span>
        <span class="admin-stat-number" style="color:#d97706;">{{ $statusCounts['Tahap Seleksi'] }}</span>
    </div>
    <div class="admin-stat-card {{ $filterStatus === 'Selesai' ? 'active-filter' : '' }}" wire:click="$set('filterStatus', '{{ $filterStatus === 'Selesai' ? '' : 'Selesai' }}')">
        <span class="admin-stat-label">Selesai</span>
        <span class="admin-stat-number" style="color:#16a34a;">{{ $statusCounts['Selesai'] }}</span>
    </div>
    <div class="admin-stat-card {{ $filterStatus === 'Belum Dimulai' ? 'active-filter' : '' }}" wire:click="$set('filterStatus', '{{ $filterStatus === 'Belum Dimulai' ? '' : 'Belum Dimulai' }}')">
        <span class="admin-stat-label">Belum Dimulai</span>
        <span class="admin-stat-number" style="color:#64748b;">{{ $statusCounts['Belum Dimulai'] }}</span>
    </div>
</div>
