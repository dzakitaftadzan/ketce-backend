<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Panggil Seeder lain di sini
        $this->call([
            ProductSeeder::class,
        ]);

        // Buat Admin
        User::updateOrCreate(
            ['email' => 'admin@ketce.com'],
            [
                'name' => 'Admin Ketce',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}