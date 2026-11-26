@if($showDeleteConfirm)
<div class="swal-overlay" wire:click.self="closeModal" role="dialog" aria-modal="true">
    <div class="swal-box">
        <button type="button" class="swal-close" wire:click="closeModal" aria-label="Tutup">&times;</button>
        <div class="swal-icon swal-icon-warning">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
        </div>
        <div class="swal-title">Konfirmasi Hapus</div>
        <div class="swal-text">Yakin ingin menghapus data pelatihan ini? Data yang dihapus tidak akan tampil lagi di dashboard publik.</div>
        <div class="swal-actions">
            <button type="button" class="swal-btn-cancel" wire:click="closeModal">Batal</button>
            <button type="button" class="swal-btn-danger" wire:click="doDelete" wire:loading.attr="disabled">Ya, Hapus</button>
        </div>
    </div>
</div>
@endif
