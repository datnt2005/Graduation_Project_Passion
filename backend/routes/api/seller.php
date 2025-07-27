<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SellerController;

Route::prefix('sellers')->group(function () {
    // Đăng ký tài khoản seller — chỉ cho user đã đăng nhập
    Route::middleware(['auth:sanctum'])->prefix('register')->group(function () {
        Route::post('/full', [SellerController::class, 'registerFull']);
    });

    // 2. Seller và Admin có quyền xem/ cập nhật thông tin của chính mình
    Route::middleware(['auth:sanctum', 'checkRole:seller,admin'])->group(function () {
        Route::get('/me', [SellerController::class, 'getMySellerInfo']);
        Route::post('/update', [SellerController::class, 'update']);
    });

    // 3. Admin có quyền quản lý danh sách seller
    Route::middleware(['auth:sanctum', 'checkRole:admin'])->get('/', [SellerController::class, 'index']);

    // 4. Route công khai – không cần đăng nhập
    Route::get('/store/{slug}', [SellerController::class, 'showStore']);
    Route::get('/store/{slug}/deals', [SellerController::class, 'getDeals']);
    // Route::get('/store/{slug}/discounts', [SellerController::class, 'getDiscounts']);
    Route::get('/verified', [SellerController::class, 'getVerifiedSellers']);

    Route::get('/{seller_id}', [SellerController::class, 'show']);
});

    Route::get('/brands', [SellerController::class, 'getBrands']);