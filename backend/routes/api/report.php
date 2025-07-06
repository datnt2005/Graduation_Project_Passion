    <?php

    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\ReportController;

    // Người dùng gửi báo cáo (user/seller/admin)
    Route::middleware(['auth:sanctum', 'checkRole:user,seller,admin'])->group(function () {
        Route::post('/reports', [ReportController::class, 'store']);
    });

    // Quản trị viên xử lý báo cáo – Tách riêng route theo từng loại nếu cần
    Route::prefix('admin/reports')->middleware(['auth:sanctum', 'checkRole:admin'])->group(function () {
        Route::get('/', [ReportController::class, 'index']); // Lấy tất cả report (review + post_comment)
        Route::get('/{id}', [ReportController::class, 'show']); // Xem chi tiết 1 report
        Route::put('/{id}/status', [ReportController::class, 'updateStatus']); // Cập nhật trạng thái (resolved, dismissed)
    });
    
