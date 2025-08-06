<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReviewController;

// Public
Route::get('/reviews', [ReviewController::class, 'index']); // Hiển thị đánh giá công khai

// Các route dành cho user đã đăng nhập
Route::middleware(['auth:sanctum', 'checkRole:user,seller,admin'])->group(function () {
    Route::post('/reviews', [ReviewController::class, 'store']);               // Gửi đánh giá
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);          // Cập nhật đánh giá
    Route::post('/reviews/{id}/like', [ReviewController::class, 'like']);      // Like đánh giá
    Route::get('/reviews/{id}/liked', [ReviewController::class, 'checkLiked']); // Kiểm tra đã like
    Route::post('/reviews/{id}/unlike', [ReviewController::class, 'unlike']);  // Unlike đánh giá
    Route::post('/reviews/{id}/reply', [ReviewController::class, 'reply']);    // Trả lời đánh giá
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);      // Xóa đánh giá cá nhân
});


Route::prefix('seller/reviews')
    ->middleware(['auth:sanctum', 'checkRole:seller'])
    ->group(function () {
        Route::get('/', [ReviewController::class, 'sellerIndex']);
        Route::get('/counts', [ReviewController::class, 'sellerReviewCounts']); // Đặt trước
        Route::get('/{id}', [ReviewController::class, 'sellerShow']);
        Route::put('/{id}', [ReviewController::class, 'sellerUpdate']);
        Route::delete('/{id}', [ReviewController::class, 'sellerDestroy']);
    });
