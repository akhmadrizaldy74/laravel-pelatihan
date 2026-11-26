<div class="login-card">
    <div class="login-logo">
        <img src="{{ asset('images/logo-kemnaker.jpg') }}" alt="Logo Kemnaker Corpu" class="logo-img">
        <h1>Portal Admin PPSDMK</h1>
        <p>Manajemen Jadwal &amp; Kalender Pelatihan</p>
    </div>

    @if ($errorMessage)
        <div class="error-box">
            <span>⚠️</span>
            <span>{{ $errorMessage }}</span>
        </div>
    @endif

    <form wire:submit.prevent="login">
        <div class="form-group">
            <label for="email">Alamat Email</label>
            <input type="email" id="email" wire:model="email" placeholder="nama@kemnaker.go.id" required autocomplete="username">
            @error('email') <span style="color:#d32f2f; font-size:0.78rem;">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="password">Kata Sandi</label>
            <input type="password" id="password" wire:model="password" placeholder="••••••••" required autocomplete="current-password">
            @error('password') <span style="color:#d32f2f; font-size:0.78rem;">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="btn-login" wire:loading.attr="disabled">
            <span wire:loading.remove>Masuk ke Dashboard</span>
            <span wire:loading>Memproses...</span>
        </button>
    </form>

    <p style="font-size:0.75rem; color:#64748b; text-align:center; margin-top:16px;">
        Gunakan akun resmi yang terdaftar oleh Administrator PPSDMK.
    </p>

    <a href="{{ url('/') }}" class="back-link">← Kembali ke Dashboard Publik</a>
</div>
