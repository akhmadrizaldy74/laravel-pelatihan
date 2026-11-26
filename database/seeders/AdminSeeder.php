<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@pelatihan.com'],
            [
                'name'      => 'Administrator PPSDMK',
                'email'     => 'admin@pelatihan.com',
                'password'  => Hash::make('admin123'),
                'role'      => User::ROLE_ADMIN,
                'is_active' => true,
            ]
        );
    }
}
