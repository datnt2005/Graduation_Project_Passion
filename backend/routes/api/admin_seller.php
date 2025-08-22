<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminSellerController;
use App\Http\Controllers\SellerOrderController;
use App\Http\Controllers\OrderController;

Route::prefix('admin')->group(function () {
   Route::get('/sellers', [AdminSellerController::class, 'index']);
   Route::get('/sellers/{id}', [AdminSellerController::class, 'show']);
   Route::post('/sellers/{id}/verify', [AdminSellerController::class, 'verify']);
   Route::post('/sellers/{id}/reject', [AdminSellerController::class, 'reject']);

    Route::post('/sellers/{id}/ban', [AdminSellerController::class, 'ban']);
    Route::post('/sellers/{id}/unban', [AdminSellerController::class, 'unban']);
});

Route::middleware(['auth:sanctum', 'checkRole:seller'])->get('/orders/seller', [SellerOrderController::class, 'index']);

Route::middleware(['auth:sanctum', 'checkRole:seller'])->group(function () {
    Route::put('/orders/seller/{id}/status', [SellerOrderController::class, 'updateStatus']);
    Route::delete('/orders/seller/bulk-delete', [SellerOrderController::class, 'bulkDelete']);
});
Route::middleware(['auth:sanctum', 'checkRole:seller'])->group(function () {
   Route::get('orders/seller/{id}', [SellerOrderController::class, 'show']);
   Route::post('orders/seller/{orderId}/sync-ghn', [OrderController::class, 'syncGhnStatus']);
});
