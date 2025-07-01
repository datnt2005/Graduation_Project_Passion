<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SellerFollowerController;


// Seller Follower
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/sellers/{id}/follow', [SellerFollowerController::class, 'follow']);
    Route::post('/sellers/{id}/unfollow', [SellerFollowerController::class, 'unfollow']);
    Route::get('/my-followed-sellers', [SellerFollowerController::class, 'myFollows']);
    Route::get('/sellers/{id}/followers', [SellerFollowerController::class, 'followersOfSeller']);
});


