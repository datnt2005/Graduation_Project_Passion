<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LogController;


Route::prefix('logs')->middleware(['auth:sanctum', 'checkRole:admin,seller'])->group(function () {
    Route::get('/ghn-sync', [LogController::class, 'ghnSyncLogs']);
});