<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi Input (Wajib upload minimal 1 gambar, maksimal 5 gambar)
        $request->validate([
            'name'        => 'required|string|max:255',
            'category'    => 'required|string',
            'base_price'  => 'required|numeric',
            'description' => 'nullable|string',
            'images'      => 'required|array|min:1|max:5', 
            'images.*'    => 'image|mimes:jpeg,png,jpg,webp|max:2048', // Maks 2MB per foto
        ]);

        $uploadedImages = [];

        // 2. Proses Looping Upload 3-5 Gambar ke Supabase Storage
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                // Buat nama file unik: produk-slug-timestamp-random.extension
                $filename = Str::slug($request->name) . '-' . time() . '-' . Str::random(5) . '.' . $file->getClientOriginalExtension();
                
                // Upload langsung ke disk 'supabase' yang sudah kita konfigurasi di Filesystem
                $path = Storage::disk('supabase')->putFileAs('produk-ketce', $file, $filename);
                
                // Ambil URL Publik dari Supabase Storage
                $uploadedImages[] = Storage::disk('supabase')->url($path);
            }
        }

        // 3. Simpan Data ke Tabel Products (Kolom image otomatis menyimpan array JSON link foto)
        $product = Product::create([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'description' => $request->description ?? '-',
            'category'    => $request->category,
            'base_price'  => $request->base_price,
            'image'       => $uploadedImages, // Menyimpan array berisi 3-5 link foto
        ]);

        // 4. Buat Varian Ukuran Otomatis (M, L, XL) sebagai pelengkap data produk baru
        $sizes = ['M', 'L', 'XL'];
        foreach ($sizes as $size) {
            ProductVariant::create([
                'product_id' => $product->id,
                'size'       => $size,
                'color'      => 'Default',
                'stock'      => 50,
                'sku'        => 'KTC-' . strtoupper(Str::random(4)) . '-' . $size,
            ]);
        }

        return response()->json([
            'status'  => 'sukses',
            'pesan'   => 'Produk baru dengan galeri foto berhasil ditambahkan!',
            'data'    => $product->load('variants')
        ], 201);
    }
}