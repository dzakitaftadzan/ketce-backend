<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Exception;

class CartService
{
    public function getCartForUser($userId)
    {
        $cart = Cart::where('user_id', $userId)
            ->with('items.product.images')
            ->first();

        if (!$cart) {
            return [
                'cart_id' => null,
                'items'   => [],
                'total'   => 0,
            ];
        }

        $total = $cart->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        return [
            'cart_id' => $cart->id,
            'items'   => $cart->items,
            'total'   => $total,
        ];
    }

    public function addToCart($userId, array $data)
    {
        $product = Product::findOrFail($data['product_id']);
        $size = $data['size'] ?? null;
        $quantity = $data['quantity'];

        $maxStock = $product->stock;

        if (is_array($product->sizes)) {
            if (!$size && count($product->sizes) > 0) {
                throw new Exception('Silakan pilih ukuran');
            }
            if ($size && isset($product->sizes[$size])) {
                $maxStock = $product->sizes[$size];
            }
        }

        if ($quantity > $maxStock) {
            throw new Exception('Stok tidak mencukupi');
        }

        $cart = Cart::firstOrCreate(['user_id' => $userId]);

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('size', $size)
            ->first();

        if ($item) {
            $newQty = $item->quantity + $quantity;

            if ($newQty > $maxStock) {
                throw new Exception('Stok tidak mencukupi');
            }

            $item->quantity = $newQty;
            $item->save();
        } else {
            $item = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'price' => $product->price,
                'size' => $size,
            ]);
        }

        return $item;
    }

    public function updateQuantity($userId, $itemId, $quantity)
    {
        $cart = Cart::where('user_id', $userId)->first();

        if (!$cart) {
            throw new Exception('Keranjang tidak ditemukan');
        }

        $item = CartItem::where('cart_id', $cart->id)
            ->where('id', $itemId)
            ->first();

        if (!$item) {
            throw new Exception('Item keranjang tidak ditemukan');
        }

        $product = Product::findOrFail($item->product_id);

        $maxStock = $product->stock;
        if (is_array($product->sizes) && $item->size && isset($product->sizes[$item->size])) {
            $maxStock = $product->sizes[$item->size];
        }

        if ($quantity > $maxStock) {
            throw new Exception('Stok tidak mencukupi');
        }

        $item->quantity = $quantity;
        $item->save();

        return $item;
    }

    public function removeItem($userId, $itemId)
    {
        $cart = Cart::where('user_id', $userId)->first();

        if (!$cart) {
            throw new Exception('Keranjang tidak ditemukan');
        }

        $item = CartItem::where('cart_id', $cart->id)
            ->where('id', $itemId)
            ->first();

        if (!$item) {
            throw new Exception('Item keranjang tidak ditemukan');
        }

        $item->delete();
    }

    public function clearCart($userId)
    {
        $cart = Cart::where('user_id', $userId)->first();

        if (!$cart) {
            return; // Already clear
        }

        CartItem::where('cart_id', $cart->id)->delete();
    }
}
