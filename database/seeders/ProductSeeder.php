<?php

namespace Database\Seeders;

use App\Models\Product; // Pastikan menggunakan Model
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Menggunakan updateOrCreate untuk mencegah duplikasi data
        // jika seeder dijalankan berkali-kali.
        Product::updateOrCreate(
            ['name' => 'Produk Tes'], // Kriteria unik
            [
                'price' => 50000,
                'stock' => 100,
                // created_at dan updated_at otomatis diisi oleh Eloquent
            ]
        );
    }
}