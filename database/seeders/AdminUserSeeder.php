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
        $adminPhone = env('ADMIN_PHONE', '081234567890');
        $adminPassword = env('ADMIN_PASSWORD', 'password');

        User::firstOrCreate(
            ['phone' => $adminPhone],
            [
                'name' => 'Admin NetVerse',
                'role' => 'admin',
                'password' => Hash::make($adminPassword),
            ]
        );
    }
}
