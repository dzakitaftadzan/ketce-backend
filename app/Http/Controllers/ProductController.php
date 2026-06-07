<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    // Mengambil semua produk yang aktif
    public function index(): JsonResponse
    {
        // Mengambil produk beserta variannya
        $products = Product::where('is_active', true)->with('variants')->get();
        
        return response()->json([
            'message' => 'Berhasil mengambil daftar produk',
            'data' => $products
        ], 200);
    }

    // Mengambil detail produk berdasarkan ID
    public function show(Product $product): JsonResponse
    {
        $product->load('variants');
        
        return response()->json([
            'message' => 'Berhasil mengambil detail produk',
            'data' => $product
        ], 200);
    }
}