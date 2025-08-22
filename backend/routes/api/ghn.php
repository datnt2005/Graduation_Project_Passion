<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GHNController;

// GHN
Route::get('/ghn/provinces', [GHNController::class, 'getProvinces']);
Route::get('/ghn/districts', [GHNController::class, 'getDistricts']);
Route::get('/ghn/wards', [GHNController::class, 'getWards']);
Route::post('/ghn/districts', [GHNController::class, 'getDistricts']);
Route::post('/ghn/wards', [GHNController::class, 'getWards']);
Route::post('ghn/shipping-fee', [GHNController::class, 'calculateFee']);
Route::post('ghn/shipping-fee-with-discount', [GHNController::class, 'calculateFeeWithDiscount']);
Route::post('/ghn/services', [GHNController::class, 'getServices']);