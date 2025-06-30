<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

// Payments
Route::prefix('payments')->group(function () {
    Route::get('/', [PaymentController::class, 'index']);
    Route::get('/{id}', [PaymentController::class, 'show']);
    Route::post('/', [PaymentController::class, 'store']);
    Route::put('/{id}', [PaymentController::class, 'update']);
    Route::delete('/{id}', [PaymentController::class, 'destroy']);

    // VNPAY routes
    Route::post('/vnpay/create', [PaymentController::class, 'createVNPayPayment']);
    Route::match(['get', 'post'], '/vnpay/return', [PaymentController::class, 'vnpayReturn']);
    // MOMO routes
    Route::post('/momo/create', [PaymentController::class, 'createMoMoPayment']);
    Route::match(['get', 'post'], '/momo/return', [PaymentController::class, 'momoReturn']);
    Route::post('/momo/ipn', [PaymentController::class, 'momoIPN']);
});
