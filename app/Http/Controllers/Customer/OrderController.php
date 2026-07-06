<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Checkout Order
     */
    public function store(Request $request)
    {
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'payment_proof' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        try {

            $path = $request->file('payment_proof')
                ->store('payment_proofs', 'public');

            $order = $this->orderService->createOrderFromCart(
                auth()->user(),
                $request->address_id,
                $path
            );

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibuat.',
                'data' => $order,
            ], 201);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Riwayat Order
     */
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Detail Order
     */
    public function show($code)
    {
        $order = Order::where('order_code', $code)
            ->where('user_id', auth()->id())
            ->with('orderItems')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Cancel Order
     */
    public function cancel($code)
    {
        $order = Order::where('order_code', $code)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($order->order_status != 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak dapat dibatalkan.',
            ], 400);
        }

        $order->update([
            'order_status' => 'cancelled',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order berhasil dibatalkan.',
        ]);
    }
}