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
Route::middleware('auth:sanctum')->get('/user-list', [UserController::class, 'getAllUsers']);


Route::middleware(['auth:sanctum', 'checkRole:user,admin'])->group(function () {
    Route::apiResource('users', UserController::class);
});

// Route gửi email cảnh báo khi người dùng từ chối nhận hàng
Route::middleware('auth:sanctum')->post('/send-warning-email', [UserController::class, 'sendWarningEmail']);

// Lấy danh sách theo vai trò – cho phép admin + seller
Route::middleware(['auth:sanctum', 'checkRole:admin,seller'])->get('/users/by-role/{role}', [UserController::class, 'getByRole']);
