<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

Route::prefix('chat')->group(function () {
    Route::post('/send-message', [ChatController::class, 'sendMessage']);
    Route::get('/messages/{sessionId}', [ChatController::class, 'getMessages']);
    Route::get('/sessions', [ChatController::class, 'getSessions']);
    Route::put('/messages/{id}/action', [ChatController::class, 'messageAction']);
});