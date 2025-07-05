<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;


Route::middleware(['auth:sanctum', 'checkRole:seller,admin'])->post('/reports', [ReportController::class, 'store']);

// Quản trị viên xử lý báo cáo
Route::prefix('admin/reports/reviews')
    ->middleware(['auth:sanctum', 'checkRole:admin'])
    ->group(function () {
        Route::get('/', [ReportController::class, 'index']);
        Route::get('/{id}', [ReportController::class, 'show']);
        Route::put('/{id}/status', [ReportController::class, 'updateStatus']);
    });


Route::prefix('seller/reports/reviews')
    ->middleware(['auth:sanctum', 'checkRole:seller'])
    ->group(function () {
        Route::get('/', [ReportController::class, 'sellerIndex']);
        Route::get('/{id}', [ReportController::class, 'sellerShow']);
        Route::put('/{id}/status', [ReportController::class, 'sellerUpdateStatus']); // ✅ THÊM DÒNG NÀY
    });

