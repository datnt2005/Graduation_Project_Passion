<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductVariantController;

Route::middleware(['auth:sanctum', 'checkRole:admin,seller'])->get('/product-variants', [ProductVariantController::class, 'index']);
