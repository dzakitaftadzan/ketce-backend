<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\{Request, JsonResponse};

class AddressController extends Controller
{
    public function index(): JsonResponse { return response()->json(Address::all()); }
    public function store(Request $request): JsonResponse { return response()->json(Address::create($request->all()), 201); }
}