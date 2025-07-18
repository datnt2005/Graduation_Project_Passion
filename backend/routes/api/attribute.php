<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttributeController;

//attributes
Route::prefix('attributes')->group(function () {
    // ✅ Public routes (ai cũng xem được)
    Route::get('/', [AttributeController::class, 'index']);
    Route::get('/{id}', [AttributeController::class, 'show']);
    Route::get('/slug/{slug}', [AttributeController::class, 'showBySlug']);

    //Admin: các route yêu cầu đăng nhập 
    Route::middleware('auth:sanctum')->group(function () {
        //  Admin đều có thể gọi
        Route::middleware('checkRole:admin')->group(function () {
            Route::put('/{id}', [AttributeController::class, 'update']);
            Route::delete('/{id}', [AttributeController::class, 'destroy']);
        });
        Route::middleware('checkRole:admin,seller')->group(function () {
            Route::post('/', [AttributeController::class, 'store']);
        });
    });
});
