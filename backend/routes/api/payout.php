
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PayoutController;


Route::middleware(['auth:sanctum'])->prefix('seller')->group(function () {
    Route::get('/payout/list-approved', [PayoutController::class, 'approvedList']); // Đúng tên hàm
    Route::get('/payout/{id}', [PayoutController::class, 'show']);
    Route::post('/payouts', [PayoutController::class, 'store']);
    Route::put('/payouts/{id}', [PayoutController::class, 'update']);
    Route::post('/payouts/{id}/approve', [PayoutController::class, 'approve']); // Thêm route mới
});