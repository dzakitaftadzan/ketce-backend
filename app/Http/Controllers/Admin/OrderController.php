<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // ... method index kamu ...

    public function confirmPayment(Request $request, $id)
    {
        $request->validate(['action' => 'required|in:accept,reject']);
        $order = Order::with('orderItems')->findOrFail($id);

        if ($order->payment_status === 'paid') {
            return response()->json(['message' => 'Pesanan sudah dibayar'], 400);
        }

        return DB::transaction(function () use ($order, $request) {
            if ($request->action === 'accept') {
                $order->update(['payment_status' => 'paid', 'order_status' => 'confirmed']);
                foreach ($order->orderItems as $item) {
                    $variant = ProductVariant::where('name', $item->variant_info)->first();
                    if ($variant) {
                        $variant->decrement('stock', $item->quantity);
                    }
                }
                return response()->json(['message' => 'Pembayaran diterima']);
            }
            $order->update(['payment_status' => 'failed', 'order_status' => 'cancelled']);
            return response()->json(['message' => 'Bukti bayar ditolak']);
        });
    }
}