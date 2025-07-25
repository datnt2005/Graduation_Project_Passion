<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FavoriteController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/favorites', [FavoriteController::class, 'list']);
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggle']);
});