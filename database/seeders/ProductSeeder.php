<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::firstOrCreate(
            ['slug' => 'pakaian-pria'],
            ['name' => 'Pakaian Pria']
        );

        Product::updateOrCreate(
            ['name' => 'Kemeja Flanel Ketce'],
            [
                'category_id' => $category->id,
                'price' => 150000,
                'stock' => 50,
                'description' => 'Kemeja flanel premium yang sangat ketce dan nyaman dipakai.'
            ]
        );
        
        Product::updateOrCreate(
            ['name' => 'Kaos Polos Basic'],
            [
                'category_id' => $category->id,
                'price' => 50000,
                'stock' => 100,
                'description' => 'Kaos polos untuk kebutuhan sehari-hari.'
            ]
        );
    }
}