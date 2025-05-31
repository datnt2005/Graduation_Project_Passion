<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Symfony\Component\Routing\Router;


// Api user login, otp, register, and resend otp
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('login', [AuthController::class, 'login']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);


// crud user
Route::apiResource('users', UserController::class);




