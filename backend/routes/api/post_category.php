<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostCategoryController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/post-categories', [PostCategoryController::class, 'index']);
    Route::get('/post-categories/{id}', [PostCategoryController::class, 'show']);
    Route::post('/post-categories', [PostCategoryController::class, 'store']);
    Route::put('/post-categories/{id}', [PostCategoryController::class, 'update']);
    Route::delete('/post-categories/{id}', [PostCategoryController::class, 'destroy']);
});