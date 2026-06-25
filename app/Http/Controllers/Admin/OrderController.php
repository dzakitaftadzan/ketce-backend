<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request \)
    {
        \ = Order::with(['user', 'orderItems'])->latest()->paginate(20);
        return response()->json(\);
    }

    public function confirmPayment(Request \, \)
    {
        \->validate(['action' => 'required|in:accept,reject']);
        \ = Order::findOrFail(\);

        if (\->action === 'accept') {
            DB::transaction(function () use (\) {
                \->update(['payment_status' => 'paid', 'order_status' => 'confirmed']);
                foreach (\->orderItems as \) {
                    // Logika pengurangan stok (asumsi variant_info mengandung data untuk mencari variant)
                    // Ini contoh sederhana, nanti bisa disesuaikan dengan relasi yang lebih presisi
                    ProductVariant::where('sku', 'like', '%' . \->product_name . '%')->decrement('stock', \->quantity);
                }
            });
            return response()->json(['message' => 'Pembayaran diterima']);
        } else {
            \->update(['payment_status' => 'failed']);
            return response()->json(['message' => 'Bukti bayar ditolak']);
        }
    }
}
