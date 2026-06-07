<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
{
    return DB::transaction(function () use ($request) {
        $user = $request->user();

        // Cari atau buat alamat asal-asalan saja supaya tidak error
        $address = \App\Models\Address::firstOrCreate(
            ['user_id' => $user->id],
            ['city' => 'Makassar'] // Hanya isi yang benar-benar wajib saja
        );

        $cartItems = \App\Models\Cart::where('user_id', $user->id)->with('variant')->get();
        
        $subtotal = $cartItems->sum(fn($item) => $item->variant->price * $item->quantity);

        $order = \App\Models\Order::create([
            'user_id'      => $user->id,
            'address_id'   => $address->id,
            'subtotal'     => $subtotal,
            'total'        => $subtotal, // Abaikan biaya lain kalau tidak perlu
            'order_status' => 'pending'
        ]);

        // ... (sisanya logika OrderItems dan hapus Cart) ...

        return response()->json(['message' => 'Pesanan berhasil!'], 201);
    });
}
}