<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

Route::prefix('orders')->middleware(['auth:sanctum'])->group(function () {

    // Admin hoặc Seller được quyền xem danh sách & chi tiết đơn hàng
    Route::middleware('checkRole:admin,seller')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/{id}', [OrderController::class, 'show']);
    });

    // User đặt đơn hàng (store)
    Route::middleware('checkRole:user')->post('/', [OrderController::class, 'store']);

    // Admin hoặc Seller được quyền sửa & xóa đơn
    Route::middleware('checkRole:admin,seller')->group(function () {
        Route::put('/{id}', [OrderController::class, 'update']);
        Route::delete('/{id}', [OrderController::class, 'destroy']);
    });

    // Áp dụng / gỡ mã giảm giá – chỉ cho admin và seller
    Route::middleware('checkRole:admin,seller')->group(function () {
        Route::post('/{id}/apply-discount', [OrderController::class, 'applyDiscount']);
        Route::delete('/{id}/remove-discount', [OrderController::class, 'removeDiscount']);
    });

    Route::middleware('auth:sanctum')->post('/validate-buy-now', [OrderController::class, 'validateBuyNow']);
});
