<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

// ✅ Người dùng gửi báo cáo (user, seller, admin)
Route::middleware(['auth:sanctum', 'checkRole:user,seller,admin'])->post('/reports', [ReportController::class, 'store']);

// ✅ Quản trị viên xử lý tất cả các loại báo cáo (review, post_comment, ...)
Route::prefix('admin/reports')
    ->middleware(['auth:sanctum', 'checkRole:admin'])
    ->group(function () {
        Route::get('/', [ReportController::class, 'index']); // Lấy danh sách
        Route::get('/reviews', [ReportController::class, 'adminIndex']);
        Route::get('/reviews/{id}', [ReportController::class, 'adminShow']);
        Route::put('/reviews/{id}/status', [ReportController::class, 'adminUpdateStatus']);
        Route::get('/products', [ReportController::class, 'getReportProduct']); // Lấy danh sách sản phẩm
        Route::get('/products/{id}', [ReportController::class, 'getReportProductById']); // Lấy danh sách sản phẩm theo id
        Route::delete('/products/{id}', [ReportController::class, 'destroy']); 
        Route::get('/{id}', [ReportController::class, 'show']); // Xem chi tiết
        Route::put('/{id}/status', [ReportController::class, 'updateStatus']); // Cập nhật trạng thái
    });

// ✅ Seller xử lý báo cáo liên quan đến sản phẩm của họ (review)
Route::prefix('seller/reports/reviews')
    ->middleware(['auth:sanctum', 'checkRole:seller'])
    ->group(function () {
        Route::get('/', [ReportController::class, 'sellerIndex']);
        Route::get('/{id}', [ReportController::class, 'sellerShow']);
        Route::put('/{id}/status', [ReportController::class, 'sellerUpdateStatus']);
    });


