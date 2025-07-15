<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SettingController;


Route::middleware(['auth:sanctum', 'isAdmin'])->prefix('/settings')->group(function () {
    Route::get('/', [SettingController::class, 'index']);
    Route::post('/update', [SettingController::class, 'update']);
    Route::post('/upload', [SettingController::class, 'upload']);
    Route::get('/backup', [SettingController::class, 'backup']);
    Route::post('/restore', [SettingController::class, 'restore']);
});
