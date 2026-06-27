<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Membuat akun Admin
        User::create([
            'name' => 'Admin Ketce',
            'email' => 'admin@ketce.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'phone' => '08123456789'
        ]);

        // Membuat akun Kurir 1
        User::create([
            'name' => 'Kurir 1',
            'email' => 'kurir1@ketce.com',
            'password' => Hash::make('kurir123'),
            'role' => 'kurir',
            'phone' => '08122222222'
        ]);

        // Membuat akun Kurir 2
        User::create([
            'name' => 'Kurir 2',
            'email' => 'kurir2@ketce.com',
            'password' => Hash::make('kurir123'),
            'role' => 'kurir',
            'phone' => '08123333333'
        ]);
    }
}