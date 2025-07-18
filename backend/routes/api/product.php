<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductApprovalController;

Route::prefix('products')->group(function () {
    // ✅ Public routes (ai cũng xem được)
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/shop', [ProductController::class, 'getAllProducts']);
    Route::get('/search/{slug?}', [ProductController::class, 'getProducts']);
    Route::get('/category/{slug}', [ProductController::class, 'getProductBySlugCategory']);
    Route::get('/slug/{slug}', [ProductController::class, 'showBySlug']);

    // ⚠️ Route động phải có điều kiện whereNumber để không nuốt nhầm các route tĩnh như /trash
    Route::get('/{id}', [ProductController::class, 'show'])->whereNumber('id');

    // ✅ Seller/Admin: các route yêu cầu đăng nhập 
    Route::middleware('auth:sanctum')->group(function () {
        // Seller hoặc Admin đều có thể gọi
        Route::middleware('checkRole:admin,seller')->group(function () {
            Route::get('/sellers', [ProductController::class, 'getAllProductBySellers']);
            Route::get('/sellers/{id}', [ProductController::class, 'getProductsBySellerId'])->whereNumber('id');
            Route::get('/sellers/trash', [ProductController::class, 'getTrashBySeller']);
            Route::get('/trash', [ProductController::class, 'getTrash']);

            Route::post('/', [ProductController::class, 'store']);
            Route::post('/import', [ProductController::class, 'import']);
            Route::post('/change-status/{id}', [ProductController::class, 'changeStatus'])->whereNumber('id');
            Route::put('/{id}', [ProductController::class, 'update'])->whereNumber('id');
            Route::delete('/{id}', [ProductController::class, 'destroy'])->whereNumber('id');
        });
    });
});

// ✅ Route cho admin duyệt sản phẩm
Route::middleware(['auth:sanctum', 'checkRole:admin'])->group(function () {
    Route::prefix('approvals')->group(function () {
        Route::get('/', [ProductApprovalController::class, 'index']);
        Route::get('/rejected', [ProductApprovalController::class, 'getRejectedProducts']);
        Route::get('/history', [ProductApprovalController::class, 'getHistoryApproval']);
        Route::get('/{id}', [ProductApprovalController::class, 'getProductApprovalById'])->whereNumber('id');
        Route::post('/{id}', [ProductApprovalController::class, 'approveProduct'])->whereNumber('id');
    });
});
