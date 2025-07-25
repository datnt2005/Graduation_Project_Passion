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
    Route::get('/posts/{post}/comments/{id}/liked', [PostCommentController::class, 'liked']); // Đã like chưa
    Route::post('/posts/{post}/comments/{id}/reply', [PostCommentController::class, 'reply']);     // Trả lời bình luận
});

Route::prefix('admin')->middleware(['auth:sanctum', 'checkRole:admin'])->group(function () {
    Route::get('post-comments', [PostCommentController::class, 'indexAdmin']);
    Route::put('post-comments/{id}', [PostCommentController::class, 'updateAdmin']);
    Route::delete('post-comments/{id}', [PostCommentController::class, 'destroyAdmin']);
});

