<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostCommentController;

// ======= Public Routes =======
Route::get('/posts/{post}/comments', [PostCommentController::class, 'getByPost']);         // Lấy bình luận bài viết
Route::get('/posts/{post}/comments/{id}', [PostCommentController::class, 'show']);          // Xem chi tiết 1 bình luận

// ======= Authenticated User (user, seller, admin) =======
Route::middleware(['auth:sanctum', 'checkRole:user,seller,admin'])->group(function () {
    Route::post('/posts/{post}/comments', [PostCommentController::class, 'store']);         // Gửi bình luận
    Route::put('/posts/{post}/comments/{id}', [PostCommentController::class, 'update']);    // Cập nhật bình luận
    Route::delete('/posts/{post}/comments/{id}', [PostCommentController::class, 'destroy']); // Xoá bình luận cá nhân
    Route::post('/posts/{post}/comments/{id}/like', [PostCommentController::class, 'like']);       // Like
    Route::post('/posts/{post}/comments/{id}/unlike', [PostCommentController::class, 'unlike']);   // Unlike
    Route::get('/posts/{post}/comments/{id}/liked', [PostCommentController::class, 'checkLiked']); // Đã like chưa
    Route::post('/posts/{post}/comments/{id}/reply', [PostCommentController::class, 'reply']);     // Trả lời bình luận
});

// ======= Admin Routes =======
Route::prefix('admin/post-comments')->middleware(['auth:sanctum', 'checkRole:admin'])->group(function () {
    Route::get('/', [PostCommentController::class, 'adminIndex']);          // Tất cả comment
    Route::get('/{id}', [PostCommentController::class, 'adminShow']);       // Xem chi tiết
    Route::put('/{id}', [PostCommentController::class, 'adminUpdate']);     // Admin phản hồi
    Route::delete('/{id}', [PostCommentController::class, 'adminDestroy']); // Admin xóa
});

