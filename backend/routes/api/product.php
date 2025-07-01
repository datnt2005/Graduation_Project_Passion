<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

// Products
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/sellers', [ProductController::class, 'getAllProductBySellers'])->middleware('auth:sanctum');
    Route::get('/trash', [ProductController::class, 'getTrash']);
    Route::get('/shop', [ProductController::class, 'getAllProducts']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::post('/', [ProductController::class, 'store'])->middleware('auth:sanctum');
    Route::post('/import', [ProductController::class, 'import']);
    Route::put('/{id}', [ProductController::class, 'update'])->middleware('auth:sanctum');
    Route::delete('/{id}', [ProductController::class, 'destroy']);
    Route::get('/slug/{slug}', [ProductController::class, 'showBySlug']);
    Route::post('/change-status/{id}', [ProductController::class, 'changeStatus'])->middleware('auth:sanctum');
    Route::get('/category/{slug}', [ProductController::class, 'getProductBySlugCategory']);
    Route::get('/search/{slug?}', [ProductController::class, 'getProducts']);
    Route::get('/sellers/trash', [ProductController::class, 'getTrashBySeller'])->middleware('auth:sanctum');
});