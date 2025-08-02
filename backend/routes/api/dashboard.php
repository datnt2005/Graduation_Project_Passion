<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\SellerController;

// Dashboard stats
Route::middleware(['auth:sanctum', 'checkRole:admin'])
    ->prefix('dashboard')
    ->group(function () {
        Route::get('/stats', [DashboardController::class, 'stats']);
        Route::get('/stats-list', [DashboardController::class, 'statsList']);
        Route::get('/revenue-chart', [DashboardController::class, 'revenueChart']);
        Route::get('/revenue-profit-chart', [DashboardController::class, 'revenueProfitChart']);
        Route::get('/user-stats', [DashboardController::class, 'getUserStats']);
        
        // Thêm các routes mới
        Route::get('/sellers-stats', [DashboardController::class, 'getAllSellersStats']);
        Route::get('/orders-stats', [DashboardController::class, 'getAllOrdersStats']);
        Route::get('/users-stats', [DashboardController::class, 'getActiveUsersStats']);
        Route::get('/system-overview', [DashboardController::class, 'getSystemOverview']);
    });

// Admin routes
Route::middleware(['auth:sanctum', 'checkRole:admin'])
    ->prefix('admin')
    ->group(function () {
        // Orders
        Route::get('/orders', [OrderController::class, 'adminList']);
        Route::get('/orders/list', [OrderController::class, 'index']);
         
        // Sellers
        Route::get('/sellers', [SellerController::class, 'index']);
        Route::get('/sellers/stats', [SellerController::class, 'stats']);
        
        // Payouts
        Route::get('/payouts/approved', [PayoutController::class, 'approvedList']);
        Route::get('/payouts/stats', [PayoutController::class, 'stats']);
        Route::get('/payouts/chart', [PayoutController::class, 'chart']);
        

    });

// Orders routes
Route::middleware(['auth:sanctum'])
    ->group(function () {
        Route::get('/orders', [OrderController::class, 'index']);
    });

// Dashboard stats cho seller
Route::middleware(['auth:sanctum', 'checkRole:seller'])
    ->prefix('dashboard')
    ->group(function () {
        Route::get('/seller-stats-list', [DashboardController::class, 'sellerStatsList']);
        Route::get('/seller-revenue-profit-chart', [DashboardController::class, 'sellerRevenueProfitChart']);
    });

// Inventory routes cho seller
Route::middleware(['auth:sanctum', 'checkRole:seller'])
    ->prefix('inventory')
    ->group(function () {
        Route::get('/seller-list', [InventoryController::class, 'sellerList']);
        Route::get('/seller-best-sellers', [InventoryController::class, 'sellerBestSellers']);
    });

    