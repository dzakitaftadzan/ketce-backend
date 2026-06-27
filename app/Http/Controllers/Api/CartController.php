<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\{Request, JsonResponse};

class CartController extends Controller
{
    public function index(): JsonResponse { return response()->json(Cart::with('variant')->get()); }
    public function store(Request $request): JsonResponse { return response()->json(Cart::create($request->all()), 201); }
}