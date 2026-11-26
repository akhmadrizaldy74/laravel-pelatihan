{{-- Header Khusus Menu Daftar Pelatihan --}}
<div class="tab-header-card">
    <div>
        <h1 class="tab-header-title">Panel Kelola Pelatihan PPSDMK — {{ $tahun }}</h1>
        <p class="tab-header-sub">Tambah, edit, unggah Excel, dan atur status tampil/draft pelatihan.</p>
    </div>
    <div class="tab-header-actions">
        <select wire:model.live="tahun" class="form-control" style="width:auto; font-weight:600;" aria-label="Pilih Tahun">
            @foreach($availableYears as $yr)
                <option value="{{ $yr }}">Tahun {{ $yr }}</option>
            @endforeach
        </select>
        @if($canManage)
            <a href="{{ route('admin.template.kalender.download', ['tahun' => $tahun]) }}" class="btn-secondary">Template Excel</a>
            <button type="button" wire:click="openImportModal" class="btn-success">Upload Excel</button>
            <button type="button" wire:click="openCreate" class="btn-primary">+ Tambah Pelatihan</button>
        @endif
    </div>
</div>

@if($canManage && array_sum($statusCounts) > 0)
    <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:6px; padding:8px 12px; margin-bottom:12px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; font-size:12px;">
        <div>
            Publikasi Tahun <strong>{{ $tahun }}</strong>:
            <strong style="color:#16a34a;">{{ array_sum($statusCounts) - $draftCount }} Tampil</strong>
            &middot;
            <strong style="color:#64748b;">{{ $draftCount }} Draft</strong>
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

<div class="admin-filter-bar">
    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama, kode, angkatan, atau penyelenggara..."
           class="form-control filter-search">
    <select wire:model.live="filterKategori" class="form-control filter-select">
        <option value="">Semua Kategori</option>
        @foreach($activeKategoris as $kat)
            <option value="{{ $kat }}">{{ $kat }}</option>
        @endforeach
    </select>
    <select wire:model.live="filterStatus" class="form-control filter-select">
        <option value="">Semua Status</option>
        <option value="Belum Dimulai">Belum Dimulai</option>
        <option value="Sedang Berlangsung">Sedang Berlangsung</option>
        <option value="Tahap Seleksi">Tahap Seleksi</option>
        <option value="Selesai">Selesai</option>
    </select>
    <select wire:model.live="filterPublish" class="form-control filter-select">
        <option value="">Semua Visibilitas</option>
        <option value="published">Tampil di Publik</option>
        <option value="draft">Draft (Tersembunyi)</option>
    </select>
</div>

<div class="admin-table-wrap">
    <div class="admin-table-scroll">
        <table class="admin-table" style="text-align:left;">
            <thead>
                <tr>
                    <th style="width:40px; text-align:center;">No</th>
                    <th style="width:130px;">Kategori</th>
                    <th style="min-width:220px;">Nama Pelatihan</th>
                    <th style="text-align:center; width:128px;">Peserta</th>
                    <th style="text-align:center; width:68px;">Durasi</th>
                    <th style="text-align:center; width:150px;">Periode</th>
                    <th style="text-align:center; width:105px;">Dana / Lokasi</th>
                    <th style="text-align:center; width:100px;">Metode</th>
                    <th style="text-align:center; width:115px;">Status</th>
                    <th style="text-align:center; width:82px;">Publikasi</th>
                    @if($canManage)
                        <th style="text-align:center; width:108px;">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($pelatihans as $p)
                @php $rowNo = (($currentPage - 1) * $perPage) + $loop->iteration; @endphp
                <tr style="background: {{ $loop->even ? '#f8fafc' : '#ffffff' }};">
                    <td style="text-align:center; color:#64748b; font-weight:600;">{{ $rowNo }}</td>
                    <td>
                        <div style="font-size:11px; font-weight:700; color:#334155;">{{ $p->kategori ?: 'FUNGSIONAL' }}</div>
                        @if($p->kode_pelatihan)
                            <div style="font-size:11px; color:#64748b;">{{ $p->kode_pelatihan }}</div>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight:600; color:#0f172a; line-height:1.35;">{{ $p->nama_pelatihan }}</div>
                        <div style="font-size:11px; color:#64748b; margin-top:2px;">
                            @if($p->angkatan) {{ $p->angkatan }} &middot; @endif
                            @if($p->penyelenggara) {{ $p->penyelenggara }} &middot; @endif
                            Seleksi H-{{ $p->seleksi_hari ?? 7 }}
                            @if(!$p->auto_status)
                                <span style="color:#b91c1c; font-weight:600;">(Manual)</span>
                            @endif
                        </div>
                        @php $conflicts = $p->getHolidayConflicts($hariLibursYear); @endphp
                        @if(!empty($conflicts))
                            <div class="conflict-badge" title="{{ implode(' | ', $conflicts) }}">
                                Bentrok Libur: {{ $conflicts[0] }}
                            </div>
                        @endif
                    </td>
                    <td style="text-align:center; white-space:nowrap;">
                        <div style="font-weight:700;">{{ $p->jumlah_peserta }} org</div>
                        <div style="font-size:11px; color:#64748b;">Daftar {{ $p->peserta_terdaftar }} &middot; Lulus {{ $p->peserta_lulus }}</div>
                    </td>
                    <td style="text-align:center; color:#475569; white-space:nowrap;">
                        <div>{{ $p->durasi_display }}</div>
                        @if($p->jam_pelajaran)
                            <div style="font-size:11px; color:#64748b;">{{ $p->jam_pelajaran }} JP</div>
                        @endif
                    </td>
                    <td style="text-align:center; white-space:nowrap;">
                        <div style="font-weight:600;">{{ $p->periode_display }}</div>
                        <div style="font-size:10.5px; color:#94a3b8;">{{ $p->tanggal_mulai?->format('d/m/Y') }} - {{ $p->tanggal_selesai?->format('d/m/Y') }}</div>
                    </td>
                    <td style="text-align:center; white-space:nowrap;">{{ $p->sumber_dana ?: 'RM' }} &middot; {{ $p->tempat_pelaksanaan ?: 'PPSDM' }}</td>
                    <td style="text-align:center;">
                        <span style="font-size:11px; font-weight:600; padding:2px 6px; border-radius:3px; background:{{ \App\Models\Pelatihan::getMetodeColor($p->metode ?: 'Klasikal') }}; color:{{ \App\Models\Pelatihan::getMetodeTextColor($p->metode ?: 'Klasikal') }};">
                            {{ $p->metode ?: 'Klasikal' }}
                        </span>
                        @if(!empty($p->jadwal_tahapan) && is_array($p->jadwal_tahapan))
                            <div style="font-size:10.5px; color:#475569; margin-top:2px;">{{ count($p->jadwal_tahapan) }} tahap</div>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        <span class="status-pill {{ $p->status_color }}">{{ $p->computed_status }}</span>
                    </td>
                    <td style="text-align:center;">
                        @if($canManage)
                            <button type="button" wire:click="togglePublish({{ $p->id }})"
                                    style="padding:2px 8px; border-radius:4px; font-size:11px; font-weight:600; cursor:pointer; border:1px solid {{ $p->is_published ? '#86efac' : '#cbd5e1' }}; background:{{ $p->is_published ? '#f0fdf4' : '#f1f5f9' }}; color:{{ $p->is_published ? '#15803d' : '#475569' }};"
                                    title="Klik untuk ubah tampil / sembunyikan di publik">
                                {{ $p->is_published ? 'Tampil' : 'Draft' }}
                            </button>
                        @else
                            <span style="font-size:11px; color:{{ $p->is_published ? '#15803d' : '#64748b' }}; font-weight:600;">
                                {{ $p->is_published ? 'Tampil' : 'Draft' }}
                            </span>
                        @endif
                    </td>
                    @if($canManage)
                        <td style="text-align:center;">
                            <div style="display:flex; justify-content:center; gap:4px;">
                                <button type="button" wire:click="openEdit({{ $p->id }})" class="btn-action-edit">Edit</button>
                                <button type="button" wire:click="confirmDelete({{ $p->id }})" class="btn-action-del">Hapus</button>
                            </div>
                        </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $canManage ? 11 : 10 }}" style="padding:28px; text-align:center; color:#94a3b8;">
                        Tidak ada data pelatihan pada Tahun {{ $tahun }}.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="admin-table-footer">
        @php
            $fromItem = $totalFiltered > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
            $toItem   = min($totalFiltered, $currentPage * $perPage);
        @endphp
        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <span>Menampilkan <strong>{{ $fromItem }}–{{ $toItem }}</strong> dari <strong>{{ $totalFiltered }}</strong> pelatihan</span>
            <select wire:model.live="perPage" class="pagination-perpage" aria-label="Jumlah baris per halaman">
                <option value="10">10 baris</option>
                <option value="15">15 baris</option>
                <option value="25">25 baris</option>
                <option value="50">50 baris</option>
            </select>
        </div>

        @if($lastPage > 1)
            <div class="pagination-controls">
                <button type="button" wire:click="prevPage" class="page-btn" {{ $currentPage <= 1 ? 'disabled' : '' }}>
                    ← Sebelumnya
                </button>
                @for($p = 1; $p <= $lastPage; $p++)
                    <button type="button" wire:click="gotoPage({{ $p }})" class="page-btn {{ $p === $currentPage ? 'active' : '' }}">
                        {{ $p }}
                    </button>
                @endfor
                <button type="button" wire:click="nextPage({{ $lastPage }})" class="page-btn" {{ $currentPage >= $lastPage ? 'disabled' : '' }}>
                    Berikutnya →
                </button>
            </div>
        @endif
    </div>
</div>
