<div class="filter-row">
    <div class="search-box">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:#64748b; flex-shrink:0;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama pelatihan, kode, angkatan, atau unit..." class="search-input" />
        @if($search)
            <button type="button" wire:click="$set('search', '')" style="background:none; border:none; color:#94a3b8; cursor:pointer; font-size:1.1rem; padding:0 4px;">&times;</button>
        @endif
    </div>

    <select wire:model.live="filterKategori" class="select-filter" aria-label="Filter Kategori">
        <option value="">Semua Kategori</option>
        @foreach($availableKategoris as $kat)
            <option value="{{ $kat }}">{{ $kat }}</option>
        @endforeach
    </select>

    <select wire:model.live="filterStatus" class="select-filter" aria-label="Filter Status">
        <option value="">Semua Status ({{ array_sum($statusCounts) }})</option>
        <option value="Sedang Berlangsung">Sedang Berlangsung ({{ $statusCounts['Sedang Berlangsung'] }})</option>
        <option value="Tahap Seleksi">Tahap Seleksi ({{ $statusCounts['Tahap Seleksi'] }})</option>
        <option value="Selesai">Selesai ({{ $statusCounts['Selesai'] }})</option>
        <option value="Belum Dimulai">Belum Dimulai ({{ $statusCounts['Belum Dimulai'] }})</option>
    </select>

    @if(count($availableSumberDana) > 1)
        <select wire:model.live="filterSumberDana" class="select-filter" aria-label="Filter Sumber Dana">
            <option value="">Semua Dana</option>
            @foreach($availableSumberDana as $sd)
                <option value="{{ $sd }}">{{ $sd }}</option>
            @endforeach
        </select>
    @endif

    @if(count($availableTempat) > 1)
        <select wire:model.live="filterTempat" class="select-filter" aria-label="Filter Tempat">
            <option value="">Semua Lokasi</option>
            @foreach($availableTempat as $tp)
                <option value="{{ $tp }}">{{ $tp }}</option>
            @endforeach
        </select>
    @endif

    @if($search || $filterStatus || $filterKategori || $filterSumberDana || $filterTempat)
        <button type="button" wire:click="resetFilters" class="btn-reset-filter">Reset Filter</button>
    @endif

    <div class="filter-actions-right">
        <a href="{{ route('public.kalender.print', ['tahun' => $tahun]) }}" target="_blank" class="btn-outline-link" title="Cetak atau simpan sebagai PDF">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 6 2 18 2 18 9" />
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                <rect x="6" y="14" width="12" height="8" />
            </svg>
            <span>Cetak PDF</span>
        </a>
        <button type="button" wire:click="exportExcel" class="btn-outline-link" title="Unduh data jadwal ke Excel">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                <polyline points="7 10 12 15 17 10" />
                <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
            <span>Unduh Excel</span>
        </button>
    </div>
</div>
