<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\GHNController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Support\Facades\Redis;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Http\Controllers\CartController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\AdminSellerController;
use App\Http\Controllers\SellerFollowerController;

// api chat user width seller
use App\Http\Controllers\ChatController;
use App\Http\Controllers\SettingController;

// api setting
// Route::middleware(['auth:sanctum', 'can:admin'])->group(function () {
//     Route::get('/settings', [SettingController::class, 'index']);
//     Route::put('/settings', [SettingController::class, 'update']);
//     Route::post('/settings/upload', [SettingController::class, 'upload']);
// });
Route::get('/settings', [SettingController::class, 'index']);
    Route::put('/settings', [SettingController::class, 'update']);
    Route::post('/settings/upload', [SettingController::class, 'upload']);
    Route::get('/settings/backup', [SettingController::class, 'backup']);
    Route::post('/settings/restore', [SettingController::class, 'restore']);





// api chat user width seller
Route::prefix('chat')->group(function () {
    Route::post('/session', [ChatController::class, 'createSession']);
    Route::get('/sessions', [ChatController::class, 'getSessions']);
    Route::post('/message', [ChatController::class, 'sendMessage']);
    Route::get('/messages/{sessionId}', [ChatController::class, 'getMessages']);
    Route::post('/messages/{sessionId}/read', [ChatController::class, 'markAsRead']);
});

// Category
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/parents', [CategoryController::class, 'showAllCategoryParent']);
    Route::get('/tree', [CategoryController::class, 'getCategoryTree']);
    Route::get('/{id}', [CategoryController::class, 'show']);
    Route::get('/{id}/children', [CategoryController::class, 'children']);
    Route::get('/{id}/parents', [CategoryController::class, 'parents']);
    Route::post('/', [CategoryController::class, 'store']);
    Route::put('/{id}', [CategoryController::class, 'update']);
    Route::delete('/{id}', [CategoryController::class, 'destroy']);
});


Route::prefix('notifications')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::post('/', [NotificationController::class, 'store']);
    Route::get('/{id}', [NotificationController::class, 'show']);
    Route::put('/{id}', [NotificationController::class, 'update']);
    Route::post('/mark-read', [NotificationController::class, 'markAsRead']);
    Route::delete('/{id}', [NotificationController::class, 'destroy']);
    Route::post('/send-multiple', [NotificationController::class, 'sendMultiple']);
    Route::post('/destroy-multiple', [NotificationController::class, 'destroyMultiple']);
    Route::delete('/destroy-all', [NotificationController::class, 'destroyAll']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my-notifications', [NotificationController::class, 'getMyNotifications']);

    // ✅ Đánh dấu 1 thông báo là đã đọc
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    // ✅ Đánh dấu nhiều thông báo là đã đọc
    Route::post('/notifications/mark-multiple-read', [NotificationController::class, 'markMultipleAsRead']);

    // ✅ Đánh dấu tất cả thông báo là đã đọc
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);

    // ✅ Ẩn (không xóa thật) nhiều thông báo
    Route::post('/notifications/delete-multiple', [NotificationController::class, 'deleteMultiple']);

    // ✅ Ẩn tất cả thông báo
    Route::delete('/notifications/delete-all', [NotificationController::class, 'deleteAll']);
});



//tags
Route::prefix('tags')->group(function () {
    Route::get('/', [TagController::class, 'index']);
    Route::get('/{id}', [TagController::class, 'show']);
    Route::post('/', [TagController::class, 'store']);
    Route::put('/{id}', [TagController::class, 'update']);
    Route::patch('/{id}', [TagController::class, 'update']);
    Route::delete('/{id}', [TagController::class, 'destroy']);
});

//attributes
Route::prefix('attributes')->group(function () {
    Route::get('/', [AttributeController::class, 'index']);
    Route::get('/{id}', [AttributeController::class, 'show']);
    Route::post('/', [AttributeController::class, 'store']);
    Route::put('/{id}', [AttributeController::class, 'update']);
    Route::delete('/{id}', [AttributeController::class, 'destroy']);
    Route::get('/slug/{slug}', [AttributeController::class, 'showBySlug']);
});

// Products
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/sellers', [ProductController::class, 'getAllProductBySellers'])->middleware('auth:sanctum');
    Route::get('/trash', [ProductController::class, 'getTrash']);
    Route::get('/shop', [ProductController::class, 'getAllProducts']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::post('/', [ProductController::class, 'store'])->middleware('auth:sanctum');
    Route::post('/import', [ProductController::class, 'import']);
    Route::put('/{id}', [ProductController::class, 'update'])->middleware('auth:sanctum');
    Route::delete('/{id}', [ProductController::class, 'destroy']);
    Route::get('/slug/{slug}', [ProductController::class, 'showBySlug']);
    Route::post('/change-status/{id}', [ProductController::class, 'changeStatus'])->middleware('auth:sanctum');
    Route::get('/category/{slug}', [ProductController::class, 'getProductBySlugCategory']);
    Route::get('/search/{slug?}', [ProductController::class, 'getProducts']);
    Route::get('/sellers/trash', [ProductController::class, 'getTrashBySeller'])->middleware('auth:sanctum');

});

// Orders
Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::get('/{id}', [OrderController::class, 'show']);
    Route::post('/', [OrderController::class, 'store']);
    Route::put('/{id}', [OrderController::class, 'update']);
    Route::delete('/{id}', [OrderController::class, 'destroy']);

    // Thêm routes cho mã giảm giá
    Route::post('/{id}/apply-discount', [OrderController::class, 'applyDiscount']);
    Route::delete('/{id}/remove-discount', [OrderController::class, 'removeDiscount']);
});

// Order Items
Route::prefix('order-items')->group(function () {
    Route::get('/', [OrderItemController::class, 'index']);
    Route::get('/{id}', [OrderItemController::class, 'show']);
    Route::post('/', [OrderItemController::class, 'store']);
    Route::put('/{id}', [OrderItemController::class, 'update']);
    Route::delete('/{id}', [OrderItemController::class, 'destroy']);
});

// Payments
Route::prefix('payments')->group(function () {
    Route::get('/', [PaymentController::class, 'index']);
    Route::get('/{id}', [PaymentController::class, 'show']);
    Route::post('/', [PaymentController::class, 'store']);
    Route::put('/{id}', [PaymentController::class, 'update']);
    Route::delete('/{id}', [PaymentController::class, 'destroy']);

    // VNPAY routes
    Route::post('/vnpay/create', [PaymentController::class, 'createVNPayPayment']);
    Route::match(['get', 'post'], '/vnpay/return', [PaymentController::class, 'vnpayReturn']);
    // MOMO routes
    Route::post('/momo/create', [PaymentController::class, 'createMoMoPayment']);
    Route::match(['get', 'post'], '/momo/return', [PaymentController::class, 'momoReturn']);
    Route::post('/momo/ipn', [PaymentController::class, 'momoIPN']);
});

// Payment Methods
Route::prefix('payment-methods')->group(function () {
    Route::get('/', [PaymentMethodController::class, 'index']);
    Route::get('/{id}', [PaymentMethodController::class, 'show']);
    Route::post('/', [PaymentMethodController::class, 'store']);
    Route::put('/{id}', [PaymentMethodController::class, 'update']);
    Route::delete('/{id}', [PaymentMethodController::class, 'destroy']);
});

// Đặt ở trên
Route::middleware('auth:sanctum')->get('/discounts/my-vouchers', [DiscountController::class, 'myVouchers']);

Route::prefix('discounts')->group(function () {
    Route::middleware('auth:sanctum')->get('/my-vouchers', [DiscountController::class, 'myVouchers']);
    Route::get('/', [DiscountController::class, 'index']);
    Route::get('/{id}', [DiscountController::class, 'show']);
    Route::post('/', [DiscountController::class, 'store']);
    Route::put('/{id}', [DiscountController::class, 'update']);
    Route::delete('/{id}', [DiscountController::class, 'destroy']);

    // Assign routes
    Route::post('/{discountId}/products', [DiscountController::class, 'assignProducts']);
    Route::post('/{discountId}/categories', [DiscountController::class, 'assignCategories']);
    Route::post('/{discountId}/users', [DiscountController::class, 'assignUsers']);

    // Flash sale routes
    Route::get('/flash-sales', [DiscountController::class, 'indexFlashSales']);
    Route::get('/flash-sales/{id}', [DiscountController::class, 'showFlashSale']);
    Route::post('/flash-sales', [DiscountController::class, 'storeFlashSale']);
    Route::put('/flash-sales/{id}', [DiscountController::class, 'updateFlashSale']);
    Route::delete('/flash-sales/{id}', [DiscountController::class, 'destroyFlashSale']);
});

// Api user login, otp, register, and resend otp
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('login', [AuthController::class, 'login']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/resend-otp-by-email', [AuthController::class, 'resendOtpByEmail']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);
Route::post('/send-forgot-password', [AuthController::class, 'sendForgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Reviews
Route::get('/reviews', [ReviewController::class, 'index']); // Hiển thị đánh giá công khai

Route::middleware(['auth:sanctum'])->prefix('admin/reviews')->group(function () {
    Route::get('/', [ReviewController::class, 'adminIndex']);
    Route::get('/{id}', [ReviewController::class, 'adminShow']);
    Route::put('/{id}', [ReviewController::class, 'adminUpdate']);
    Route::delete('/{id}', [ReviewController::class, 'adminDestroy']);
});

// Các route yêu cầu đăng nhập
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/reviews', [ReviewController::class, 'store']);               // Gửi đánh giá
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);          // Cập nhật đánh giá
    Route::post('/reviews/{id}/like', [ReviewController::class, 'like']);      // Like đánh giá
    Route::get('/reviews/{id}/liked', [ReviewController::class, 'checkLiked']);
    Route::post('/reviews/{id}/unlike', [ReviewController::class, 'unlike']);  // Unlike đánh giá
    Route::post('/reviews/{id}/reply', [ReviewController::class, 'reply']);    // Trả lời đánh giá
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);      // Xóa đánh giá

});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/address', [AddressController::class, 'index']);
    Route::get('/address/{id}', [AddressController::class, 'show']);
    Route::post('/address', [AddressController::class, 'store']);
    Route::put('/address/{id}', [AddressController::class, 'update']);
    Route::delete('/address/{id}', [AddressController::class, 'destroy']);
});


// google
Route::get('auth/google/redirect', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);


Route::get('/ghn/provinces', [GHNController::class, 'getProvinces']);
Route::get('/ghn/districts', [GHNController::class, 'getDistricts']);
Route::get('/ghn/wards', [GHNController::class, 'getWards']);
Route::post('/ghn/districts', [GHNController::class, 'getDistricts']);
Route::post('/ghn/wards', [GHNController::class, 'getWards']);
Route::post('/shipping/calculate-fee', [GHNController::class, 'calculateFee']);
Route::post('/ghn/services', [GHNController::class, 'getServices']);


// crud user
Route::apiResource('users', UserController::class);

Route::post('users/batch-delete', [UserController::class, 'batchDelete']);
Route::post('users/batch-add-role', [UserController::class, 'batchAddRole']);
Route::post('users/batch-remove-role', [UserController::class, 'batchRemoveRole']);
Route::get('/users/by-role/{role}', [UserController::class, 'getByRole']);


Route::post('profile/update/{id}', [UserController::class, 'updateUser']);

// crud user
Route::apiResource('users', UserController::class);

// api seller



Route::prefix('sellers')->group(function ()
{
    // lấy seller or business theo id
    Route::middleware('auth:sanctum')->get('/seller/me', [SellerController::class, 'getMySellerInfo']);

    Route::get('/', [SellerController::class, 'index']);
    Route::post('/register', [SellerController::class, 'register'])->middleware('auth:sanctum');
    // Route::post('/login', [SellerController::class, 'login']);
    Route::get('/', [SellerController::class, 'index'])->middleware('auth:sanctum');
    Route::get('/store/{slug}', [SellerController::class, 'showStore']);
    Route::post('/update', [SellerController::class, 'update'])->middleware('auth:sanctum');
    Route::get('/verified', [SellerController::class, 'getVerifiedSellers']);

});

// Seller Follower
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/sellers/{id}/follow', [SellerFollowerController::class, 'follow']);
    Route::post('/sellers/{id}/unfollow', [SellerFollowerController::class, 'unfollow']);
    Route::get('/my-followed-sellers', [SellerFollowerController::class, 'myFollows']);
    Route::get('/sellers/{id}/followers', [SellerFollowerController::class, 'followersOfSeller']);
});


Route::prefix('admin')->group(function () {
   Route::get('/sellers', [AdminSellerController::class, 'index']);
   Route::get('/sellers/{id}', [AdminSellerController::class, 'show']);
   Route::post('/sellers/{id}/verify', [AdminSellerController::class, 'verify']);
   Route::post('/sellers/{id}/reject', [AdminSellerController::class, 'reject']);
});

// chat bot
Route::post('/chatbot', [ChatbotController::class, 'chat']);


// Cart Management
Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index']);
    Route::post('/add', [CartController::class, 'addItem']);
    Route::put('/items/{id}', [CartController::class, 'updateItem']);
    Route::delete('/items/{id}', [CartController::class, 'removeItem']);
    Route::delete('/', [CartController::class, 'clear']);
    Route::post('/select-items', [CartController::class, 'selectItems']);
    Route::get('/selected-items', [CartController::class, 'getSelectedItems']);
});


// Dashboard stats
Route::prefix('dashboard')->group(function () {
    Route::get('/stats', [DashboardController::class, 'stats']);
    Route::get('/stats-list', [DashboardController::class, 'statsList']);
    Route::get('/revenue-chart', [DashboardController::class, 'revenueChart']);
    Route::get('/revenue-profit-chart', [DashboardController::class, 'revenueProfitChart']);
});

Route::get('inventory/list', [App\Http\Controllers\InventoryController::class, 'list']);
Route::get('inventory/low-stock', [App\Http\Controllers\InventoryController::class, 'lowStock']);
Route::get('inventory/best-sellers', [App\Http\Controllers\InventoryController::class, 'bestSellers']);

Route::middleware('auth:sanctum')->post('/discounts/save-by-code', [DiscountController::class, 'saveVoucherByCode']);
Route::middleware('auth:sanctum')->delete('/discounts/my-voucher/{id}', [DiscountController::class, 'deleteUserVoucher']);
