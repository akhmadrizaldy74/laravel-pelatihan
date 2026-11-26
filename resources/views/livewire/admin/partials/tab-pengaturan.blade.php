{{-- Header Khusus Menu Akun & Audit Log --}}
<div class="tab-header-card">
    <div>
        <h1 class="tab-header-title">Akun Pengguna &amp; Audit Log</h1>
        <p class="tab-header-sub">Kelola kata sandi, hak akses admin/operator/viewer, dan pantau riwayat aktivitas sistem.</p>
    </div>
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:14px;">
    <div class="panel-card">
        <h3 style="font-size:14px; font-weight:700; margin-bottom:10px;">Ubah Password Saya</h3>
        <form wire:submit.prevent="changeOwnPassword">
            <div class="form-group-full">
                <label class="form-label">Password Saat Ini</label>
                <input type="password" wire:model="currentPassword" class="form-control" required>
                @error('currentPassword') <div style="color:#dc2626; font-size:12px;">{{ $message }}</div> @enderror
            </div>
            <div class="form-group-full">
                <label class="form-label">Password Baru (min. 6 karakter)</label>
                <input type="password" wire:model="newPassword" class="form-control" required>
                @error('newPassword') <div style="color:#dc2626; font-size:12px;">{{ $message }}</div> @enderror
            </div>
            <div class="form-group-full">
                <label class="form-label">Konfirmasi Password Baru</label>
                <input type="password" wire:model="confirmPassword" class="form-control" required>
                @error('confirmPassword') <div style="color:#dc2626; font-size:12px;">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn-primary">Simpan Password</button>
        </form>
    </div>

    @if($isSuperAdmin)
        <div class="panel-card">
            <h3 style="font-size:14px; font-weight:700; margin-bottom:10px;">Manajemen Akun Pengguna</h3>
            <form wire:submit.prevent="createUser" style="background:#f8fafc; padding:10px; border:1px solid #e2e8f0; border-radius:5px; margin-bottom:12px;">
                <div class="form-row">
                    <input type="text" wire:model="newUserName" class="form-control" placeholder="Nama lengkap" required>
                    <input type="email" wire:model="newUserEmail" class="form-control" placeholder="Email" required>
                </div>
                <div class="form-row" style="margin-bottom:8px;">
                    <input type="password" wire:model="newUserPassword" class="form-control" placeholder="Password (min. 6)" required>
                    <select wire:model="newUserRole" class="form-control">
                        <option value="operator">Operator (Kelola Jadwal)</option>
                        <option value="admin">Admin (Akses Penuh)</option>
                        <option value="viewer">Viewer (Hanya Lihat)</option>
                    </select>
                </div>
                @error('newUserEmail') <div style="color:#dc2626; font-size:12px; margin-bottom:6px;">{{ $message }}</div> @enderror
                <button type="submit" class="btn-primary">Tambah Akun</button>
            </form>

            @foreach($users as $u)
                <div style="display:flex; justify-content:space-between; align-items:center; padding:7px 2px; border-bottom:1px solid #f1f5f9; gap:8px; flex-wrap:wrap;">
                    <div>
                        <div style="font-size:12.5px; font-weight:600;">{{ $u->name }}</div>
                        <div style="font-size:11.5px; color:#64748b;">{{ $u->email }}</div>
                    </div>
                    <div style="display:flex; gap:6px; align-items:center;">
                        <select wire:change="updateUserRole({{ $u->id }}, $event.target.value)" class="form-control" style="padding:3px 6px; font-size:12px; width:auto;">
                            <option value="admin" {{ $u->role === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="operator" {{ $u->role === 'operator' ? 'selected' : '' }}>Operator</option>
                            <option value="viewer" {{ $u->role === 'viewer' ? 'selected' : '' }}>Viewer</option>
                        </select>
                        <button type="button" wire:click="toggleUserActive({{ $u->id }})" class="{{ $u->is_active ? 'btn-action-edit' : 'btn-action-del' }}">
                            {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="panel-card" style="grid-column:1 / -1;">
        <h3 style="font-size:14px; font-weight:700; margin-bottom:10px;">Riwayat Aktivitas (Audit Log)</h3>
        <div style="overflow-x:auto; max-height:320px;">
            <table style="width:100%; border-collapse:collapse; font-size:12px;">
                <thead>
                    <tr style="background:#f1f5f9; text-align:left;">
                        <th style="padding:7px 10px; border-bottom:1px solid #cbd5e1;">Waktu</th>
                        <th style="padding:7px 10px; border-bottom:1px solid #cbd5e1;">Pengguna</th>
                        <th style="padding:7px 10px; border-bottom:1px solid #cbd5e1;">Aksi</th>
                        <th style="padding:7px 10px; border-bottom:1px solid #cbd5e1;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditLogs as $log)
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:6px 10px; color:#64748b; white-space:nowrap;">{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                            <td style="padding:6px 10px; font-weight:600;">{{ $log->user_name }}</td>
                            <td style="padding:6px 10px; text-transform:uppercase; font-weight:700; color:#1d4ed8;">{{ $log->action }}</td>
                            <td style="padding:6px 10px; color:#334155;">{{ $log->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding:18px; text-align:center; color:#94a3b8;">Belum ada catatan aktivitas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
