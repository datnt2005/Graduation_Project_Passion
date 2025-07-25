<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
Route::middleware(['auth:sanctum', 'checkRole:admin'])->group(function () {
    Route::prefix('posts')->group(function () {
        Route::get('/all', [PostController::class, 'getAllPost']);
    });
});
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{id}', [PostController::class, 'show']);
Route::get('/posts/slug/{slug}', [PostController::class, 'showBySlug']);

// Các hành động còn lại yêu cầu auth
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{id}', [PostController::class, 'update']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
});
