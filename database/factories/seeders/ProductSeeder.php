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
        // 1. Daftar 10 Produk Asli Kamu dengan penyesuaian harga dan kategori
        $productsData = [
            ['name' => 'Boxy Tee', 'price' => 149000, 'sku_code' => 'BOXY', 'category' => 't-shirt'],
            ['name' => 'Hoodie Obsidian Black', 'price' => 249000, 'sku_code' => 'HOD-OB', 'category' => 'hoodie'],
            ['name' => 'Yoka Jacket Choengsam', 'price' => 299000, 'sku_code' => 'JKT-YKA', 'category' => 'jacket'],
            ['name' => 'Claps Jigoku Jacket', 'price' => 279000, 'sku_code' => 'JKT-JGK', 'category' => 'jacket'],
            ['name' => 'Classic Flannel Shirt', 'price' => 189000, 'sku_code' => 'FLN-CLS', 'category' => 'shirt'],
            ['name' => 'Button Pants Black', 'price' => 199000, 'sku_code' => 'PNT-BTN', 'category' => 'pants'],
            ['name' => 'Pleated Trouser', 'price' => 219000, 'sku_code' => 'TRS-PLT', 'category' => 'pants'],
            ['name' => 'White Baggy Pants', 'price' => 199000, 'sku_code' => 'PNT-BGY', 'category' => 'pants'],
            ['name' => 'Sakura Raw Denim', 'price' => 349000, 'sku_code' => 'DNM-SKR', 'category' => 'denim'],
            ['name' => 'Short Plated Trouser', 'price' => 159000, 'sku_code' => 'TRS-SHRT', 'category' => 'pants'],
        ];

        foreach ($productsData as $item) {
            // 2. Insert ke tabel products (Lolos constraint category & base_price)
            $product = Product::create([
                'name'        => $item['name'],
                'slug'        => Str::slug($item['name']),
                'description' => '-', 
                'category'    => $item['category'], 
                'base_price'  => $item['price'], 
                'image'       => null, 
            ]);

            // 3. Insert ke tabel product_variants (Lolos fillable: product_id, size, color, stock, sku)
            $sizes = ['M', 'L', 'XL'];
            foreach ($sizes as $size) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'size'       => $size,
                    'color'      => 'Default', // Warna bawaan produk
                    'stock'      => 50,         // Stok masing-masing ukuran diatur 50 pcs
                    'sku'        => 'KTC-' . $item['sku_code'] . '-' . $size,
                ]);
            }
        }
    }
}