<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RefundController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/refunds', [RefundController::class, 'index']);
    Route::get('/refunds/{id}', [RefundController::class, 'show']);
    Route::post('/refunds', [RefundController::class, 'store']);
    Route::put('/refunds/{id}', [RefundController::class, 'update']);
    Route::delete('/refunds/{id}', [RefundController::class, 'destroy']);
    Route::get('/orders/{id}/refund', [RefundController::class, 'getByOrderId']);
    Route::post('/orders/{id}/refund', [RefundController::class, 'process']);
    
    // Route mới cho duyệt và từ chối yêu cầu hoàn tiền
    Route::post('/refunds/{id}/approve', [RefundController::class, 'approve']);
    Route::post('/refunds/{id}/reject', [RefundController::class, 'reject']);
});