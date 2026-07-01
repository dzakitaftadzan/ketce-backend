<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    public function generateOrderCode(): string
    {
        $lastOrder = Order::latest()->first();

        $number = 1;

        if ($lastOrder && $lastOrder->order_code) {
            $parts = explode('-', $lastOrder->order_code);
            if (isset($parts[2]) && is_numeric($parts[2])) {
                $number = (int) $parts[2] + 1;
            } else {
                $number = (int) substr($lastOrder->order_code, 8) + 1;
            }
        }

        // Add a random 4-letter suffix to prevent Midtrans order_id collision on DB reset
        $randomStr = strtoupper(substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 4));
        return 'KTC-ORD-' . str_pad($number, 5, '0', STR_PAD_LEFT) . '-' . $randomStr;
    }

    public function createOrderFromCart(User $user, int $addressId, ?string $paymentMethod = null, int $shippingCost = 15000): Order
    {
        return DB::transaction(function () use ($user, $addressId, $paymentMethod, $shippingCost) {

            $cart = Cart::where('user_id', $user->id)->first();

            if (!$cart) {
                throw new Exception('Keranjang tidak ditemukan');
            }

            $cartItems = CartItem::with('product')
                ->where('cart_id', $cart->id)
                ->get();

            if ($cartItems->isEmpty()) {
                throw new Exception('Keranjang kosong');
            }

            $subtotal = 0;

            foreach ($cartItems as $item) {

                if (!$item->product) {
                    throw new Exception("Produk tidak ditemukan.");
                }

                $maxStock = $item->product->stock;
                if (is_array($item->product->sizes) && $item->size && isset($item->product->sizes[$item->size])) {
                    $maxStock = $item->product->sizes[$item->size];
                }

                if ($maxStock < $item->quantity) {
                    throw new Exception("Stok {$item->product->name} " . ($item->size ? "ukuran {$item->size} " : "") . "tidak cukup.");
                }

                $subtotal += $item->product->price * $item->quantity;
            }

            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $addressId,
                'order_code' => $this->generateOrderCode(),
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total_price' => $subtotal + $shippingCost,
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'payment_proof' => null,
                'payment_method' => $paymentMethod,
            ]);

            foreach ($cartItems as $item) {

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product->id,
                    'product_name' => $item->product->name,
                    'variant_info' => $item->size ? 'Ukuran: ' . $item->size : 'Default',
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);

                // Reduce stock
                $product = $item->product;
                $product->decrement('stock', $item->quantity);
                
                if (is_array($product->sizes) && $item->size && isset($product->sizes[$item->size])) {
                    $sizes = $product->sizes;
                    $sizes[$item->size] -= $item->quantity;
                    $product->sizes = $sizes;
                    $product->save();
                }
            }

            $cart->items()->delete();
            $cart->delete();

            return $order;
        });
    }

    public function createDirectOrder(
        User $user,
        int $addressId,
        ?string $paymentMethod,
        int $productId,
        int $quantity,
        ?string $size = null,
        int $shippingCost = 15000
    ): Order {
        return DB::transaction(function () use ($user, $addressId, $paymentMethod, $productId, $quantity, $size, $shippingCost) {

            $product = Product::findOrFail($productId);

            $maxStock = $product->stock;
            if (is_array($product->sizes) && $size && isset($product->sizes[$size])) {
                $maxStock = $product->sizes[$size];
            }

            if ($maxStock < $quantity) {
                throw new Exception("Stok {$product->name} " . ($size ? "ukuran {$size} " : "") . "tidak cukup.");
            }

            $subtotal = $product->price * $quantity;

            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $addressId,
                'order_code' => $this->generateOrderCode(),
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total_price' => $subtotal + $shippingCost,
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'payment_proof' => null,
                'payment_method' => $paymentMethod,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'variant_info' => $size ? 'Ukuran: ' . $size : 'Default',
                'quantity' => $quantity,
                'price' => $product->price,
            ]);

            // Reduce stock
            $product->decrement('stock', $quantity);
            
            if (is_array($product->sizes) && $size && isset($product->sizes[$size])) {
                $sizes = $product->sizes;
                $sizes[$size] -= $quantity;
                $product->sizes = $sizes;
                $product->save();
            }

            return $order;
        });
    }

    public function cancelOrder(Order $order): void
    {
        if (!in_array($order->order_status, ['pending', 'confirmed', 'packed'])) {
            throw new Exception('Pesanan yang sudah dalam pengiriman atau selesai tidak dapat dibatalkan.');
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'order_status' => 'cancelled',
                'payment_status' => 'rejected',
            ]);

            // Kembalikan stok
            foreach ($order->orderItems as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }
        });
    }

    public function confirmPayment(Order $order, string $action): void
    {
        if ($order->payment_status === 'paid') {
            throw new Exception('Pembayaran untuk pesanan ini sudah dikonfirmasi.');
        }

        if ($action === 'accept') {
            $order->update([
                'payment_status' => 'paid',
                'order_status'   => 'confirmed',
            ]);
            return;
        }

        // action === 'reject'
        $this->cancelOrder($order);
    }

    public function packOrder(Order $order): void
    {
        if ($order->order_status !== 'confirmed') {
            throw new Exception('Pesanan harus berstatus CONFIRMED sebelum bisa dikemas.');
        }

        $order->update(['order_status' => 'packed']);
    }

    public function assignCourier(Order $order, int $courierId): \App\Models\Delivery
    {
        if ($order->order_status !== 'packed') {
            throw new Exception('Pesanan harus berstatus PACKED sebelum bisa di-assign kurir.');
        }

        $courier = User::where('id', $courierId)
            ->where('role', 'kurir')
            ->where('is_active', true)
            ->first();

        if (!$courier) {
            throw new Exception('Kurir tidak ditemukan atau tidak aktif.');
        }

        return DB::transaction(function () use ($order, $courier) {
            $delivery = \App\Models\Delivery::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'courier_id'      => $courier->id,
                    'status'          => 'assigned',
                    'delivery_code' => 'KTC-' . strtoupper(uniqid()),
                ]
            );

            $order->update(['order_status' => 'shipping']);
            return $delivery;
        });
    }

    public function syncMidtransStatus(Order $order): void
    {
        if ($order->payment_status === 'pending' && $order->payment_token) {
            try {
                \Midtrans\Config::$serverKey = config('midtrans.server_key');
                \Midtrans\Config::$isProduction = config('midtrans.is_production');
                $status = \Midtrans\Transaction::status($order->order_code);
                
                if (in_array($status->transaction_status, ['capture', 'settlement'])) {
                    $order->update([
                        'payment_status' => 'paid',
                        'order_status' => 'confirmed',
                        'paid_at' => now(),
                        'payment_method' => $status->payment_type
                    ]);
                } elseif (in_array($status->transaction_status, ['deny', 'cancel', 'expire'])) {
                    if ($order->order_status !== 'cancelled') {
                        $this->cancelOrder($order);
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Auto-sync Midtrans Error: ' . $e->getMessage());
            }
        }
    }
}