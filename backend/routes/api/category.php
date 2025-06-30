<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;


// Category
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/parents', [CategoryController::class, 'showAllCategoryParent']);
    Route::get('/tree', [CategoryController::class, 'getCategoryTree']);
    Route::get('/{id}', [CategoryController::class, 'show']);
    Route::get('/{id}/children', [CategoryController::class, 'children']);
    Route::get('/{id}/parents', [CategoryController::class, 'parents']);
    Route::post('/', [CategoryController::class, 'store']);
    Route::put('/{id}', [CategoryController::class, 'update']);
    Route::delete('/{id}', [CategoryController::class, 'destroy']);
});
