<div class="excel-jadwal-box">
    <div class="table-section-head">
        <div class="table-section-title">Daftar Jadwal Pelatihan</div>
        <span class="table-section-count">{{ $totalFiltered }} program ditemukan</span>
    </div>

    <div class="table-container">
        <table class="pelatihan-table">
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-kategori">Kategori</th>
                    <th class="col-nama">Nama Pelatihan</th>
                    <th class="col-peserta">Peserta</th>
                    <th class="col-durasi">Durasi</th>
                    <th class="col-periode">Periode</th>
                    <th class="col-dana">Dana</th>
                    <th class="col-tempat">Lokasi</th>
                    <th class="col-status">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pelatihans as $pelatihan)
                @php $rowNo = (($currentPage - 1) * $perPage) + $loop->iteration; @endphp
                <tr wire:click="openDetail({{ $pelatihan->id }})"
                    style="cursor:pointer;"
                    title="Klik untuk melihat detail pelatihan"
                    class="{{ $loop->even ? 'row-odd' : 'row-even' }} {{ $pelatihan->computed_status === 'Sedang Berlangsung' ? 'row-active' : '' }}">
                    <td class="col-no">{{ $rowNo }}</td>
                    <td class="col-kategori">{{ $pelatihan->kategori }}</td>
                    <td class="col-nama">
                        <div class="nama-primary">
                            <span>{{ $pelatihan->nama_pelatihan }}</span>
                            @if($pelatihan->angkatan)
                                <span class="badge-angkatan">{{ $pelatihan->angkatan }}</span>
                            @endif
                        </div>
                        @if($pelatihan->metode || $pelatihan->penyelenggara)
                            <div class="nama-meta">
                                {{ $pelatihan->metode ?: 'Klasikal' }}
                                @if($pelatihan->penyelenggara)
                                    &middot; {{ $pelatihan->penyelenggara }}
                                @endif
                            </div>
                        @endif
                    </td>
                    <td class="col-peserta">
                        <div>{{ $pelatihan->jumlah_peserta }} org</div>
                        @if($pelatihan->peserta_lulus > 0)
                            <div style="font-size:11px; color:#059669; font-weight:600;">Lulus: {{ $pelatihan->peserta_lulus }}</div>
                        @endif
                    </td>
                    <td class="col-durasi">{{ $pelatihan->durasi_display }}</td>
                    <td class="col-periode">{{ $pelatihan->periode_display }}</td>
                    <td class="col-dana">{{ $pelatihan->sumber_dana ?: 'RM' }}</td>
                    <td class="col-tempat">{{ $pelatihan->tempat_pelaksanaan ?: 'PPSDM' }}</td>
                    <td class="col-status">
                        <span class="status-pill {{ $pelatihan->status_color }}">
                            @if($pelatihan->computed_status === 'Sedang Berlangsung')
                                <span class="pill-pulse"></span>
                            @endif
                            {{ $pelatihan->computed_status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center; padding:32px; color:#94a3b8;">
                        Tidak ada pelatihan yang cocok dengan filter saat ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Card View --}}
    <div class="mobile-cards">
        @forelse($pelatihans as $pelatihan)
        @php $rowNo = (($currentPage - 1) * $perPage) + $loop->iteration; @endphp
        <div class="mobile-card" wire:click="openDetail({{ $pelatihan->id }})" style="cursor:pointer;">
            <div class="mobile-card-header">
                <span class="mobile-card-no">#{{ $rowNo }} &middot; {{ $pelatihan->kategori }}</span>
                <span class="status-pill {{ $pelatihan->status_color }}">{{ $pelatihan->computed_status }}</span>
            </div>
            <div class="mobile-card-nama">{{ $pelatihan->nama_pelatihan }}</div>
            <div class="mobile-card-info">
                <div>
                    <span class="mobile-card-label">Peserta</span>
                    <span class="mobile-card-value">{{ $pelatihan->jumlah_peserta }} orang</span>
                </div>
                <div>
                    <span class="mobile-card-label">Periode ({{ $pelatihan->durasi_display }})</span>
                    <span class="mobile-card-value">{{ $pelatihan->periode_display }}</span>
                </div>
            </div>
        </div>
        @empty
        <div style="text-align:center; padding:24px; color:#94a3b8;">Tidak ada data ditemukan</div>
        @endforelse
    </div>

    <div class="table-footer">
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
