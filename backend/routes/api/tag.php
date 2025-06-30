<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagController;

//tags
Route::prefix('tags')->group(function () {
    Route::get('/', [TagController::class, 'index']);
    Route::get('/{id}', [TagController::class, 'show']);
    Route::post('/', [TagController::class, 'store']);
    Route::put('/{id}', [TagController::class, 'update']);
    Route::patch('/{id}', [TagController::class, 'update']);
    Route::delete('/{id}', [TagController::class, 'destroy']);
});

