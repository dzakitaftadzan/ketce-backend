<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $price = rand(50000, 500000);
            
            Order::create([
                'user_id' => 1,
                'address_id' => 1,
                'order_code' => 'INV-' . strtoupper(Str::random(8)),
                'subtotal' => $price,
                'shipping_cost' => 0, // Tambahkan ini
                'total_price' => $price,
                'payment_status' => $i % 2 == 0 ? 'paid' : 'pending',
                'order_status' => $i % 2 == 0 ? 'confirmed' : 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}