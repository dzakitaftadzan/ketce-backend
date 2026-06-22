<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // Lihat isi keranjang user
    public function index(Request $request)
    {
        $carts = Cart::where('user_id', $request->user()->id)->with('variant.product')->get();
        return response()->json(['data' => $carts]);
    }

    // Tambah ke keranjang
    public function store(Request $request)
    {
        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = Cart::updateOrCreate(
            ['user_id' => $request->user()->id, 'product_variant_id' => $request->product_variant_id],
            ['quantity' => $request->quantity]
        );

        return response()->json(['message' => 'Produk masuk keranjang', 'data' => $cart]);
    }
}