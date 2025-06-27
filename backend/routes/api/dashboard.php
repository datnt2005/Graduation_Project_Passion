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
