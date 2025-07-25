<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;


// Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('chat')->group(function () {
        Route::post('/session', [ChatController::class, 'createSession']);
        Route::get('/sessions', [ChatController::class, 'getSessions']);
        Route::post('/message', [ChatController::class, 'sendMessage']);
        Route::get('/messages/{sessionId}', [ChatController::class, 'getMessages']);
        Route::post('/messages/{sessionId}/read', [ChatController::class, 'markAsRead']);
    });
// });