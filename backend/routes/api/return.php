<?php

use App\Http\Controllers\ReturnController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/returns', [ReturnController::class, 'store']);
    Route::get('/returns/my', [ReturnController::class, 'myRequests']);

});

Route::middleware(['auth:sanctum', 'checkRole:admin'])->group(function () {
    Route::get('/admin/returns', [ReturnController::class, 'index']);
        Route::put('/admin/returns/{returnRequest}', [ReturnController::class, 'update']);
        });

