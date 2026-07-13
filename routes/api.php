<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\Customer\AddressController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\TrackingController;

use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\CourierController;
use App\Http\Controllers\Admin\ProductController;

use App\Http\Controllers\Courier\DeliveryController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public endpoints for frontend catalog
Route::get('/products', function (Illuminate\Http\Request $request) {
    $query = \App\Models\Product::with('images')->latest();
    if ($request->has('featured')) {
        $query->limit($request->query('limit', 8));
    }
    return response()->json([
        'success' => true,
        'data' => $query->get()
    ]);
});

Route::get('/categories', function () {
    return response()->json([
        'success' => true,
        'data' => \App\Models\Category::all()
    ]);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Illuminate\Http\Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // ==========================
    // CUSTOMER
    // ==========================
    Route::middleware('checkrole:customer,admin')->group(function () {

        // Address
        Route::prefix('addresses')->group(function () {
            Route::get('/', [AddressController::class, 'index']);
            Route::post('/', [AddressController::class, 'store']);
            Route::delete('/{id}', [AddressController::class, 'destroy']);
        });

        // Cart
        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'index']);
            Route::post('/', [CartController::class, 'store']);
            Route::put('/{id}', [CartController::class, 'update']);
            Route::delete('/{id}', [CartController::class, 'destroy']);
        });

        // Orders
        Route::prefix('orders')->group(function () {
            Route::post('/', [CustomerOrderController::class, 'store']);
            Route::get('/', [CustomerOrderController::class, 'index']);
            Route::get('/{code}', [CustomerOrderController::class, 'show']);
            Route::delete('/{code}', [CustomerOrderController::class, 'cancel']);
            Route::get('/{code}/tracking', [TrackingController::class, 'show']);
        });
    });

    // ==========================
    // ADMIN
    // ==========================
    Route::middleware('checkrole:admin')->prefix('admin')->group(function () {

        // Product CRUD
        Route::apiResource('products', ProductController::class);

        // Order
        Route::get('/orders', [AdminOrderController::class, 'index']);
        Route::post('/orders/{id}/confirm', [AdminOrderController::class, 'confirmPayment']);
        Route::patch('/orders/{id}/pack', [AdminOrderController::class, 'pack']);
        Route::patch('/orders/{id}/assign', [AdminOrderController::class, 'assignCourier']);
        Route::delete('/orders/{id}', [AdminOrderController::class, 'cancelOrder']);
        Route::get('/stats', [AdminOrderController::class, 'stats']);

        // Courier
        Route::apiResource('couriers', CourierController::class);
        Route::patch('/couriers/{id}/toggle', [CourierController::class, 'toggle']);
    });

    // ==========================
    // COURIER
    // ==========================
    Route::middleware('checkrole:courier,admin')->prefix('courier')->group(function () {

        Route::get('/deliveries', [DeliveryController::class, 'index']);
        Route::post('/deliveries/{id}/pickup', [DeliveryController::class, 'pickup']);
        Route::post('/deliveries/{id}/done', [DeliveryController::class, 'done']);
        Route::post('/deliveries/{id}/failed', [DeliveryController::class, 'failed']);
    });
});
