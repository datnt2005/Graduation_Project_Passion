<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WithdrawRequestController;

Route::middleware(['auth:sanctum', 'checkRole:seller'])->group(function () {
    // Lịch sử rút tiền
    Route::get('withdraw-requests', [WithdrawRequestController::class, 'index']);
    // Gửi yêu cầu rút tiền
    Route::post('withdraw-requests', [WithdrawRequestController::class, 'store']);
    // Lấy số dư khả dụng
    Route::get('withdraw-available', [WithdrawRequestController::class, 'getAvailableBalance']);
});

// Route cho admin duyệt và xem danh sách yêu cầu rút tiền
Route::middleware(['auth:sanctum', 'checkRole:admin'])->group(function () {
    // Xem tất cả yêu cầu rút tiền
    Route::get('admin/withdraw-requests', [\App\Http\Controllers\WithdrawRequestController::class, 'adminIndex']);
    // Duyệt yêu cầu rút tiền
    Route::post('admin/withdraw-requests/{id}/approve', [\App\Http\Controllers\WithdrawRequestController::class, 'approve']);
    // Từ chối yêu cầu rút tiền
    Route::post('admin/withdraw-requests/{id}/reject', [\App\Http\Controllers\WithdrawRequestController::class, 'reject']);
}); 