<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SettingController;

Route::prefix('settings')->group(function () {
    // Các route không yêu cầu xác thực
    Route::get('/', [SettingController::class, 'index']);

    // Các route yêu cầu xác thực và quyền admin
    Route::middleware(['auth:sanctum', 'checkRole:admin'])->group(function () {
        Route::post('/update', [SettingController::class, 'update']);
        Route::post('/upload', [SettingController::class, 'upload']);
        Route::get('/backup', [SettingController::class, 'backup']);
        Route::post('/restore', [SettingController::class, 'restore']);
        Route::delete('/{key}', [SettingController::class, 'destroy']);
    });
});