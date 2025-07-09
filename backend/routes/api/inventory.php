<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\InventoryController;

Route::get('inventory/list', [App\Http\Controllers\InventoryController::class, 'list']);
Route::get('inventory/low-stock', [App\Http\Controllers\InventoryController::class, 'lowStock']);
Route::get('inventory/best-sellers', [App\Http\Controllers\InventoryController::class, 'bestSellers']);


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('inventory/list', [App\Http\Controllers\InventoryController::class, 'list']);
    Route::get('inventory/low-stock', [App\Http\Controllers\InventoryController::class, 'lowStock']);
    Route::get('inventory/best-sellers', [App\Http\Controllers\InventoryController::class, 'bestSellers']);

    Route::get('/payouts', [PayoutController::class, 'index']);
    Route::post('/payouts', [PayoutController::class, 'store']);
    Route::get('/payouts/{id}', [PayoutController::class, 'show']);
    Route::put('/payouts/{id}', [PayoutController::class, 'update']);


});


Route::middleware(['auth:sanctum', 'checkRole:admin,seller'])->group(function () {
    Route::get('/stock-movements', [InventoryController::class, 'stockHistory']);
    Route::post('/inventories/{inventory}/damage', [InventoryController::class, 'markDamaged']);

    Route::post('/inventories/import', [InventoryController::class, 'import']);
    Route::put('/inventories/{inventory}', [InventoryController::class, 'update']);
});


Route::middleware(['auth:sanctum', 'checkRole:admin,seller'])
->get('/payout/list-approved', [PayoutController::class, 'listApproved']);
