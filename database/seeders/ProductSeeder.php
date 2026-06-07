<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ['Pria', 'Wanita', 'Aksesoris'];
        $sizes = ['S', 'M', 'L', 'XL', 'XXL'];
        $colors = ['Hitam', 'Putih', 'Navy', 'Maroon'];

        for ($index = 1; $index <= 10; $index++) {
            $category = $categories[array_rand($categories)];
            
            $product = Product::create([
                'name' => "Koleksi Fashion Model $index",
                'description' => "Deskripsi eksklusif untuk produk fashion model ke-$index dari Ketce.",
                'category' => $category,
                'base_price' => rand(75, 299) * 1000,
                'image' => "products/sample-image-$index.webp",
                'is_active' => true
            ]);

            $catCode = strtoupper(substr($category, 0, 3));
            $variantCount = rand(3, 5); // Tiap produk punya 3-5 varian
            $selectedVariants = [];

            for ($j = 0; $j < $variantCount; $j++) {
                $size = $sizes[array_rand($sizes)];
                $color = $colors[array_rand($colors)];
                
                // Mencegah duplikasi varian ukuran+warna di satu produk
                $comboKey = "$size-$color";
                if (in_array($comboKey, $selectedVariants)) continue;
                $selectedVariants[] = $comboKey;

                ProductVariant::create([
                    'product_id' => $product->id,
                    'size' => $size,
                    'color' => $color,
                    'stock' => rand(0, 50),
                    'sku' => "KTC-{$catCode}-" . str_pad($index, 3, '0', STR_PAD_LEFT) . "-$j"
                ]);
            }
        }
    }
}