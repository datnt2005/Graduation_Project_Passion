<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;



// Api user login, otp, register, and resend otp
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('login', [AuthController::class, 'login']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/resend-otp-by-email', [AuthController::class, 'resendOtpByEmail']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);
Route::post('/send-forgot-password', [AuthController::class, 'sendForgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

