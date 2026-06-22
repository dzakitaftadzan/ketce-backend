<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Address;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        Address::create([
            'user_id'        => 6,
            'label'          => 'Rumah',
            'recipient_name' => 'Dzaki Tafta',
            'street'         => 'Jl. Alauddin No 1',
            'city'           => 'Makassar',
            'province'       => 'Sulawesi Selatan',
            'postal_code'    => '90221',
            'is_default'     => true
        ]);
    }
}