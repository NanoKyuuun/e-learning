<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@elearning.com'],
            [
                'full_name' => 'Admin Sistem',
                'username' => 'admin_system',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );

        $admin->assignRole('admin-sistem');
    }
}
