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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

use Illuminate\Support\Facades\Storage;



// Category
Route::prefix('categories')->group(function () {
Route::get('/', [CategoryController::class, 'index']);
Route::get('/{id}', [CategoryController::class, 'show']);
Route::get('/{id}/children', [CategoryController::class, 'children']);
Route::get('/{id}/parents', [CategoryController::class, 'parents']);
Route::post('/', [CategoryController::class, 'store']);
Route::put('/{id}', [CategoryController::class, 'update']);
Route::delete('/{id}', [CategoryController::class, 'destroy']);
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
    Route::get('/trash', [ProductController::class, 'getTrash']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::post('/', [ProductController::class, 'store']);
    Route::post('/import', [ProductController::class, 'import']);
    Route::put('/{id}', [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);
    Route::get('/slug/{slug}', [ProductController::class, 'showBySlug']);
    Route::post('/change-status/{id}', [ProductController::class, 'changeStatus']);

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
    Route::get('/vnpay/return', [PaymentController::class, 'vnpayReturn']);

    // MOMO routes
    Route::post('/momo/create', [PaymentController::class, 'createMoMoPayment']);
    Route::get('/momo/return', [PaymentController::class, 'momoReturn']);
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

// Discounts
Route::prefix('discounts')->group(function () {
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

// Các route yêu cầu đăng nhập
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/reviews', [ReviewController::class, 'store']);               // Gửi đánh giá
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);          // Cập nhật đánh giá
    Route::post('/reviews/{id}/like', [ReviewController::class, 'like']);      // Like đánh giá
    Route::post('/reviews/{id}/reply', [ReviewController::class, 'reply']);    // Trả lời đánh giá
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);      // Xóa đánh giá
});


Route::get('/address', [AddressController::class, 'index']);
Route::post('/address', [AddressController::class, 'store']);
Route::put('/address/{id}', [AddressController::class, 'update']);
Route::delete('/address/{id}', [AddressController::class, 'destroy']);
// google
Route::get('auth/google/redirect', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);


Route::get('/ghn/provinces', [GHNController::class, 'getProvinces']);
Route::get('/ghn/districts', [GHNController::class, 'getDistricts']);
Route::get('/ghn/wards', [GHNController::class, 'getWards']);
Route::post('/ghn/districts', [GHNController::class, 'getDistricts']);
Route::post('/ghn/wards', [GHNController::class, 'getWards']);
Route::post('/ghn/fee', [GHNController::class, 'calculateFee']);

// crud user
Route::apiResource('users', UserController::class);

Route::post('users/batch-delete', [UserController::class, 'batchDelete']);
Route::post('users/batch-add-role', [UserController::class, 'batchAddRole']);
Route::post('users/batch-remove-role', [UserController::class, 'batchRemoveRole']);


