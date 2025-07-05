<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;

// notification
Route::prefix('notifications')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::post('/', [NotificationController::class, 'store']);
    Route::get('/{id}', [NotificationController::class, 'show']);
    Route::put('/{id}', [NotificationController::class, 'update']);
    Route::post('/mark-read', [NotificationController::class, 'markAsRead']);
    Route::delete('/{id}', [NotificationController::class, 'destroy']);
    Route::post('/send-multiple', [NotificationController::class, 'sendMultiple']);
    Route::post('/destroy-multiple', [NotificationController::class, 'destroyMultiple']);
    Route::delete('/destroy-all', [NotificationController::class, 'destroyAll']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my-notifications', [NotificationController::class, 'getMyNotifications']);

    // ✅ Đánh dấu 1 thông báo là đã đọc
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    // ✅ Đánh dấu nhiều thông báo là đã đọc
    Route::post('/notifications/mark-multiple-read', [NotificationController::class, 'markMultipleAsRead']);

    // ✅ Đánh dấu tất cả thông báo là đã đọc
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);

    // ✅ Ẩn (không xóa thật) nhiều thông báo
    Route::post('/notifications/delete-multiple', [NotificationController::class, 'deleteMultiple']);

    // ✅ Ẩn tất cả thông báo
    Route::delete('/notifications/delete-all', [NotificationController::class, 'deleteAll']);
});
