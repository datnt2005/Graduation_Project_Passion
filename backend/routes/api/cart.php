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
    Route::post('/select-items', [CartController::class, 'selectItems']);
    Route::get('/selected-items', [CartController::class, 'getSelectedItems']);
});
