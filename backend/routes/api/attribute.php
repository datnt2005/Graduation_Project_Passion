<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttributeController;

//attributes
Route::prefix('attributes')->group(function () {
    Route::get('/', [AttributeController::class, 'index']);
    Route::get('/{id}', [AttributeController::class, 'show']);
    Route::post('/', [AttributeController::class, 'store']);
    Route::put('/{id}', [AttributeController::class, 'update']);
    Route::delete('/{id}', [AttributeController::class, 'destroy']);
    Route::get('/slug/{slug}', [AttributeController::class, 'showBySlug']);
});


// Category
// Route::prefix('categories')->group(function () {
//     // ✅ Public routes (ai cũng xem được)
//     Route::get('/', [CategoryController::class, 'index']);
//     Route::get('/parents', [CategoryController::class, 'showAllCategoryParent']);
//     Route::get('/tree', [CategoryController::class, 'getCategoryTree']);
//     Route::get('/{id}', action: [CategoryController::class, 'show']);
//     Route::get('/{id}/children', [CategoryController::class, 'children']);
//     Route::get('/{id}/parents', [CategoryController::class, 'parents']);

//     //Admin: các route yêu cầu đăng nhập 
//     Route::middleware('auth:sanctum')->group(function () {
//         //  Admin đều có thể gọi
//         Route::middleware('checkRole:admin')->group(function () {
//             Route::post('/', [CategoryController::class, 'store']);
//             Route::put('/{id}', [CategoryController::class, 'update']);
//             Route::delete('/{id}', [CategoryController::class, 'destroy']);
//         });
//     });
// });
