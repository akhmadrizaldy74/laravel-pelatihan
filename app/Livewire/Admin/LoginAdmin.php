<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Component;

class LoginAdmin extends Component
{
    public string $email = '';
    public string $password = '';
    public string $errorMessage = '';

    public function mount(): void
    {
        if (session('auth_error')) {
            $this->errorMessage = (string)session('auth_error');
        }
    }

    public function login(): void
    {
        $this->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $throttleKey = Str::lower($this->email) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->errorMessage = "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.";
            return;
        }

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            $user = Auth::user();

            if (!$user->canAccessAdminPanel()) {
                Auth::logout();
                RateLimiter::hit($throttleKey, 60);
                $this->errorMessage = 'Akun Anda dinonaktifkan atau tidak memiliki izin akses.';
                return;
            }

            RateLimiter::clear($throttleKey);
            session()->regenerate();

            AuditLog::record('login', "Login ke Panel Admin ({$user->email})", 'User', $user->id);

            $this->redirect(route('admin.dashboard'), navigate: false);
        } else {
            RateLimiter::hit($throttleKey, 60);
            $this->errorMessage = 'Email atau password salah!';
        }
    }

    public function render()
    {
        return view('livewire.admin.login-admin')
            ->layout('layouts.admin-guest');
    }
}
