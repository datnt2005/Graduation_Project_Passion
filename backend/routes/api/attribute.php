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
