<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SellerCustomerController;


Route::middleware(['auth:sanctum'])
    ->prefix('seller')
    ->group(function () {
        Route::get('/customers', [SellerCustomerController::class, 'index']);
    });
