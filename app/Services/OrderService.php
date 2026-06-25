<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function generateOrderCode()
    {
         = Order::latest()->first();
         =  ? (int) substr(->order_code, 8) + 1 : 1;
        return 'KTC-ORD-' . str_pad(, 5, '0', STR_PAD_LEFT);
    }

    public function calculateTotal(array , int )
    {
         = 0;
        foreach ( as ) {
             += (\['product_variant']['price'] * \['quantity']);
        }
        return [\, \, \ + \];
    }

    public function createOrderFromCart(\, \, \)
    {
        return DB::transaction(function () use (\, \, \) {
            \ = Cart::where('user_id', \->id)->with('productVariant.product')->get();
            
            if (\->isEmpty()) {
                throw new \Exception("Cart is empty");
            }

            [\, \, \] = \->calculateTotal(\->toArray(), 15000);

            \ = Order::create([
                'user_id' => \->id,
                'address_id' => \,
                'order_code' => \->generateOrderCode(),
                'subtotal' => \,
                'shipping_cost' => \,
                'total' => \,
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'payment_proof' => \
            ]);

            foreach (\ as \) {
                OrderItem::create([
                    'order_id' => \->id,
                    'product_name' => \->productVariant->product->name,
                    'variant_info' => \->productVariant->size . ' / ' . \->productVariant->color,
                    'quantity' => \->quantity,
                    'price' => \->productVariant->price
                ]);
            }

            Cart::where('user_id', \->id)->delete();
            return \;
        });
    }
}
