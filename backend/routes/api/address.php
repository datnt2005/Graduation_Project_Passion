<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AddressController;


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/address', [AddressController::class, 'index']);
    Route::get('/address/{id}', [AddressController::class, 'show']);
    Route::post('/address', [AddressController::class, 'store']);
    Route::put('/address/{id}', [AddressController::class, 'update']);
    Route::delete('/address/{id}', [AddressController::class, 'destroy']);
});


