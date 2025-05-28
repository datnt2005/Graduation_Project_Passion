<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;


// Api user login, otp, register, and resend otp
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('login', [AuthController::class, 'login']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);

// crud user
Route::apiResource('users', UserController::class);




