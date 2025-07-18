<?php

use App\Http\Controllers\ReturnController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/returns', [ReturnController::class, 'store']);
    Route::get('/returns/my', [ReturnController::class, 'myRequests']);
    Route::get('/return-requests/check/{orderItemId}', [ReturnController::class, 'check']);
});

Route::middleware(['auth:sanctum', 'checkRole:seller'])->group(function () {
    Route::get('/seller/returns', [ReturnController::class, 'index']);
    Route::put('/seller/returns/{returnRequest}', [ReturnController::class, 'update']);
});
