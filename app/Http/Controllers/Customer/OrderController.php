<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function store(Request $request)
    {
        // Validasi
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'payment_proof' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        try {
            // Upload file ke storage/app/public/payment_proofs
            $path = $request->file('payment_proof')->store('payment_proofs', 'public');
            
            // Panggil Service
            $order = $this->orderService->createOrderFromCart(auth()->user(), $request->address_id, $path);
            
            return response()->json([
                'message' => 'Order berhasil dibuat', 
                'data' => $order
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}