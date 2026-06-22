<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;

// --- 1. PUBLIC ROUTES ---
// Area bebas akses (Tanpa Token)
Route::post('/products', [ProductController::class, 'store']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

// Rute upload gambar di sini (Tidak perlu login)
Route::post('/products/{id}/images', [ProductController::class, 'uploadImages']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// --- 2. PROTECTED ROUTES ---
// Area yang mewajibkan Token (Sesuai struktur sistemmu)
Route::middleware('auth:sanctum')->group(function () {
    
    // Fitur Keranjang
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    
    // Fitur Orders
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
});