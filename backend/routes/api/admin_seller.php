<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminSellerController;

Route::prefix('admin')->group(function () {
   Route::get('/sellers', [AdminSellerController::class, 'index']);
   Route::get('/sellers/{id}', [AdminSellerController::class, 'show']);
   Route::post('/sellers/{id}/verify', [AdminSellerController::class, 'verify']);
   Route::post('/sellers/{id}/reject', [AdminSellerController::class, 'reject']);
});
