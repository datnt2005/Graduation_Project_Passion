<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

// CRUD user – Chỉ admin được phép
Route::middleware(['auth:sanctum', 'checkRole:admin'])->group(function () {
    Route::apiResource('users', UserController::class);

    Route::post('users/batch-delete', [UserController::class, 'batchDelete']);
    Route::post('users/batch-add-role', [UserController::class, 'batchAddRole']);
    Route::post('users/batch-remove-role', [UserController::class, 'batchRemoveRole']);

    // Cập nhật profile của người dùng khác – quyền admin
    Route::post('profile/update/{id}', [UserController::class, 'updateUser']);

});

Route::middleware(['auth:sanctum', 'checkRole:user'])->group(function () {
    Route::apiResource('users', UserController::class);


});

// Lấy danh sách theo vai trò – cho phép admin + seller
Route::middleware(['auth:sanctum', 'checkRole:admin,seller'])->get('/users/by-role/{role}', [UserController::class, 'getByRole']);
