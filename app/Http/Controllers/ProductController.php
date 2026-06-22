<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * FUNGSI 1: Mengambil semua produk yang aktif beserta variannya
     */
    public function index(): JsonResponse
    {
        $products = Product::where('is_active', true)->with('variants')->get();
        
        return response()->json([
            'message' => 'Berhasil mengambil daftar produk',
            'data'    => $products
        ], 200);
    }

    /**
     * FUNGSI 2: Mengambil detail produk berdasarkan ID beserta variannya
     */
    public function show(Product $product): JsonResponse
    {
        $product->load('variants');
        
        return response()->json([
            'message' => 'Berhasil mengambil detail produk',
            'data'    => $product
        ], 200);
    }

    /**
     * FUNGSI 3: Membuat produk baru sekaligus dengan galeri fotonya (3-5 Foto)
     */
    public function store(Request $request): JsonResponse
    {
        // Validasi Input (Wajib upload minimal 1 gambar, maksimal 5 gambar)
        $request->validate([
            'name'        => 'required|string|max:255',
            'category'    => 'required|string',
            'base_price'  => 'required|numeric',
            'description' => 'nullable|string',
            'images'      => 'required|array|min:1|max:5', 
            'images.*'    => 'image|mimes:jpeg,png,jpg,webp|max:2048', 
        ]);

        $uploadedImages = [];

        // Proses Looping Upload 3-5 Gambar ke Supabase Storage
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $filename = Str::slug($request->name) . '-' . time() . '-' . Str::random(5) . '.' . $file->getClientOriginalExtension();
                $path = Storage::disk('supabase')->putFileAs('produk-ketce', $file, $filename);
                $uploadedImages[] = Storage::disk('supabase')->url($path);
            }
        }

        // Simpan Data ke Tabel Products
        $product = Product::create([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'description' => $request->description ?? '-',
            'category'    => $request->category,
            'base_price'  => $request->base_price,
            'image'       => $uploadedImages, 
        ]);

        // Buat Varian Ukuran Otomatis (M, L, XL)
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
            'message' => 'Produk baru dengan galeri foto berhasil ditambahkan!',
            'data'    => $product->load('variants')
        ], 201);
    }

    /**
     * FUNGSI 4: Menambahkan/mengupdate gambar khusus untuk produk yang SUDAH ADA.
     * (Cocok untuk mengisi 3-5 gambar ke 10 data produk aslimu dari Supabase)
     */
    public function uploadImages(Request $request, $id): JsonResponse
    {
        // Validasi Input (Wajib upload 1-5 gambar)
        $request->validate([
            'images'   => 'required|array|min:1|max:5', 
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048', 
        ]);

        // Cari produk berdasarkan ID yang ada di Supabase
        $product = Product::findOrFail($id);
        $uploadedImages = [];

        // Proses Looping Upload Gambar ke Supabase
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                // Buat nama file rapi sesuai nama produk
                $filename = Str::slug($product->name) . '-' . time() . '-' . Str::random(5) . '.' . $file->getClientOriginalExtension();
                
                $path = Storage::disk('supabase')->putFileAs('produk-ketce', $file, $filename);
                $uploadedImages[] = Storage::disk('supabase')->url($path);
            }
        }

        // Update kolom image di database untuk produk ini
        $product->update([
            'image' => $uploadedImages
        ]);

        return response()->json([
            'status'  => 'sukses',
            'message' => 'Galeri foto untuk produk ' . $product->name . ' berhasil diunggah!',
            'data'    => $product
        ], 200);
    }
}