<div>
<div class="admin-shell">
    @include('livewire.admin.partials.sidebar')

    <section class="admin-main">
        @if(!empty($importWarnings))
            <div class="alert-inline">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                    <strong style="color:#92400e; font-size:12.5px;">Catatan Import ({{ count($importWarnings) }} baris dilewati)</strong>
                    <button type="button" wire:click="clearImportWarnings" style="background:none; border:none; color:#64748b; font-size:18px; cursor:pointer;">&times;</button>
                </div>
                <ul style="margin-left:18px; font-size:12px; color:#475569; max-height:120px; overflow-y:auto;">
                    @foreach($importWarnings as $warn)
                        <li>{{ $warn }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($adminTab === 'daftar')
            @include('livewire.admin.partials.tab-daftar')
        @elseif($adminTab === 'kalender')
            @include('livewire.admin.partials.tab-kalender')
        @elseif($adminTab === 'analitik')
            @include('livewire.admin.partials.tab-analitik')
        @elseif($adminTab === 'master')
            @include('livewire.admin.partials.tab-master')
        @elseif($adminTab === 'pengaturan')
            @include('livewire.admin.partials.tab-pengaturan')
        @endif
    </section>
</div>

    @include('livewire.admin.partials.modal-import')
    @include('livewire.admin.partials.modal-pelatihan-form')
    @include('livewire.admin.partials.modal-delete')

    @if($notif)
    <div class="swal-overlay" wire:click.self="clearNotif" role="dialog" aria-modal="true">
        <div class="swal-box">
            <button type="button" class="swal-close" wire:click="clearNotif" aria-label="Tutup">&times;</button>
            <div class="swal-icon {{ $notifType === 'success' ? 'swal-icon-success' : 'swal-icon-error' }}">
                @if($notifType === 'success')
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                @else
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                @endif
            </div>
            <div class="swal-title">{{ $notifType === 'success' ? 'Informasi !' : 'Perhatian !' }}</div>
            <div class="swal-text">{{ $notif }}</div>
            <div class="swal-actions">
                <button type="button" class="swal-btn-ok" wire:click="clearNotif">OK</button>
            </div>
        </div>
    </div>
    @endif
</div>
