<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;

// chatbot
Route::post('/chatbot', [ChatbotController::class, 'chat']);
