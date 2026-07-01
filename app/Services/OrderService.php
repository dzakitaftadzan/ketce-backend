<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    public function generateOrderCode(): string
    {
        $lastOrder = Order::latest()->first();
        $number = $lastOrder ? (int) substr($lastOrder->order_code, 8) + 1 : 1;
        return 'KTC-ORD-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    public function createOrderFromCart(User $user, int $addressId, string $paymentProofPath): Order
    {
        return DB::transaction(function () use ($user, $addressId, $paymentProofPath) {
            $cart = Cart::where('user_id', $user->id)->first();
            if (!$cart) throw new Exception("Keranjang tidak ditemukan");

            $cartItems = CartItem::where('cart_id', $cart->id)->with('product')->get();
            if ($cartItems->isEmpty()) throw new Exception("Keranjang kosong");

            $subtotal = 0;
            foreach ($cartItems as $item) {
                if ($item->product->stock < $item->quantity) {
                    throw new Exception("Stok {$item->product->name} tidak cukup.");
                }
                $subtotal += ($item->product->price * $item->quantity);
            }

            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $addressId,
                'order_code' => $this->generateOrderCode(),
                'subtotal' => $subtotal,
                'shipping_cost' => 15000,
                'total_price' => $subtotal + 15000,
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'payment_proof' => $paymentProofPath
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_name' => $item->product->name,
                    'variant_info' => 'Default',
                    'quantity' => $item->quantity,
                    'price' => $item->product->price
                ]);
                
                $item->product->decrement('stock', $item->quantity);
            }

            CartItem::where('cart_id', $cart->id)->delete();
            $cart->delete();
            
            return $order;
        });
    }
}