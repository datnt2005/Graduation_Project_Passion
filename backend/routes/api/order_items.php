<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderItemController;


// Order Items
Route::prefix('order-items')->group(function () {
    Route::get('/', [OrderItemController::class, 'index']);
    Route::get('/{id}', [OrderItemController::class, 'show']);
    Route::post('/', [OrderItemController::class, 'store']);
    Route::put('/{id}', [OrderItemController::class, 'update']);
    Route::delete('/{id}', [OrderItemController::class, 'destroy']);
});