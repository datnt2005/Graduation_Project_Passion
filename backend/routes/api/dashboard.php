<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;



// Dashboard stats
Route::middleware(['auth:sanctum', 'checkRole:admin'])
    ->prefix('dashboard')
    ->group(function () {
        Route::get('/stats', [DashboardController::class, 'stats']);
        Route::get('/stats-list', [DashboardController::class, 'statsList']);
        Route::get('/revenue-chart', [DashboardController::class, 'revenueChart']);
        Route::get('/revenue-profit-chart', [DashboardController::class, 'revenueProfitChart']);
    });

// Dashboard stats cho seller
Route::middleware(['auth:sanctum', 'checkRole:seller'])
    ->prefix('dashboard')
    ->group(function () {
        Route::get('/seller-stats-list', [DashboardController::class, 'sellerStatsList']);
        Route::get('/seller-revenue-profit-chart', [DashboardController::class, 'sellerRevenueProfitChart']);
    });

    