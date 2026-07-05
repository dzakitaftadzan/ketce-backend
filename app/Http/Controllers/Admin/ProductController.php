<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Menampilkan semua produk beserta gambarnya.
     */
    public function index()
    {
        $products = Product::with('images')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar produk berhasil diambil.',
            'data' => $products,
        ]);
    }

    /**
     * Menampilkan detail satu produk.
     */
    public function show(Product $product)
    {
        $product->load('images');

        return response()->json([
            'success' => true,
            'message' => 'Detail produk berhasil diambil.',
            'data' => $product,
        ]);
    }

    /**
     * Menyimpan produk baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string',

            'images' => 'required|array|min:1|max:4',
            'images.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        DB::beginTransaction();

        try {

            $product = Product::create([
                'name' => $validated['name'],
                'price' => $validated['price'],
                'stock' => $validated['stock'],
                'description' => $validated['description'] ?? null,
            ]);

            foreach ($request->file('images') as $index => $image) {

                $path = $image->store('products', 'public');

                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $path,
                    'sort_order' => $index + 1,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan.',
                'data' => $product->load('images'),
            ], 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            dd([
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }

    /**
     * Mengupdate produk.
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Menghapus produk.
     */
    public function destroy(Product $product)
    {
        //
    }
}