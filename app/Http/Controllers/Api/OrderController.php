<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\{Request, JsonResponse};

class OrderController extends Controller
{
    public function index(): JsonResponse { return response()->json(Order::with(['items', 'delivery'])->get()); }
    public function store(Request $request): JsonResponse { return response()->json(Order::create($request->all()), 201); }
}