<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Address;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // Fungsi untuk buat pesanan (Checkout)
    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $user = $request->user();
            $address = Address::findOrFail($request->address_id);

            $cartItems = Cart::where('user_id', $user->id)->with('variant')->get();
            
            $subtotal = $cartItems->sum(fn($item) => $item->variant->price * $item->quantity);

            $order = Order::create([
                'user_id'      => $user->id,
                'address_id'   => $address->id,
                'subtotal'     => $subtotal,
                'total'        => $subtotal,
                'order_status' => 'pending'
            ]);

            // Hapus cart setelah berhasil order
            Cart::where('user_id', $user->id)->delete();

            return response()->json(['message' => 'Pesanan berhasil!', 'order' => $order], 201);
        });
    }

    // Fungsi untuk melihat detail pesanan
    public function show($id, Request $request)
{
    // Menggunakan where agar user hanya bisa melihat pesanannya sendiri
    $order = Order::where('id', $id)
                  ->where('user_id', $request->user()->id)
                  ->first();

    // Jika tidak ditemukan, kembalikan pesan error yang rapi
    return $order ?? response()->json(['message' => 'Order tidak ditemukan atau bukan milik Anda'], 404);
}

    // Fungsi untuk ganti status pesanan
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->update(['order_status' => $request->status]);
        
        return response()->json(['message' => 'Status berhasil diubah']);
    }
}