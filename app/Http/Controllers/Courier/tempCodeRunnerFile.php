<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Order;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        // Menampilkan daftar kiriman milik kurir yang sedang login
        $deliveries = Delivery::where('courier_id', $request->user()->id)
            ->with('order')
            ->get();
            
        return response()->json(['data' => $deliveries]);
    }

    public function pickup($id)
    {
        $delivery = Delivery::where('id', $id)->where('courier_id', auth()->id())->firstOrFail();
        $delivery->update(['status' => 'delivering']);
        
        Order::where('id', $delivery->order_id)->update(['order_status' => 'on-delivery']);
        
        return response()->json(['message' => 'Pesanan telah diambil oleh kurir']);
    }

    public function done($id)
    {
        $delivery = Delivery::where('id', $id)->where('courier_id', auth()->id())->firstOrFail();
        $delivery->update(['status' => 'delivered']);
        
        Order::where('id', $delivery->order_id)->update(['order_status' => 'completed']);
        
        return response()->json(['message' => 'Pengiriman selesai']);
    }

    public function failed($id)
    {
        $delivery = Delivery::where('id', $id)->where('courier_id', auth()->id())->firstOrFail();
        $delivery->update(['status' => 'failed']);
        
        Order::where('id', $delivery->order_id)->update(['order_status' => 'failed']);
        
        return response()->json(['message' => 'Pengiriman gagal']);
    }
}