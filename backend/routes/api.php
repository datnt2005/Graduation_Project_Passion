<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AttributeController;
use App\Models\Category;

// Category
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::get('/categories/{id}/children', [CategoryController::class, 'children']);
Route::get('/categories/{id}/parents', [CategoryController::class, 'parents']);
Route::post('/categories', [CategoryController::class, 'store']);
Route::put('/categories/{id}', [CategoryController::class, 'update']);
Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

//tags
Route::get('/tags', [\App\Http\Controllers\TagController::class, 'index']);
Route::get('/tags/{id}', [\App\Http\Controllers\TagController::class, 'show']);
Route::post('/tags', [\App\Http\Controllers\TagController::class, 'store']);
Route::put('/tags/{id}', [\App\Http\Controllers\TagController::class, 'update']);
Route::patch('/tags/{id}', [\App\Http\Controllers\TagController::class, 'update']);
Route::delete('/tags/{id}', [\App\Http\Controllers\TagController::class, 'destroy']);

//attributes
Route::get('/attributes', [AttributeController::class, 'index']);
Route::get('/attributes/{id}', [AttributeController::class, 'show']);
Route::post('/attributes', [AttributeController::class, 'store']);
Route::put('/attributes/{id}', [AttributeController::class, 'update']);
Route::delete('/attributes/{id}', [AttributeController::class, 'destroy']);

// Products
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::post('/', [ProductController::class, 'store']);
    Route::post('/import', [ProductController::class, 'import']);
    Route::put('/{id}', [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);
});