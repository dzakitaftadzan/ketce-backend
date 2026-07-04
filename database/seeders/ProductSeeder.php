<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $mockData = [
            // === KATEGORI: ATASAN ===
            [
                'name' => 'Boxy Tee', 
                'category' => 'Atasan', 
                'price' => 189000, 
                'description' => 'Kaus berpotongan boxy fit yang nyaman. Menampilkan cetakan teks "front tee" pada bagian depan dan "dirty clothes" pada bagian belakang.',
                'variants' => [
                    ['size' => 'M', 'color' => 'Black', 'stock' => 15], 
                    ['size' => 'L', 'color' => 'Black', 'stock' => 20], 
                    ['size' => 'XL', 'color' => 'White', 'stock' => 10]
                ]
            ],
            [
                'name' => 'Hoodie Obsidian Black', 
                'category' => 'Atasan', 
                'price' => 450000, 
                'description' => 'Hoodie tebal dengan warna hitam pekat dan potongan oversized.',
                'variants' => [
                    ['size' => 'M', 'color' => 'Obsidian Black', 'stock' => 10], 
                    ['size' => 'L', 'color' => 'Obsidian Black', 'stock' => 8]
                ]
            ],
            [
                'name' => 'Yoka Jacket Choengsam', 
                'category' => 'Atasan', 
                'price' => 550000, 
                'description' => 'Jaket perpaduan gaya modern dan kerah tradisional khas Choengsam.',
                'variants' => [
                    ['size' => 'S', 'color' => 'Navy', 'stock' => 5], 
                    ['size' => 'M', 'color' => 'Navy', 'stock' => 12], 
                    ['size' => 'L', 'color' => 'Black', 'stock' => 7]
                ]
            ],
            [
                'name' => 'Clasp Jigoku Jacket', 
                'category' => 'Atasan', 
                'price' => 599000, 
                'description' => 'Jaket utilitas dengan detail pengait (clasp) industrial.',
                'variants' => [
                    ['size' => 'M', 'color' => 'Olive', 'stock' => 8], 
                    ['size' => 'L', 'color' => 'Olive', 'stock' => 10]
                ]
            ],
            [
                'name' => 'Classic Flannel Shirt', 
                'category' => 'Atasan', 
                'price' => 250000, 
                'description' => 'Kemeja flanel klasik dengan material katun premium.',
                'variants' => [
                    ['size' => 'M', 'color' => 'Red Tartan', 'stock' => 15], 
                    ['size' => 'L', 'color' => 'Blue Tartan', 'stock' => 12],
                    ['size' => 'XL', 'color' => 'Red Tartan', 'stock' => 5]
                ]
            ],

            // === KATEGORI: BAWAHAN ===
            [
                'name' => 'Button Pants Black', 
                'category' => 'Bawahan', 
                'price' => 299000, 
                'description' => 'Celana panjang dengan aksen kancing eksklusif di bagian samping.',
                'variants' => [
                    ['size' => '28', 'color' => 'Black', 'stock' => 10], 
                    ['size' => '30', 'color' => 'Black', 'stock' => 15], 
                    ['size' => '32', 'color' => 'Black', 'stock' => 12]
                ]
            ],
            [
                'name' => 'Pleated Trouser', 
                'category' => 'Bawahan', 
                'price' => 320000, 
                'description' => 'Celana panjang trouser dengan detail lipatan (pleats) elegan.',
                'variants' => [
                    ['size' => '30', 'color' => 'Charcoal', 'stock' => 8], 
                    ['size' => '32', 'color' => 'Charcoal', 'stock' => 14], 
                    ['size' => '34', 'color' => 'Khaki', 'stock' => 6]
                ]
            ],
            [
                'name' => 'White Baggy Pants', 
                'category' => 'Bawahan', 
                'price' => 310000, 
                'description' => 'Celana berpotongan longgar (baggy) berwarna putih bersih.',
                'variants' => [
                    ['size' => 'S', 'color' => 'White', 'stock' => 5], 
                    ['size' => 'M', 'color' => 'White', 'stock' => 15], 
                    ['size' => 'L', 'color' => 'White', 'stock' => 10]
                ]
            ],
            [
                'name' => 'Sakura Raw Denim', 
                'category' => 'Bawahan', 
                'price' => 450000, 
                'description' => 'Celana raw denim kaku yang akan memudar (fade) seiring pemakaian.',
                'variants' => [
                    ['size' => '30', 'color' => 'Deep Indigo', 'stock' => 20], 
                    ['size' => '32', 'color' => 'Deep Indigo', 'stock' => 25], 
                    ['size' => '34', 'color' => 'Deep Indigo', 'stock' => 10]
                ]
            ],
            [
                'name' => 'Short Plated Trouser', 
                'category' => 'Bawahan', 
                'price' => 250000, 
                'description' => 'Celana pendek trouser santai dengan aksen lipatan rapi.',
                'variants' => [
                    ['size' => 'M', 'color' => 'Beige', 'stock' => 12], 
                    ['size' => 'L', 'color' => 'Beige', 'stock' => 8], 
                    ['size' => 'L', 'color' => 'Black', 'stock' => 15]
                ]
            ]
        ];

        foreach ($mockData as $index => $item) {
            $product = Product::create([
                'name' => $item['name'],
                'category' => $item['category'],
                'base_price' => $item['price'],
                'description' => $item['description'] ?? null,
                'is_active' => true
            ]);

            // Ambil 3 huruf pertama kategori untuk kode SKU (ATA / BAW)
            $catCode = strtoupper(substr($item['category'], 0, 3));
            $prodNumber = str_pad($index + 1, 3, '0', STR_PAD_LEFT);

            foreach ($item['variants'] as $v) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'size' => $v['size'],
                    'color' => $v['color'],
                    'stock' => $v['stock'],
                    'sku' => "KTC-{$catCode}-{$prodNumber}-" . strtoupper($v['size'])
                ]);
            }
        }
    }
}