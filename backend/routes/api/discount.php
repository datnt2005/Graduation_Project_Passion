<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\DiscountSellerController;

Route::get('/discounts/all', [DiscountController::class, 'indexPublic']);    
Route::get('/discounts/seller/{sellerId}', [DiscountController::class, 'getSellerDiscounts']);    
    Route::get('/sellers/store/{slug}/discounts', [DiscountController::class, 'getStoreDiscounts']);

// Các route dành cho user đã đăng nhập
Route::middleware(['auth:sanctum', 'checkRole:user,seller,admin'])->group(function () {
    Route::get('/discounts/my-vouchers', [DiscountController::class, 'myVouchers']);
    Route::post('/discounts/save-by-code', [DiscountController::class, 'saveVoucherByCode']);
    Route::post('/discounts/check', [DiscountController::class, 'checkVoucher']);
    Route::post('/discounts/check-shop-discount', [DiscountController::class, 'checkShopDiscount']);
    Route::delete('/discounts/my-voucher/{id}', [DiscountController::class, 'deleteUserVoucher']);
});

// Các route quản lý ưu đãi dành cho admin
Route::prefix('discounts')->middleware(['auth:sanctum', 'checkRole:admin'])->group(function () {
    Route::get('/', [DiscountController::class, 'index']);
    Route::get('/{id}', [DiscountController::class, 'show']);
    Route::post('/', [DiscountController::class, 'store']);
    Route::put('/{id}', [DiscountController::class, 'update']);
    Route::delete('/{id}', [DiscountController::class, 'destroy']);

    // Gán đối tượng cho ưu đãi
    Route::post('/{discountId}/products', [DiscountController::class, 'assignProducts']);
    Route::post('/{discountId}/categories', [DiscountController::class, 'assignCategories']);
    Route::post('/{discountId}/users', [DiscountController::class, 'assignUsers']);

    // Flash sale
    Route::get('/flash-sales', [DiscountController::class, 'indexFlashSales']);
    Route::get('/flash-sales/{id}', [DiscountController::class, 'showFlashSale']);
    Route::post('/flash-sales', [DiscountController::class, 'storeFlashSale']);
    Route::put('/flash-sales/{id}', [DiscountController::class, 'updateFlashSale']);
    Route::delete('/flash-sales/{id}', [DiscountController::class, 'destroyFlashSale']);
});

// Route dành riêng cho seller (chỉ seller mới vào được)
Route::prefix('seller/discounts')->middleware(['auth:sanctum', 'checkRole:seller'])->group(function () {
    Route::get('/', [DiscountSellerController::class, 'index']);
    
    // Route lấy danh sách sản phẩm của seller (phải đặt trước /{id})
    Route::get('/products', [DiscountSellerController::class, 'sellerProducts']);
    
    Route::get('/{id}', [DiscountSellerController::class, 'show']);
    Route::post('/', [DiscountSellerController::class, 'store']);
    Route::put('/{id}', [DiscountSellerController::class, 'update']);
    Route::delete('/{id}', [DiscountSellerController::class, 'destroy']);

    // Gán đối tượng cho ưu đãi
    Route::post('/{discountId}/products', [DiscountSellerController::class, 'assignProducts']);
    Route::post('/{discountId}/categories', [DiscountSellerController::class, 'assignCategories']);
    
    // Route kiểm tra discount cho shop
    Route::post('/check-shop-discount', [DiscountSellerController::class, 'checkShopDiscount']);
});
