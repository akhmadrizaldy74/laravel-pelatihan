<?php

namespace App\Livewire\Admin\Concerns;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

trait ManagesUsersAndSecurity
{
    public function changeOwnPassword(): void
    {
        $this->validate([
            'currentPassword' => 'required',
            'newPassword'     => 'required|min:6',
            'confirmPassword' => 'required|same:newPassword',
        ], [
            'confirmPassword.same' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user = Auth::user();
        if (!Hash::check($this->currentPassword, $user->password)) {
            $this->addError('currentPassword', 'Password saat ini tidak sesuai.');
            return;
        }

        $user->update(['password' => Hash::make($this->newPassword)]);
        AuditLog::record('settings', 'Mengubah password akun sendiri.', 'User', $user->id);
        $this->currentPassword = '';
        $this->newPassword     = '';
        $this->confirmPassword = '';
        $this->flashSuccess('Password akun Anda berhasil diperbarui.');
    }

    public function createUser(): void
    {
        if (!$this->isSuperAdmin()) return;

        $this->validate([
            'newUserName'     => 'required|string|max:120',
            'newUserEmail'    => 'required|email|unique:users,email',
            'newUserPassword' => 'required|min:6',
            'newUserRole'     => 'required|in:admin,operator,viewer',
        ]);

        $u = User::create([
            'name'      => trim($this->newUserName),
            'email'     => strtolower(trim($this->newUserEmail)),
            'password'  => Hash::make($this->newUserPassword),
            'role'      => $this->newUserRole,
            'is_active' => true,
        ]);

        AuditLog::record('create', "Menambah akun {$u->role}: {$u->email}", 'User', $u->id);
        $this->newUserName     = '';
        $this->newUserEmail    = '';
        $this->newUserPassword = '';
        $this->newUserRole     = User::ROLE_OPERATOR;
        $this->flashSuccess("Akun '{$u->name}' berhasil dibuat.");
    }

    public function updateUserRole(int $id, string $role): void
    {
        if (!$this->isSuperAdmin()) return;
        if (!in_array($role, [User::ROLE_ADMIN, User::ROLE_OPERATOR, User::ROLE_VIEWER], true)) {
            return;
        }
        $user = User::findOrFail($id);
        if ($user->id === Auth::id() && $role !== User::ROLE_ADMIN) {
            $this->flashError('Anda tidak dapat menurunkan peran akun Anda sendiri.');
            return;
        }
        $user->update(['role' => $role]);
        AuditLog::record('update', "Mengubah peran {$user->email} menjadi {$role}", 'User', $user->id);
        $this->flashSuccess("Peran '{$user->name}' diubah menjadi {$role}.");
    }

    public function toggleUserActive(int $id): void
    {
        if (!$this->isSuperAdmin()) return;
        $user = User::findOrFail($id);
        if ($user->id === Auth::id()) {
            $this->flashError('Anda tidak dapat menonaktifkan akun Anda sendiri.');
            return;
        }
        $user->update(['is_active' => !$user->is_active]);
        AuditLog::record('update', ($user->is_active ? 'Mengaktifkan' : 'Menonaktifkan') . " akun {$user->email}", 'User', $user->id);
        $this->flashSuccess('Status akun diperbarui.');
    }
}
