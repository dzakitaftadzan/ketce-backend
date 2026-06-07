<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;


// --- 1. PUBLIC ROUTES ---
// Jalur yang bisa diakses oleh siapa saja (tidak butuh token)
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


// --- 2. PROTECTED ROUTES ---
// Jalur yang hanya bisa diakses jika user sudah login (Wajib bawa token)
Route::middleware('auth:sanctum')->group(function () {
    
    // Fitur Keranjang
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::post('/orders', [OrderController::class, 'store']);
    // Nanti di sini kita akan tambahkan route untuk Orders / Checkout
});