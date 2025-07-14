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
    Route::post('/send-all', [NotificationController::class, 'sendAll']);
    Route::post('/destroy-multiple', [NotificationController::class, 'destroyMultiple']);
    Route::delete('/destroy-all', [NotificationController::class, 'destroyAll']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my-notifications', [NotificationController::class, 'getMyNotifications']);
});

Route::prefix('seller/notifications')
    ->middleware(['auth:sanctum', 'checkRole:seller'])
    ->group(function () {
        Route::get('/my', [NotificationController::class, 'getMyNotifications']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/mark-multiple-read', [NotificationController::class, 'markMultipleAsRead']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::post('/delete-multiple', [NotificationController::class, 'deleteMultiple']);
        Route::delete('/delete-all', [NotificationController::class, 'deleteAll']);
    });

Route::prefix('admin/notifications')
    ->middleware(['auth:sanctum', 'checkRole:admin'])
    ->group(function () {
        Route::get('/my', [NotificationController::class, 'getMyNotifications']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/mark-multiple-read', [NotificationController::class, 'markMultipleAsRead']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::post('/delete-multiple', [NotificationController::class, 'deleteMultiple']);
        Route::delete('/delete-all', [NotificationController::class, 'deleteAll']);
    });