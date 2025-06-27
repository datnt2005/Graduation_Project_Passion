<?php

use Illuminate\Support\Facades\Route;






foreach (glob(__DIR__.'/api/*.php') as $routeFile) {
    require $routeFile;
}


Route::get('inventory/list', [App\Http\Controllers\InventoryController::class, 'list']);
Route::get('inventory/low-stock', [App\Http\Controllers\InventoryController::class, 'lowStock']);
Route::get('inventory/best-sellers', [App\Http\Controllers\InventoryController::class, 'bestSellers']);

