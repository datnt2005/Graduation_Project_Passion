<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

// Products
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/trash', [ProductController::class, 'getTrash']);
    Route::get('/shop', [ProductController::class, 'getAllProducts']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::post('/', [ProductController::class, 'store']);
    Route::post('/import', [ProductController::class, 'import']);
    Route::put('/{id}', [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);
    Route::get('/slug/{slug}', [ProductController::class, 'showBySlug']);
    Route::post('/change-status/{id}', [ProductController::class, 'changeStatus']);
    Route::get('/category/{slug}', [ProductController::class, 'getProductBySlugCategory']);
    Route::get('/search/{slug?}', [ProductController::class, 'getProducts']);
});
