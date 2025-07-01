<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserOrderController;


// user Orrderss
Route::middleware('auth:sanctum')->prefix('user/orders')->controller(UserOrderController::class)->group(function () {
    Route::get('/', 'index');
    Route::post('{order}/cancel', 'cancel');
    Route::post('{order}/reorder', 'reorder');
    Route::get('/{order}', 'show');
});


