<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentMethodController;

// Payment Methods
Route::prefix('payment-methods')->group(function () {
    Route::get('/', [PaymentMethodController::class, 'index']);
    Route::get('/{id}', [PaymentMethodController::class, 'show']);
    Route::post('/', [PaymentMethodController::class, 'store']);
    Route::put('/{id}', [PaymentMethodController::class, 'update']);
    Route::delete('/{id}', [PaymentMethodController::class, 'destroy']);
});