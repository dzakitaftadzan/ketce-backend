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
            'payment_method' => 'nullable|string|max:50',
            'shipping_cost' => 'required|integer|min:0',
            'direct_buy' => 'nullable|boolean',
            'product_id' => 'required_if:direct_buy,true|exists:products,id',
            'quantity' => 'required_if:direct_buy,true|integer|min:1',
            'size' => 'nullable|string',
        ]);

        try {
            if ($request->direct_buy) {
                $order = $this->orderService->createDirectOrder(
                    auth()->user(),
                    $request->address_id,
                    $request->payment_method,
                    $request->product_id,
                    $request->quantity,
                    $request->size,
                    $request->shipping_cost
                );
            } else {
                $order = $this->orderService->createOrderFromCart(
                    auth()->user(),
                    $request->address_id,
                    $request->payment_method,
                    $request->shipping_cost
                );
            }

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
            ->with(['delivery', 'orderItems'])
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
            ->with(['orderItems.product', 'delivery'])
            ->firstOrFail();

        // [LOCAL DEV FIX] Auto-sync status dari Midtrans jika masih pending
        $this->orderService->syncMidtransStatus($order);

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
        $order = Order::with('orderItems.product')
            ->where('order_code', $code)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        try {
            $this->orderService->cancelOrder($order);
            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibatalkan dan stok dikembalikan.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}