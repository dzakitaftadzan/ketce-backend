<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use App\Services\DeliveryService;
use App\Models\Delivery;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    protected \;

    public function __construct(DeliveryService \)
    {
        \->deliveryService = \;
    }

    public function pickup(\)
    {
        \ = Delivery::where('courier_id', auth()->id())->findOrFail(\);
        \->deliveryService->updateStatus(\, 'picked_up', auth()->id(), 'Paket diambil, dalam perjalanan');
        return response()->json(['message' => 'Paket berhasil di-pickup', 'data' => \]);
    }
}
