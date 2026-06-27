<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(): JsonResponse { return response()->json(Product::with('variants')->get()); }
    public function show($id): JsonResponse { return response()->json(Product::with('variants')->findOrFail($id)); }
}