<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupportController;

// Route công khai cho user gửi hỗ trợ và admin xem danh sách

Route::post('/supports', [SupportController::class, 'store']);

// Các route dành cho admin (nếu cần phân quyền riêng, giữ nguyên middleware)
Route::prefix('admin/supports')
    ->middleware(['auth:sanctum', 'checkRole:admin'])
    ->group(function () {
        Route::get('/supports', [SupportController::class, 'index']);
        Route::get('/supports/{id}', [SupportController::class, 'show']);
        Route::post('/{id}/reply', [SupportController::class, 'reply']);
        Route::get('/', [SupportController::class, 'index']);
        Route::get('/{id}', [SupportController::class, 'show']);
        Route::post('/', [SupportController::class, 'store']);
        Route::put('/{id}', [SupportController::class, 'update']);
        Route::delete('/{id}', [SupportController::class, 'destroy']);
    });
