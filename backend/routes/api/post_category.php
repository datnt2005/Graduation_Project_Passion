<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostCategoryController;

Route::get('/post-categories', [PostCategoryController::class, 'index']);
Route::get('/post-categories/{id}', [PostCategoryController::class, 'show']);

// Các hành động yêu cầu xác thực
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/post-categories', [PostCategoryController::class, 'store']);
    Route::put('/post-categories/{id}', [PostCategoryController::class, 'update']);
    Route::delete('/post-categories/{id}', [PostCategoryController::class, 'destroy']);
});