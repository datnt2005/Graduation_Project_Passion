<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;

// notification
Route::prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::post('/', [NotificationController::class, 'store']);
    Route::get('/{id}', [NotificationController::class, 'show']);
    Route::put('/{id}', [NotificationController::class, 'update']);
    Route::post('/mark-read', [NotificationController::class, 'markAsRead']);
    Route::delete('/{id}', [NotificationController::class, 'destroy']);
    Route::post('/send-multiple', [NotificationController::class, 'sendMultiple']);
});

