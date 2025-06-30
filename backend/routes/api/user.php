<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

// crud user
Route::apiResource('users', UserController::class);
Route::post('users/batch-delete', [UserController::class, 'batchDelete']);
Route::post('users/batch-add-role', [UserController::class, 'batchAddRole']);
Route::post('users/batch-remove-role', [UserController::class, 'batchRemoveRole']);
Route::get('/users/by-role/{role}', [UserController::class, 'getByRole']);
Route::post('profile/update/{id}', [UserController::class, 'updateUser']);

Route::apiResource('users', UserController::class);
