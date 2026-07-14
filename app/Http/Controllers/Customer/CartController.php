<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CartService;
use Exception;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index()
    {
        $cartData = $this->cartService->getCartForUser(auth()->id());

        return response()->json([
            'message' => empty($cartData['cart_id']) ? 'Keranjang kosong' : 'Data keranjang berhasil diambil',
            'cart'    => $cartData,
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'size'       => 'nullable|string',
        ]);

        try {
            $item = $this->cartService->addToCart(auth()->id(), $data);

            return response()->json([
                'message' => 'Barang berhasil masuk ke keranjang',
                'data' => $item
            ], 201);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $item = $this->cartService->updateQuantity(auth()->id(), $id, $request->quantity);

            return response()->json([
                'message' => 'Quantity berhasil diupdate',
                'data' => $item
            ], 200);
        } catch (Exception $e) {
            $status = in_array($e->getMessage(), ['Keranjang tidak ditemukan', 'Item keranjang tidak ditemukan']) ? 404 : 400;
            return response()->json(['message' => $e->getMessage()], $status);
        }
    }

    public function destroy($id)
    {
        try {
            $this->cartService->removeItem(auth()->id(), $id);

            return response()->json([
                'message' => 'Item berhasil dihapus dari keranjang'
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function clear()
    {
        $this->cartService->clearCart(auth()->id());

        return response()->json([
            'message' => 'Semua item keranjang berhasil dihapus'
        ], 200);
    }
}