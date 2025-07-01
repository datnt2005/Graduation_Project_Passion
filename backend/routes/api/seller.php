<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SellerController;

Route::prefix('sellers')->group(function ()
{
    // lấy seller or business theo id
    Route::middleware('auth:sanctum')->get('/seller/me', [SellerController::class, 'getMySellerInfo']);

    // Route::get('/', [SellerController::class, 'index']);
    Route::post('/register', [SellerController::class, 'register'])->middleware('auth:sanctum');
    // Route::post('/login', [SellerController::class, 'login']);
    Route::get('/', [SellerController::class, 'index'])->middleware('auth:sanctum');
    Route::get('/store/{slug}', [SellerController::class, 'showStore']);
    Route::post('/update', [SellerController::class, 'update'])->middleware('auth:sanctum');
    Route::get('/verified', [SellerController::class, 'getVerifiedSellers']);
    
});

