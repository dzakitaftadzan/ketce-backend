<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            'data'    => $products,
        ]);
    }

    /**
     * Menampilkan detail satu produk (by ID untuk admin).
     */
    public function show($id)
    {
        $product = Product::findOrFail($id);
        $product->load('images', 'category');

        return response()->json([
            'success' => true,
            'message' => 'Detail produk berhasil diambil.',
            'data'    => $product,
        ]);
    }

    /**
     * Menyimpan produk baru.
     * PERBAIKAN: Hapus dd() di catch block (fatal di production).
     *            Tambah validasi slug & sku.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:products,slug',
            'sku'         => 'nullable|string|max:100|unique:products,sku',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'nullable|integer|min:0',
            'sizes'       => 'nullable|string',
            'description' => 'nullable|string',
            'images'      => 'required|array|min:1|max:4',
            'images.*'    => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $sizes = isset($validated['sizes']) ? json_decode($validated['sizes'], true) : null;
        $stock = $validated['stock'] ?? 0;
        if (is_array($sizes)) {
            $stock = array_sum($sizes);
        }

        DB::beginTransaction();

        try {
            $product = Product::create([
                'category_id' => $validated['category_id'],
                'name'        => $validated['name'],
                // Auto-generate slug jika tidak diisi (sudah ada di model booted)
                'slug'        => $validated['slug'] ?? Str::slug($validated['name']),
                'sku'         => $validated['sku'] ?? null,
                'price'       => $validated['price'],
                'stock'       => $stock,
                'sizes'       => $sizes,
                'description' => $validated['description'] ?? null,
            ]);

            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image'      => $path,
                    'sort_order' => $index + 1,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan.',
                'data'    => $product->load('images'),
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            // PERBAIKAN: dd() dihapus — tidak boleh ada di API production.
            // dd() akan merusak response JSON dan mematikan proses.
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan produk.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mengupdate produk yang ada.
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:products,slug,' . $product->id,
            'sku'         => 'nullable|string|max:100|unique:products,sku,' . $product->id,
            'price'       => 'required|numeric|min:0',
            'stock'       => 'nullable|integer|min:0',
            'sizes'       => 'nullable|string',
            'description' => 'nullable|string',
            'images'      => 'nullable|array|min:1|max:4',
            'images.*'    => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $sizes = isset($validated['sizes']) ? json_decode($validated['sizes'], true) : null;
        $stock = $validated['stock'] ?? $product->stock;
        if (is_array($sizes)) {
            $stock = array_sum($sizes);
        }

        DB::beginTransaction();

        try {
            $product->update([
                'category_id' => $validated['category_id'],
                'name'        => $validated['name'],
                'slug'        => $validated['slug'] ?? $product->slug,
                'sku'         => $validated['sku']  ?? $product->sku,
                'price'       => $validated['price'],
                'stock'       => $stock,
                'sizes'       => $sizes,
                'description' => $validated['description'] ?? null,
            ]);

            if ($request->hasFile('images')) {
                // Simpan gambar baru tanpa menghapus gambar lama
                $existingCount = $product->images()->count();
                $sortOffset = $existingCount;

                foreach ($request->file('images') as $index => $image) {
                    if ($existingCount + $index >= 4) break; // Maks 4 gambar total

                    $path = $image->store('products', 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image'      => $path,
                        'sort_order' => $sortOffset + $index + 1,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil diperbarui.',
                'data'    => $product->load('images'),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui produk.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menghapus produk beserta semua gambarnya.
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        DB::beginTransaction();

        try {
            // Hapus file gambar dari storage
            foreach ($product->images as $image) {
                if (Storage::disk('public')->exists($image->image)) {
                    Storage::disk('public')->delete($image->image);
                }
                $image->delete();
            }

            // Hapus data terkait di keranjang dan wishlist
            // Karena Product menggunakan SoftDeletes, DB cascade tidak otomatis berjalan
            \App\Models\CartItem::where('product_id', $product->id)->delete();
            DB::table('wishlists')->where('product_id', $product->id)->delete();

            // Ubah SKU dan Slug agar bisa digunakan lagi oleh produk baru tanpa error unik
            $newSku = $product->sku ? substr($product->sku, 0, 80) . '-del-' . time() : null;
            $newSlug = substr($product->slug, 0, 230) . '-del-' . time();
            $product->update([
                'sku' => $newSku,
                'slug' => $newSlug
            ]);

            $product->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil dihapus.',
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus produk.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menghapus spesifik satu gambar produk.
     */
    public function destroyImage($imageId)
    {
        $image = ProductImage::findOrFail($imageId);

        // Hapus file fisik
        if (Storage::disk('public')->exists($image->image)) {
            Storage::disk('public')->delete($image->image);
        }

        // Hapus record DB
        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gambar berhasil dihapus.',
        ]);
    }
}