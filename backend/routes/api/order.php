<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

// Orders
Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::get('/{id}', [OrderController::class, 'show']);
    Route::post('/', [OrderController::class, 'store']);
    Route::put('/{id}', [OrderController::class, 'update']);
    Route::delete('/{id}', [OrderController::class, 'destroy']);

    // Thêm routes cho mã giảm giá
    Route::post('/{id}/apply-discount', [OrderController::class, 'applyDiscount']);
    Route::delete('/{id}/remove-discount', [OrderController::class, 'removeDiscount']);
});