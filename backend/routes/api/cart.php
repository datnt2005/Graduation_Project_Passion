<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;

// Cart Management
Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index']);
    Route::post('/add', [CartController::class, 'addItem']);
    Route::put('/items/{id}', [CartController::class, 'updateItem']);
    Route::delete('/items/{id}', [CartController::class, 'removeItem']);
    Route::delete('/', [CartController::class, 'clear']);

    // Redis Cart Routes
    Route::get('/redis/{cartId}', [CartController::class, 'getRedisCart']);
    Route::post('/redis/{cartId}/add', [CartController::class, 'addToRedisCart']);
    Route::put('/redis/{cartId}/items/{itemId}', [CartController::class, 'updateRedisCartItem']);
    Route::delete('/redis/{cartId}/items/{itemId}', [CartController::class, 'removeRedisCartItem']);
    Route::delete('/redis/{cartId}', [CartController::class, 'clearRedisCart']);
    Route::post('/redis/{cartId}/merge', [CartController::class, 'mergeRedisCart'])->middleware('auth:sanctum');
});
