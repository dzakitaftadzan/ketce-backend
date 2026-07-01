<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\OrderService;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Daftar semua pesanan (admin view).
     */
    public function index()
    {
        $orders = Order::with(['user', 'address', 'orderItems'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $orders,
        ]);
    }

    /**
     * Konfirmasi atau tolak pembayaran.
     * Body: { action: 'accept' | 'reject' }
     */
    public function confirmPayment(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:accept,reject',
        ]);

        $order = Order::findOrFail($id);

        try {
            $this->orderService->confirmPayment($order, $request->action);
            return response()->json([
                'success' => true,
                'message' => $request->action === 'accept' 
                    ? 'Pembayaran berhasil dikonfirmasi. Pesanan masuk status CONFIRMED.'
                    : 'Bukti pembayaran ditolak. Pesanan dibatalkan dan stok dikembalikan.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Ubah status pesanan menjadi 'packed' (sudah dikemas, siap dikirim).
     */
    public function pack($id)
    {
        $order = Order::findOrFail($id);

        try {
            $this->orderService->packOrder($order);
            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil diubah ke status PACKED.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Assign kurir ke pesanan dan buat record Delivery.
     * Body: { courier_id: number }
     */
    public function assignCourier(Request $request, $id)
    {
        $request->validate([
            'courier_id' => 'required|exists:users,id',
        ]);

        $order = Order::findOrFail($id);

        try {
            $delivery = $this->orderService->assignCourier($order, $request->courier_id);
            return response()->json([
                'success' => true,
                'message' => "Kurir berhasil di-assign. Pesanan masuk status SHIPPING.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Admin batalkan pesanan.
     */
    public function cancelOrder($id)
    {
        $order = Order::with('orderItems.product')->findOrFail($id);

        try {
            $this->orderService->cancelOrder($order);
            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibatalkan oleh admin dan stok dikembalikan.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Statistik dashboard admin.
     */
    public function stats()
    {
        $totalOrders    = Order::count();
        $pendingOrders  = Order::where('order_status', 'pending')->count();
        $totalRevenue   = Order::where('payment_status', 'paid')->sum('total_price');
        $todayOrders    = Order::whereDate('created_at', today())->count();
        $activeCouriers = User::where('role', 'kurir')->where('is_active', true)->count();

        $ordersByStatus = Order::select('order_status', DB::raw('count(*) as total'))
            ->groupBy('order_status')
            ->pluck('total', 'order_status');

        return response()->json([
            'success' => true,
            'data'    => [
                'total_orders'    => $totalOrders,
                'pending_orders'  => $pendingOrders,
                'today_orders'    => $todayOrders,
                'total_revenue'   => (float) $totalRevenue,
                'active_couriers' => $activeCouriers,
                'orders_by_status' => $ordersByStatus,
            ],
        ]);
    }
}