<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function show(\)
    {
        \ = Order::where('order_code', \)->where('user_id', auth()->id())->with(['delivery.deliveryLogs', 'delivery.courier'])->firstOrFail();

        \ = [
            'pending' => 1, 'confirmed' => 1,
            'packed' => 2,
            'delivering' => 3,
            'delivered' => 4
        ];

        return response()->json([
            'order_code' => \->order_code,
            'current_status' => \->order_status,
            'current_step' => \[\->order_status] ?? 1,
            'delivery' => \->delivery,
        ]);
    }
}
