<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductApprovalController;

Route::middleware(['auth:sanctum', 'checkRole:admin'])->group(function () {
    Route::prefix('approvals')->group(function () {
        Route::get('/', [ProductApprovalController::class, 'index']);
        Route::get('/rejected', [ProductApprovalController::class, 'getRejectedProducts']);
        Route::get('/history', [ProductApprovalController::class, 'getHistoryApproval']);
        Route::get('/{id}', [ProductApprovalController::class, 'getProductApprovalById']);
        Route::post('/{id}', [ProductApprovalController::class, 'approveProduct']);
    });
});


Route::prefix('products')->group(function () {
    // ✅ Public routes (ai cũng xem được)
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/shop', [ProductController::class, 'getAllProducts']);
    Route::get('/slug/{slug}', [ProductController::class, 'showBySlug']);
    Route::get('/category/{slug}', [ProductController::class, 'getProductBySlugCategory']);
    Route::get('/search/{slug?}', [ProductController::class, 'getProducts']);
    Route::get('/{id}', [ProductController::class, 'show']);

    // ✅ Seller/Admin: các route yêu cầu đăng nhập 
    Route::middleware('auth:sanctum')->group(function () {
        // Seller hoặc Admin đều có thể gọi
        Route::middleware('checkRole:admin,seller')->group(function () {
            Route::get('/sellers', [ProductController::class, 'getAllProductBySellers']);
            Route::get('/trash', [ProductController::class, 'getTrash']);
            Route::post('/', [ProductController::class, 'store']);
            Route::post('/import', [ProductController::class, 'import']);
            Route::put('/{id}', [ProductController::class, 'update']);
            Route::delete('/{id}', [ProductController::class, 'destroy']);
            Route::get('/sellers/{id}', [ProductController::class, 'getProductsBySellerId']);
            Route::post('/change-status/{id}', [ProductController::class, 'changeStatus']);
            Route::get('/sellers/trash', [ProductController::class, 'getTrashBySeller']);
        });
    });
});
