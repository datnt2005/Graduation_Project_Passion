<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReviewController;

// Reviews

Route::get('/reviews', [ReviewController::class, 'index']);        // ?product_id=...
Route::post('/reviews', [ReviewController::class, 'store']);           // Gửi đánh giá
Route::put('/reviews/{id}', [ReviewController::class, 'update']);     // Cập nhật đánh giá
Route::post('/reviews/{id}/like', [ReviewController::class, 'like']);  // Like đánh giá
Route::post('/reviews/{id}/reply', [ReviewController::class, 'reply']); // Trả lời đánh giá
Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);   // Xóa đánh giá
Route::get('/reviews', [ReviewController::class, 'index']); // Hiển thị đánh giá công khai

Route::prefix('admin/reviews')->group(function () {
    Route::get('/', [ReviewController::class, 'adminIndex']);
    Route::get('/{id}', [ReviewController::class, 'adminShow']);
    Route::put('/{id}', [ReviewController::class, 'adminUpdate']);
    Route::delete('/{id}', [ReviewController::class, 'adminDestroy']);
});



// Các route yêu cầu đăng nhập
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/reviews', [ReviewController::class, 'store']);               // Gửi đánh giá
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);          // Cập nhật đánh giá
    Route::post('/reviews/{id}/like', [ReviewController::class, 'like']);      // Like đánh giá
    Route::get('/reviews/{id}/liked', [ReviewController::class, 'checkLiked']);
    Route::post('/reviews/{id}/unlike', [ReviewController::class, 'unlike']);  // Unlike đánh giá
    Route::post('/reviews/{id}/reply', [ReviewController::class, 'reply']);    // Trả lời đánh giá
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);      // Xóa đánh giá

});
