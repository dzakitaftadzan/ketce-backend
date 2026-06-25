<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    protected \;

    public function __construct(OrderService \)
    {
        \->orderService = \;
    }

    public function store(Request \)
    {
        \->validate([
            'address_id' => 'required|exists:addresses,id',
            'payment_proof' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        \ = \->file('payment_proof')->store('payment_proofs', 'public');

        try {
            \ = \->orderService->createOrderFromCart(auth()->user(), \->address_id, \);
            return response()->json(['message' => 'Order created', 'data' => \], 201);
        } catch (\Exception \) {
            return response()->json(['message' => \->getMessage()], 422);
        }
    }
}
