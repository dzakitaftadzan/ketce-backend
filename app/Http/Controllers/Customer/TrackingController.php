<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function show($code, Request $request)
    {
        $order = \App\Models\Order::where('order_code', $code)
            ->where('user_id', $request->user()->id)
            ->with(['delivery.courier', 'address'])
            ->firstOrFail();

        // Return the necessary data for tracking
        return response()->json([
            'status' => 'success',
            'data' => [
                'order_code' => $order->order_code,
                'order_status' => $order->order_status,
                'delivery' => $order->delivery,
                'address' => $order->address
            ]
        ]);
    }
}
