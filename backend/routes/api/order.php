<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

Route::middleware('auth:sanctum')->get('/orders/check-cod-eligibility', [OrderController::class, 'checkCodEligibility']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/shipping-methods', [OrderController::class, 'GetShipping']);
    Route::post('/shipping-methods', [OrderController::class, 'upsertShippingMethod']);
    Route::put('/shipping-methods/{id}', [OrderController::class, 'upsertShippingMethod']);
});
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
        Route::delete('/{id}', [OrderController::class, 'destroy']);
        Route::post('/{id}/refund', [OrderController::class, 'refund']);
        Route::get('/orders/{id}/refund', [OrderController::class, 'getRefund']);
       Route::put('/seller/{id}/status', [OrderController::class, 'update']);
    });

    
    
    // Áp dụng / gỡ mã giảm giá – chỉ cho admin và seller
    Route::middleware('checkRole:admin,seller')->group(function () {
        Route::post('/{id}/apply-discount', [OrderController::class, 'applyDiscount']);
        Route::delete('/{id}/remove-discount', [OrderController::class, 'removeDiscount']);
    });

    Route::middleware('auth:sanctum')->post('/validate-buy-now', [OrderController::class, 'validateBuyNow']);

    // Đồng bộ trạng thái GHN (Seller hoặc Admin)
    Route::middleware('checkRole:admin,seller')->post('/seller/{orderId}/sync-ghn', [OrderController::class, 'syncGhnStatus']);
});


