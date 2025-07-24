<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminSellerController;
use App\Http\Controllers\SellerOrderController;

Route::prefix('admin')->group(function () {
   Route::get('/sellers', [AdminSellerController::class, 'index']);
   Route::get('/sellers/{id}', [AdminSellerController::class, 'show']);
   Route::post('/sellers/{id}/verify', [AdminSellerController::class, 'verify']);
   Route::post('/sellers/{id}/reject', [AdminSellerController::class, 'reject']);

    Route::post('/sellers/{id}/ban', [AdminSellerController::class, 'ban']);
});

Route::middleware(['auth:sanctum'])->get('/orders/seller', [SellerOrderController::class, 'index']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::put('/orders/seller/{id}/status', [SellerOrderController::class, 'updateStatus']);
});
Route::middleware(['auth:sanctum'])->group(function () {
   Route::get('orders/seller/{id}', [SellerOrderController::class, 'show']);
});
