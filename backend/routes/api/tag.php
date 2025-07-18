<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagController;


//tags
Route::prefix('tags')->group(function () {
    // ✅ Public routes (ai cũng xem được)
    Route::get('/', [TagController::class, 'index']);
    Route::get('/{id}', [TagController::class, 'show']);
    Route::post('/', [TagController::class, 'store']);
    Route::put('/{id}', [TagController::class, 'update']);
    Route::patch('/{id}', [TagController::class, 'update']);
    Route::delete('/{id}', [TagController::class, 'destroy']);

Route::get('/{slug}/products', [TagController::class, 'productsBySlug']);

});

    //Admin: các route yêu cầu đăng nhập 
    Route::middleware('auth:sanctum')->group(function () {
        //  Admin đều có thể gọi
        Route::middleware('checkRole:admin')->group(function () {
            Route::post('/', [TagController::class, 'store']);
            Route::put('/{id}', [TagController::class, 'update']);
            Route::patch('/{id}', [TagController::class, 'update']);
            Route::delete('/{id}', [TagController::class, 'destroy']);
        });
    });
});
