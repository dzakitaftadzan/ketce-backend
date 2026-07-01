<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\Product;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $wishlists = $request->user()->wishlists()->with(['product.category', 'product.images'])->get();
        // Return products directly for easier frontend consumption
        $products = $wishlists->pluck('product');
        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function toggle(Request $request, $productId)
    {
        $user = $request->user();
        $product = Product::findOrFail($productId);
        
        $wishlist = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            return response()->json([
                'success' => true,
                'message' => 'Produk dihapus dari wishlist',
                'is_wishlisted' => false
            ]);
        } else {
            Wishlist::create([
                'user_id' => $user->id,
                'product_id' => $productId
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Produk ditambahkan ke wishlist',
                'is_wishlisted' => true
            ]);
        }
    }
}
