<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiscountController;


// Đặt ở trên
Route::middleware('auth:sanctum')->get('/discounts/my-vouchers', [DiscountController::class, 'myVouchers']);

Route::prefix('discounts')->group(function () {
    Route::middleware('auth:sanctum')->get('/my-vouchers', [DiscountController::class, 'myVouchers']);
    Route::get('/', [DiscountController::class, 'index']);
    Route::get('/{id}', [DiscountController::class, 'show']);
    Route::post('/', [DiscountController::class, 'store']);
    Route::put('/{id}', [DiscountController::class, 'update']);
    Route::delete('/{id}', [DiscountController::class, 'destroy']);

    // Assign routes
    Route::post('/{discountId}/products', [DiscountController::class, 'assignProducts']);
    Route::post('/{discountId}/categories', [DiscountController::class, 'assignCategories']);
    Route::post('/{discountId}/users', [DiscountController::class, 'assignUsers']);

    // Flash sale routes
    Route::get('/flash-sales', [DiscountController::class, 'indexFlashSales']);
    Route::get('/flash-sales/{id}', [DiscountController::class, 'showFlashSale']);
    Route::post('/flash-sales', [DiscountController::class, 'storeFlashSale']);
    Route::put('/flash-sales/{id}', [DiscountController::class, 'updateFlashSale']);
    Route::delete('/flash-sales/{id}', [DiscountController::class, 'destroyFlashSale']);
});

Route::middleware('auth:sanctum')->post('/discounts/save-by-code', [DiscountController::class, 'saveVoucherByCode']);
Route::middleware('auth:sanctum')->delete('/discounts/my-voucher/{id}', [DiscountController::class, 'deleteUserVoucher']);
