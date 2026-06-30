<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CourierController;

Route::prefix('admin')->group(function () {
    Route::get('/couriers', [CourierController::class, 'index']);
    Route::post('/couriers', [CourierController::class, 'store']);
    Route::post('/couriers/{id}/toggle', [CourierController::class, 'toggle']);
});