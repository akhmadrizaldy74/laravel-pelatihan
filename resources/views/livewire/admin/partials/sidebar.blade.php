<aside class="admin-sidebar">
    <div class="sidebar-label">Menu</div>

    <button type="button" wire:click="$set('adminTab', 'daftar')" class="sidebar-item {{ $adminTab === 'daftar' ? 'active' : '' }}" title="Daftar Pelatihan">
        <span class="sidebar-item-left">
            <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="16" rx="2" />
                <line x1="3" y1="10" x2="21" y2="10" />
                <line x1="9" y1="4" x2="9" y2="20" />
            </svg>
            <span class="sidebar-text">Daftar Pelatihan</span>
        </span>
        <span class="sidebar-count">{{ array_sum($statusCounts) }}</span>
    </button>

    <button type="button" wire:click="$set('adminTab', 'kalender')" class="sidebar-item {{ $adminTab === 'kalender' ? 'active' : '' }}" title="Kalender Gantt">
        <span class="sidebar-item-left">
            <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" />
                <line x1="16" y1="2" x2="16" y2="6" />
                <line x1="8" y1="2" x2="8" y2="6" />
                <line x1="3" y1="10" x2="21" y2="10" />
            </svg>
            <span class="sidebar-text">Kalender Gantt</span>
        </span>
    </button>

    <button type="button" wire:click="$set('adminTab', 'analitik')" class="sidebar-item {{ $adminTab === 'analitik' ? 'active' : '' }}" title="Rekap & Analitik">
        <span class="sidebar-item-left">
            <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="20" x2="18" y2="10" />
                <line x1="12" y1="20" x2="12" y2="4" />
                <line x1="6" y1="20" x2="6" y2="14" />
            </svg>
            <span class="sidebar-text">Rekap &amp; Analitik</span>
        </span>
    </button>

    <button type="button" wire:click="$set('adminTab', 'master')" class="sidebar-item {{ $adminTab === 'master' ? 'active' : '' }}" title="Master Data & Libur">
        <span class="sidebar-item-left">
            <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3" />
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9c.26.6.77 1.02 1.4 1.09H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z" />
            </svg>
            <span class="sidebar-text">Master Data &amp; Libur</span>
        </span>
    </button>

    <button type="button" wire:click="$set('adminTab', 'pengaturan')" class="sidebar-item {{ $adminTab === 'pengaturan' ? 'active' : '' }}" title="Akun & Log">
        <span class="sidebar-item-left">
            <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                <circle cx="12" cy="7" r="4" />
            </svg>
            <span class="sidebar-text">Akun &amp; Log</span>
        </span>
    </button>
</aside>
