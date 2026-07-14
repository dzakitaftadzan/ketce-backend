<?php

namespace App\Services;

use App\Models\Order;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;
use Exception;

class PaymentService
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;

        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function initiatePayment($orderCode)
    {
        $order = Order::with(['orderItems.product', 'user'])->where('order_code', $orderCode)->firstOrFail();
        
        // Return existing token if it's already generated and pending
        if ($order->payment_token && $order->payment_status === 'pending') {
            return $order->payment_token;
        }

        $itemDetails = [];
        foreach ($order->orderItems as $item) {
            $itemDetails[] = [
                'id' => (string) $item->product_id,
                'price' => (int) $item->price,
                'quantity' => (int) $item->quantity,
                'name' => substr($item->product_name ?? $item->product->name, 0, 50),
            ];
        }

        // Add shipping cost to item_details
        $itemDetails[] = [
            'id' => 'SHIPPING',
            'price' => (int) $order->shipping_cost,
            'quantity' => 1,
            'name' => 'Ongkos Kirim'
        ];

        $customerDetails = [
            'first_name' => $order->user->name ?? 'Customer',
            'email' => $order->user->email ?? 'customer@example.com',
            'phone' => $order->user->phone ?? '081234567890',
        ];

        $params = [
            'transaction_details' => [
                'order_id' => $order->order_code,
                'gross_amount' => (int) $order->total_price,
            ],
            'customer_details' => $customerDetails,
            'item_details' => $itemDetails,
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            
            $order->update([
                'payment_token' => $snapToken,
                'payment_status' => 'pending'
            ]);
            
            return $snapToken;
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Midtrans Error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            throw new Exception('Failed to initiate payment: ' . $e->getMessage());
        }
    }

    public function handleNotification($notificationData)
    {
        try {
            $notification = new Notification();
        } catch (Exception $e) {
            // Jika ada error payload atau signature, fallback ke data array
            $notification = (object) $notificationData;
        }
        
        $orderCode = $notification->order_id ?? ($notificationData['order_id'] ?? null);
        $transactionStatus = $notification->transaction_status ?? ($notificationData['transaction_status'] ?? null);
        $paymentType = $notification->payment_type ?? ($notificationData['payment_type'] ?? null);
        
        if ($orderCode) {
            $order = Order::with('orderItems.product')->where('order_code', $orderCode)->first();
            if ($order) {
                if (in_array($transactionStatus, ['capture', 'settlement'])) {
                    $order->update([
                        'payment_status' => 'paid',
                        'order_status' => 'confirmed',
                        'paid_at' => now(),
                        'payment_method' => $paymentType
                    ]);
                } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
                    if ($order->order_status !== 'cancelled') {
                        $this->orderService->cancelOrder($order);
                    }
                }
            }
        }
    }
}
