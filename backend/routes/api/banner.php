<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BannerController;

// route công khai
Route::get('/banners', [BannerController::class, 'index']);
Route::get('/banners/popups', [BannerController::class, 'getActivePopups']);
Route::get('/banners/{id}', [BannerController::class, 'show']);

// route dành cho user đã đăng nhập
Route::middleware(['auth:sanctum', 'checkRole:user,seller,admin'])->group(function () {
    Route::post('/banners', [BannerController::class, 'store']);
    Route::put('/banners/{id}', [BannerController::class, 'update']);
    Route::delete('/banners/{id}', [BannerController::class, 'destroy']);
});

// route dành cho admin
Route::prefix('admin/banners')
    ->middleware(['auth:sanctum', 'checkRole:admin'])
    ->group(function () {
        Route::get('/', [BannerController::class, 'index']);
        Route::get('/{id}', [BannerController::class, 'show']);
        Route::post('/', [BannerController::class, 'store']);
        Route::put('/{id}', [BannerController::class, 'update']);
        Route::delete('/{id}', [BannerController::class, 'destroy']);
    });