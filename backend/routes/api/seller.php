<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SellerController;


Route::prefix('sellers')->group(function () {

    Route::middleware(['auth:sanctum', 'checkRole:user'])->group(function () {
        Route::post('/register', [SellerController::class, 'register']);
    });

    Route::middleware(['auth:sanctum', 'checkRole:seller,admin'])->group(function () {
        Route::get('/seller/me', [SellerController::class, 'getMySellerInfo']);
        Route::post('/update', [SellerController::class, 'update']);
    });

    Route::middleware(['auth:sanctum', 'checkRole:admin'])->group(function () {
        Route::get('/', [SellerController::class, 'index']);
    });

    Route::get('/store/{slug}', [SellerController::class, 'showStore']);
    Route::get('/verified', [SellerController::class, 'getVerifiedSellers']);
});


