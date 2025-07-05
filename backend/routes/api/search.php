<?php

use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::post('/search/add', [SearchController::class, 'addSearch']);
Route::get('/search/suggestions', [SearchController::class, 'getSuggestions']);
Route::delete('/search/history', [SearchController::class, 'deleteHistory']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/search/sync-history', [SearchController::class, 'syncHistory']);
});