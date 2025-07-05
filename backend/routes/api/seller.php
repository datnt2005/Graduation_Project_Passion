<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SellerController;

Route::prefix('sellers')->group(function () {

    // 1. Đăng ký tài khoản seller — chỉ user đăng nhập mới được đăng ký
    Route::middleware(['auth:sanctum', 'checkRole:user'])->post('/register', [SellerController::class, 'register']);

    // 2. Seller và Admin có quyền xem/ cập nhật thông tin của chính mình
    Route::middleware(['auth:sanctum', 'checkRole:seller,admin'])->group(function () {
        Route::get('/seller/me', [SellerController::class, 'getMySellerInfo']);
        Route::post('/update', [SellerController::class, 'update']);
    });

    // 3. Admin có quyền quản lý danh sách seller
    Route::middleware(['auth:sanctum', 'checkRole:admin'])->get('/', [SellerController::class, 'index']);

    // 4. Route công khai – không cần đăng nhập
    Route::get('/store/{slug}', [SellerController::class, 'showStore']);
    Route::get('/verified', [SellerController::class, 'getVerifiedSellers']);

});

