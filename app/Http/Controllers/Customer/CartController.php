<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;

class CartController extends Controller
{
    public function index()
    {
        $cart = Cart::where('user_id', auth()->id())
            ->with('items')
            ->first();

        if (!$cart) {
            return response()->json([
                'message' => 'Keranjang kosong',
                'data' => []
            ], 200);
        }

        $total = $cart->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        return response()->json([
            'message' => 'Data keranjang berhasil diambil',
            'data' => [
                'cart_id' => $cart->id,
                'items' => $cart->items,
                'total' => $total
            ]
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $cart = Cart::firstOrCreate([
            'user_id' => auth()->id()
        ]);

        $product = Product::findOrFail($request->product_id);

        $item = CartItem::where('cart_id', $cart->id)
                        ->where('product_id', $product->id)
                        ->first();

        if ($item) {
            $item->quantity += $request->quantity;
            $item->save();
        } else {
            $item = CartItem::create([
                'cart_id'      => $cart->id,
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'quantity'     => $request->quantity,
                'price'        => $product->price,
            ]);
        }

        return response()->json([
            'message' => 'Barang berhasil masuk ke keranjang',
            'data'    => $item
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::where('user_id', auth()->id())->first();

        if (!$cart) {
            return response()->json([
                'message' => 'Keranjang tidak ditemukan'
            ], 404);
        }

        $item = CartItem::where('cart_id', $cart->id)
            ->where('id', $id)
            ->first();

        if (!$item) {
            return response()->json([
                'message' => 'Item keranjang tidak ditemukan'
            ], 404);
        }

        $item->quantity = $request->quantity;
        $item->save();

        return response()->json([
            'message' => 'Quantity berhasil diupdate',
            'data' => $item
        ], 200);
    }

    public function destroy($id)
    {
        $cart = Cart::where('user_id', auth()->id())->first();

        if (!$cart) {
            return response()->json([
                'message' => 'Keranjang tidak ditemukan'
            ], 404);
        }

        $item = CartItem::where('cart_id', $cart->id)
            ->where('id', $id)
            ->first();

        if (!$item) {
            return response()->json([
                'message' => 'Item keranjang tidak ditemukan'
            ], 404);
        }

        $item->delete();

        return response()->json([
            'message' => 'Item berhasil dihapus dari keranjang'
        ], 200);
    }

    public function clear()
    {
        $cart = Cart::where('user_id', auth()->id())->first();

        if (!$cart) {
            return response()->json([
                'message' => 'Keranjang sudah kosong'
            ], 200);
        }

        CartItem::where('cart_id', $cart->id)->delete();

        return response()->json([
            'message' => 'Semua item keranjang berhasil dihapus'
        ], 200);
    }
}