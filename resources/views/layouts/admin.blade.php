<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin PPSDMK — Manajemen Pelatihan</title>

    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
    @livewireStyles
</head>
<body>
    <header class="admin-nav">
        <div class="nav-left">
            <button type="button" id="sidebarToggleBtn" class="hamburger-btn" aria-label="Buka atau tutup menu sidebar" title="Toggle Sidebar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                    <line x1="3" y1="6" x2="21" y2="6" />
                    <line x1="3" y1="12" x2="21" y2="12" />
                    <line x1="3" y1="18" x2="21" y2="18" />
                </svg>
            </button>
            <div class="nav-brand">Admin PPSDMK</div>
        </div>
        <div class="nav-actions">
            <span wire:ignore class="nav-clock realtime-wib-clock">{{ now()->format('H:i:s') }} WIB</span>
            <a href="{{ url('/') }}" target="_blank" class="btn-nav-outline">Lihat Website</a>
            <span class="nav-user">{{ Auth::user()->name ?? 'Admin' }} ({{ Auth::user()->role ?? 'admin' }})</span>
            <form action="{{ route('admin.logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn-logout">Keluar</button>
            </form>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    @livewireScripts
    <script src="{{ asset('js/clock.js') }}?v={{ filemtime(public_path('js/clock.js')) }}"></script>
    <script src="{{ asset('js/admin-sidebar.js') }}?v={{ filemtime(public_path('js/admin-sidebar.js')) }}"></script>
</body>
</html>
