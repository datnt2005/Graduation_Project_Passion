<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\GhnSyncLog;
use App\Models\Payout;
use App\Models\Refund;
use App\Models\Seller;
use App\Models\User;
use App\Models\Discount;
use App\Models\DiscountUser;
use App\Models\Shipping;
use App\Models\ShippingMethod;
use App\Models\Address;
use App\Services\GHNService;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Mail\WarningEmail;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Mail\WarningRejectedOrder;
use App\Mail\OrderStatusUpdatedMail;
use App\Mail\OrderSuccessMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{

    public function syncGhnStatus(Request $request, $orderId)
    {
        try {
            $request->validate([
                'tracking_code' => 'required|string'
            ]);

            $user = Auth::user();
            $order = Order::with('shipping')->findOrFail($orderId);

            // Kiểm tra mã vận đơn đã tồn tại trong bảng shipping
            $existingOrder = Order::whereHas('shipping', function ($query) use ($request) {
                $query->where('tracking_code', $request->tracking_code);
            })->where('id', '!=', $orderId)->first();

            if ($existingOrder) {
                GhnSyncLog::create([
                    'order_id' => $orderId,
                    'tracking_code' => $request->tracking_code,
                    'ghn_status' => 'none',
                    'success' => false,
                    'message' => 'Mã vận đơn này đã được sử dụng cho đơn hàng ' . $existingOrder->id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Mã vận đơn này đã được sử dụng cho đơn hàng ' . $existingOrder->id
                ], 400);
            }

            Log::info('GHN API Request:', [
                'url' => env('GHN_API_URL') . '/v2/shipping-order/detail',
                'headers' => [
                    'Token' => env('GHN_TOKEN'),
                    'ShopId' => env('GHN_SHOP_ID')
                ],
                'tracking_code' => $request->tracking_code,
                'order_id' => $orderId
            ]);

            $ghnResponse = Http::withHeaders([
                'Token' => env('GHN_TOKEN'),
                'Content-Type' => 'application/json',
                'ShopId' => env('GHN_SHOP_ID')
            ])->post(env('GHN_API_URL') . '/v2/shipping-order/detail', [
                'order_code' => $request->tracking_code
            ]);

            if ($ghnResponse->failed()) {
                $errorMessage = $ghnResponse->json()['message'] ?? 'Không xác định';
                $errorCode = $ghnResponse->json()['code'] ?? 'Không xác định';
                GhnSyncLog::create([
                    'order_id' => $orderId,
                    'tracking_code' => $request->tracking_code,
                    'ghn_status' => 'none',
                    'success' => false,
                    'message' => "Lỗi từ GHN: {$errorMessage} (Code: {$errorCode})"
                ]);
                throw new \Exception("Lỗi từ GHN: {$errorMessage} (Code: {$errorCode})");
            }

            $ghnData = $ghnResponse->json();
            $ghnStatus = $ghnData['data']['status'] ?? null;

            if (!$ghnStatus) {
                GhnSyncLog::create([
                    'order_id' => $orderId,
                    'tracking_code' => $request->tracking_code,
                    'ghn_status' => 'none',
                    'success' => false,
                    'message' => 'Không tìm thấy trạng thái GHN'
                ]);
                throw new \Exception('Không tìm thấy trạng thái GHN');
            }

            $validGhnStatuses = [
                'ready_to_pick',
                'picking',
                'picked',
                'delivering',
                'delivered',
                'return',
                'returned',
                'cancel',
                'cancelled'
            ];

            if (!in_array($ghnStatus, $validGhnStatuses)) {
                GhnSyncLog::create([
                    'order_id' => $orderId,
                    'tracking_code' => $request->tracking_code,
                    'ghn_status' => $ghnStatus,
                    'success' => false,
                    'message' => "Trạng thái GHN không được hỗ trợ: {$ghnStatus}"
                ]);
                throw new \Exception("Trạng thái GHN không được hỗ trợ: {$ghnStatus}");
            }

            if ($ghnStatus === 'delivered') {
                $verifyResponse = Http::withHeaders([
                    'Token' => env('GHN_TOKEN'),
                    'Content-Type' => 'application/json',
                    'ShopId' => env('GHN_SHOP_ID')
                ])->post(env('GHN_API_URL') . '/v2/shipping-order/detail', [
                    'order_code' => $request->tracking_code
                ]);
                if ($verifyResponse->failed() || $verifyResponse->json()['data']['status'] !== 'delivered') {
                    GhnSyncLog::create([
                        'order_id' => $orderId,
                        'tracking_code' => $request->tracking_code,
                        'ghn_status' => $ghnStatus,
                        'success' => false,
                        'message' => 'Trạng thái GHN không khớp với delivered'
                    ]);
                    throw new \Exception('Trạng thái GHN không khớp với delivered');
                }
            }

            // Ánh xạ trạng thái GHN sang trạng thái đơn hàng hợp lệ
            $orderStatus = match ($ghnStatus) {
                'ready_to_pick', 'picking', 'picked', 'delivering' => 'shipping', // Thay 'shipped' bằng 'shipping'
                'delivered' => 'delivered',
                'return', 'returned' => 'returned',
                'cancel', 'cancelled' => 'cancelled',
                default => $order->status
            };

            // Lấy shipping_method_id hợp lệ
            $defaultShippingMethod = ShippingMethod::where('status', 'active')->first();
            if (!$defaultShippingMethod) {
                throw new \Exception('Không tìm thấy phương thức vận chuyển hợp lệ trong bảng shipping_methods');
            }

            // Cập nhật hoặc tạo bản ghi trong bảng shipping
            $order->shipping()->updateOrCreate(
                ['order_id' => $order->id],
                [
                    'tracking_code' => $request->tracking_code,
                    'status' => $ghnStatus,
                    'shipping_fee' => $ghnData['data']['service_fee'] ?? $order->shipping_fee ?? 0,
                    'note' => $ghnData['data']['note'] ?? $order->shipping?->note ?? '',
                    'shipping_method_id' => $defaultShippingMethod->id,
                    'estimated_delivery' => $order->shipping?->estimated_delivery ?? now()->addDays($defaultShippingMethod->estimated_days)
                ]
            );

            // Cập nhật trạng thái đơn hàng
            $order->update([
                'status' => $orderStatus,
                'failure_reason' => $order->failure_reason
            ]);

            if ($orderStatus === 'delivered' && !$order->payout_id) {
                $payoutAmount = max(($order->final_price - ($order->shipping?->shipping_fee ?? $order->shipping_fee ?? 0)) * 0.95, 0);
                $payout = Payout::create([
                    'seller_id' => $user->role === 'seller' ? Seller::where('user_id', $user->id)->first()->id : null,
                    'order_id' => $order->id,
                    'amount' => $payoutAmount,
                    'status' => 'pending',
                    'note' => 'Payout tự động cho đơn hàng ' . $request->tracking_code
                ]);
                $order->update([
                    'payout_id' => $payout->id,
                    'payout_status' => 'pending',
                    'payout_amount' => $payoutAmount
                ]);
                Log::info("Payout tự động được tạo cho order_id {$orderId}", [
                    'payout_id' => $payout->id,
                    'amount' => $payoutAmount,
                    'tracking_code' => $request->tracking_code
                ]);
            }

            GhnSyncLog::create([
                'order_id' => $orderId,
                'tracking_code' => $request->tracking_code,
                'ghn_status' => $ghnStatus,
                'success' => true,
                'message' => 'Đồng bộ trạng thái GHN thành công'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đồng bộ trạng thái GHN thành công',
                'data' => [
                    'status' => $orderStatus,
                    'shipping_status' => $ghnStatus,
                    'payout_id' => $order->payout_id ?? null,
                    'payout_status' => $order->payout_status ?? null
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error("Lỗi đồng bộ GHN cho order_id {$orderId}: {$e->getMessage()}", [
                'tracking_code' => $request->tracking_code ?? 'unknown',
                'stack_trace' => $e->getTraceAsString()
            ]);
            GhnSyncLog::create([
                'order_id' => $orderId,
                'tracking_code' => $request->tracking_code ?? 'unknown',
                'ghn_status' => 'none',
                'success' => false,
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e instanceof \Illuminate\Validation\ValidationException ? 422 : 500);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function validateBuyNow(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:products,id',
                'product_variant_id' => 'nullable|exists:product_variants,id',
                'quantity' => 'required|integer|min:1',
                'price' => 'required|numeric|min:0',
            ], [
                'product_id.required' => 'ID sản phẩm là bắt buộc',
                'product_id.exists' => 'Sản phẩm không tồn tại',
                'product_variant_id.exists' => 'Biến thể sản phẩm không tồn tại',
                'quantity.required' => 'Số lượng là bắt buộc',
                'quantity.integer' => 'Số lượng phải là số nguyên',
                'quantity.min' => 'Số lượng phải lớn hơn 0',
                'price.required' => 'Giá sản phẩm là bắt buộc',
                'price.numeric' => 'Giá phải là số',
                'price.min' => 'Giá phải lớn hơn hoặc bằng 0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Lấy dữ liệu sản phẩm
            $product = Product::find($request->product_id);
            $variant = $request->product_variant_id ? ProductVariant::find($request->product_variant_id) : null;

            // Validate biến thể
            if ($request->product_variant_id && !$variant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Biến thể sản phẩm không tồn tại'
                ], 404);
            }

            // Kiểm tra giá
            $actualPrice = $variant ? ($variant->sale_price ?? $variant->price ?? 0) : ($variant->sale_price ?? $variant->price ?? 0);
            if ($actualPrice <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm không có giá hợp lệ trong cơ sở dữ liệu'
                ], 400);
            }

            if (abs($actualPrice - $request->price) > 0.01) {
                return response()->json([
                    'success' => false,
                    'message' => 'Giá sản phẩm không khớp với dữ liệu server'
                ], 400);
            }

            // Kiểm tra tồn kho
            $stock = $variant ? $variant->quantity : $product->quantity;
            if ($request->quantity > $stock) {
                return response()->json([
                    'success' => false,
                    'message' => "Số lượng vượt quá tồn kho ($stock)"
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Dữ liệu buy now hợp lệ'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi validate dữ liệu buy now',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function index(Request $request)
    {
        try {
            $query = Order::with([
                'orderItems.product',
                'orderItems.productVariant',
                'user',
                'address',
                'payments.paymentMethod',
                'shipping',
                'refund',
                'payout'
            ]);

            // Lọc theo trạng thái
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }

            // Lọc theo khoảng thời gian
            if ($request->has('from_date') && !empty($request->from_date)) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }
            if ($request->has('to_date') && !empty($request->to_date)) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }

            // Tìm kiếm theo ID đơn hàng
            if ($request->has('order_id') && !empty($request->order_id)) {
                $query->where('id', $request->order_id);
            }

            // Lọc theo mã vận đơn
            if ($request->has('tracking_code') && !empty($request->tracking_code)) {
                $query->whereHas('shipping', function ($q) use ($request) {
                    $q->where('tracking_code', $request->tracking_code);
                });
            }

            // Lọc theo phương thức thanh toán
            if ($request->has('payment_method') && !empty($request->payment_method)) {
                $query->whereHas('payments.paymentMethod', function ($q) use ($request) {
                    $q->where('name', $request->payment_method);
                });
            }

            // Sắp xếp
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $allowedSortColumns = ['created_at', 'id', 'status', 'total_price', 'final_price'];
            $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'created_at';
            $query->orderBy($sortBy, $sortOrder);

            // Phân trang
            $perPage = (int)$request->input('per_page', 10);
            $perPage = max(1, min(100, $perPage));
            $orders = $query->paginate($perPage);

            if ($orders->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'meta' => [
                        'current_page' => 1,
                        'last_page' => 1,
                        'per_page' => $perPage,
                        'total' => 0,
                    ],
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'shipping' => $order->shipping ? [
                            'tracking_code' => $order->shipping->tracking_code,
                            'status' => $order->shipping->status,
                            'shipping_fee' => (float)$order->shipping->shipping_fee,
                            'estimated_delivery' => $order->shipping->estimated_delivery ? $order->shipping->estimated_delivery->toISOString() : null,
                        ] : null,
                        'user' => $order->user ? [
                            'id' => $order->user->id,
                            'name' => $order->user->name,
                            'email' => $order->user->email,
                        ] : null,
                        'address' => $order->address ? [
                            'id' => $order->address->id,
                            'address' => $order->address->address,
                            'phone' => $order->address->phone,
                            'province_id' => $order->address->province_id,
                            'district_id' => $order->address->district_id,
                            'ward_code' => $order->address->ward_code,
                            'detail' => $order->address->detail,
                        ] : null,
                        'note' => $order->note ?? '',
                        'status' => $order->status,
                        'failure_reason' => $order->failure_reason ?? null,
                        'can_delete' => in_array($order->status, ['pending', 'cancelled', 'failed', 'failed_delivery', 'rejected_by_customer']),
                        'total_price' => (float)$order->total_price,
                        'discount_price' => (float)$order->discount_price,
                        'final_price' => (float)$order->final_price,
                        'payout_amount' => $order->payout ? (float)$order->payout->amount : 0,
                        'payout_id' => $order->payout ? $order->payout->id : null,
                        'payout_status' => $order->payout ? $order->payout->status : null,
                        'transferred_at' => $order->payout ? ($order->payout->transferred_at ? $order->payout->transferred_at->toISOString() : null) : null,
                        'shipping_method' => $order->shipping_method,
                        'created_at' => $order->created_at ? $order->created_at->toISOString() : null,
                        'order_items' => $order->orderItems->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'product' => $item->product ? [
                                    'id' => $item->product->id,
                                    'name' => $item->product->name,
                                    'thumbnail' => $item->product->thumbnail,
                                ] : null,
                                'variant' => $item->productVariant ? [
                                    'id' => $item->productVariant->id,
                                    'name' => $item->productVariant->name,
                                ] : null,
                                'quantity' => $item->quantity,
                                'price' => (float)$item->price,
                                'total' => (float)($item->price * $item->quantity),
                            ];
                        }),
                        'payments' => $order->payments->map(function ($payment) {
                            return [
                                'id' => $payment->id,
                                'method' => $payment->paymentMethod ? $payment->paymentMethod->name : null,
                                'amount' => (float)$payment->amount,
                                'status' => $payment->status,
                                'created_at' => $payment->created_at ? $payment->created_at->toISOString() : null,
                            ];
                        }),
                        'refund' => $order->refund ? [
                            'id' => $order->refund->id,
                            'order_id' => $order->refund->order_id,
                            'user_id' => $order->refund->user_id,
                            'amount' => (float)$order->refund->amount,
                            'status' => $order->refund->status,
                            'reason' => $order->refund->reason,
                            'created_at' => $order->refund->created_at ? $order->refund->created_at->toISOString() : null,
                        ] : null,
                    ];
                })->toArray(),
                'meta' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi lấy danh sách đơn hàng: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách đơn hàng',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function checkCodEligibility(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Bạn cần đăng nhập để kiểm tra.'], 401);
        }

        $rejectedOrdersCount = Order::where('user_id', $user->id)
            ->where('status', 'rejected_by_customer')
            ->count();

        Log::debug('COD eligibility and account status check', [
            'user_id' => $user->id,
            'rejected_orders_count' => $rejectedOrdersCount,
            'orders' => Order::where('user_id', $user->id)
                ->where('status', 'rejected_by_customer')
                ->pluck('id')
                ->toArray(),
            'account_status' => $user->status,
        ]);

        if ($rejectedOrdersCount >= 3 && $user->status !== 'banned') {
            $user->status = 'banned';
            $user->save();
            Log::info('User account banned', [
                'user_id' => $user->id,
                'email' => $user->email,
                'rejected_orders_count' => $rejectedOrdersCount,
            ]);
        }

        return response()->json([
            'can_use_cod' => $rejectedOrdersCount < 2,
            'is_account_banned' => $user->status === 'banned',
            'message' => $user->status === 'banned'
                ? 'Tài khoản của bạn đã bị khóa do có quá nhiều đơn hàng bị từ chối nhận.'
                : ($rejectedOrdersCount >= 2
                    ? 'Bạn không thể sử dụng phương thức thanh toán COD vì có

 quá nhiều đơn hàng bị từ chối nhận.'
                    : 'Bạn có thể sử dụng phương thức thanh toán COD.')
        ], 200);
    }

    public function store(Request $request)
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Bạn cần đăng nhập để đặt hàng.'], 401);
        }

        if ($request->user()->status === 'banned') {
            return response()->json([
                'message' => 'Tài khoản của bạn đã bị khóa do có quá nhiều đơn hàng bị từ chối nhận.',
                'is_account_banned' => true
            ], 403);
        }

        Log::info('Order store request', [
            'request_data' => $request->all(),
            'user_id' => $request->user()->id,
            'items_count' => count($request->items ?? []),
            'payment_method' => $request->payment_method,
            'service_id' => $request->service_id,
        ]);

        if ($request->payment_method === 'COD') {
            $rejectedOrdersCount = Order::where('user_id', $request->user()->id)
                ->where('status', 'rejected_by_customer')
                ->count();

            if ($rejectedOrdersCount >= 2) {
                return response()->json([
                    'message' => 'Bạn không thể sử dụng phương thức thanh toán COD vì có quá nhiều đơn hàng bị từ chối nhận.',
                    'can_use_cod' => false
                ], 403);
            }
        }

        $request->validate([
            'discount_ids' => 'nullable|array',
            'discount_ids.*' => 'exists:discounts,id',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:COD,VNPAY,MOMO',
            'address_id' => 'required|exists:addresses,id',
            'service_id' => 'required|integer|min:1',
            'is_buy_now' => 'nullable|boolean',
            'skip_stock_check' => 'nullable|boolean',
        ], [
            'discount_ids.*.exists' => 'ID mã giảm giá không tồn tại',
            'items.required' => 'Danh sách sản phẩm là bắt buộc',
            'items.array' => 'Danh sách sản phẩm phải là một mảng',
            'items.min' => 'Phải có ít nhất một sản phẩm',
            'items.*.product_id.required' => 'ID sản phẩm là bắt buộc',
            'items.*.product_id.exists' => 'ID sản phẩm không tồn tại',
            'items.*.product_variant_id.exists' => 'ID biến thể sản phẩm không tồn tại',
            'items.*.quantity.required' => 'Số lượng sản phẩm là bắt buộc',
            'items.*.quantity.integer' => 'Số lượng phải là số nguyên',
            'items.*.quantity.min' => 'Số lượng phải lớn hơn 0',
            'items.*.price.required' => 'Giá sản phẩm là bắt buộc',
            'items.*.price.numeric' => 'Giá phải là số',
            'items.*.price.min' => 'Giá phải lớn hơn hoặc bằng 0',
            'payment_method.required' => 'Phương thức thanh toán là bắt buộc',
            'payment_method.in' => 'Phương thức thanh toán không hợp lệ',
            'address_id.required' => 'Địa chỉ là bắt buộc',
            'address_id.exists' => 'Địa chỉ không tồn tại',
            'service_id.required' => 'Phương thức giao hàng là bắt buộc',
            'service_id.integer' => 'Phương thức giao hàng phải là số nguyên',
            'service_id.min' => 'Phương thức giao hàng không hợp lệ',
            'is_buy_now.boolean' => 'Trường is_buy_now phải là boolean',
            'skip_stock_check.boolean' => 'Trường skip_stock_check phải là boolean',
        ]);

        try {
            DB::beginTransaction();

            $items = $request->items;
            $products = Product::whereIn('id', collect($items)->pluck('product_id'))->get()->keyBy('id');
            foreach ($items as $item) {
                $product = $products[$item['product_id']] ?? null;
                if (!$product) {
                    throw new \Exception('Sản phẩm không tồn tại: ' . $item['product_id']);
                }

                $variant = $item['product_variant_id'] ? ProductVariant::find($item['product_variant_id']) : null;
                if ($item['product_variant_id'] && !$variant) {
                    throw new \Exception('Biến thể sản phẩm không tồn tại: ' . $item['product_variant_id']);
                }

                $actualPrice = $variant ? ($variant->sale_price ?? $variant->price) : ($product->sale_price ?? $product->original_price);
                if (abs($actualPrice - $item['price']) > 0.01) {
                    throw new \Exception('Giá sản phẩm không khớp: ' . $item['product_id']);
                }

                if ($variant && !($request->skip_stock_check ?? false)) {
                    $stock = $variant->quantity ?? 0;

                    Log::info('Stock check for variant', [
                        'product_id' => $item['product_id'],
                        'variant_id' => $item['product_variant_id'],
                        'requested_quantity' => $item['quantity'],
                        'available_stock' => $stock,
                    ]);

                    if ($stock > 0 && $item['quantity'] > $stock) {
                        throw new \Exception('Số lượng vượt quá tồn kho: ' . $item['product_id'] . ' (Có: ' . $stock . ', Yêu cầu: ' . $item['quantity'] . ')');
                    }
                } else {
                    Log::info('Skipping stock check', [
                        'product_id' => $item['product_id'],
                        'requested_quantity' => $item['quantity'],
                        'skip_stock_check' => $request->skip_stock_check ?? false,
                        'has_variant' => $variant ? 'yes' : 'no',
                    ]);
                }
            }

            $itemsBySeller = [];
            $isBuyNow = $request->is_buy_now ?? false;
            if ($isBuyNow) {
                $itemsBySeller[0] = $items;
            } else {
                foreach ($items as $item) {
                    $product = $products[$item['product_id']];
                    $sellerId = $product->seller_id;
                    $itemsBySeller[$sellerId][] = $item;
                }
            }

            $orders = [];
            foreach ($itemsBySeller as $sellerId => $sellerItems) {
                $order = Order::create([
                    'user_id' => $request->user()->id,
                    'address_id' => $request->address_id,
                    'discount_id' => null,
                    'note' => $request->note ?? '',
                    'status' => 'pending',
                    'total_price' => 0,
                    'discount_price' => 0,
                    'final_price' => 0,
                    'is_buy_now' => $isBuyNow,
                ]);

                $totalPrice = 0;
                foreach ($sellerItems as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'product_variant_id' => $item['product_variant_id'] ?? null,
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);
                    $totalPrice += $item['price'] * $item['quantity'];
                }

                $discountPrice = 0;
                $appliedDiscountId = null;
                $shopDiscount = null;
                $adminDiscount = null;
                $shippingDiscount = null;
                if ($request->discount_ids && is_array($request->discount_ids)) {
                    foreach ($request->discount_ids as $did) {
                        $discount = Discount::find($did);
                        if (!$discount) continue;
                        if ($discount->discount_type === 'shipping_fee' && $discount->seller_id === null) {
                            $shippingDiscount = $discount;
                        } elseif ($discount->seller_id == $sellerId && ($discount->discount_type === 'percentage' || $discount->discount_type === 'fixed')) {
                            $shopDiscount = $discount;
                        } elseif ($discount->seller_id === null && ($discount->discount_type === 'percentage' || $discount->discount_type === 'fixed')) {
                            $adminDiscount = $discount;
                        }
                    }
                }

                $usedDiscount = $shopDiscount ?: $adminDiscount;
                if ($usedDiscount) {
                    $discountPrice = $usedDiscount->discount_type === 'percentage'
                        ? $totalPrice * ($usedDiscount->discount_value / 100)
                        : $usedDiscount->discount_value;
                    $discountPrice = min($discountPrice, $totalPrice);
                    DiscountUser::create([
                        'discount_id' => $usedDiscount->id,
                        'user_id' => $request->user()->id,
                        'is_used' => true,
                    ]);
                    $order->discount_id = $usedDiscount->id;
                }

                $finalPrice = $totalPrice - $discountPrice;

                $address = Address::find($request->address_id);
                $shippingFee = 0;
                $trackingCode = null;
                $estimatedDelivery = null;
                try {
                    $ghn = new GHNService();
                    $ghnOrder = $ghn->createShippingOrder($order, $address, $request->service_id, $request->payment_method);
                    $shippingFee = $ghnOrder['total_fee'] ?? 0;
                    $trackingCode = $ghnOrder['order_code'] ?? null;
                    $estimatedDelivery = $ghnOrder['expected_delivery_time'] ?? null;
                } catch (\Exception $e) {
                    Log::warning('GHN API error: ' . $e->getMessage());
                    $shippingFee = 30000;
                }

                if ($shippingDiscount) {
                    $shippingFee = max(0, $shippingFee - $shippingDiscount->discount_value);
                    DiscountUser::create([
                        'discount_id' => $shippingDiscount->id,
                        'user_id' => $request->user()->id,
                        'is_used' => true,
                    ]);
                }

                $shippingMethod = ShippingMethod::firstOrCreate(
                    ['id' => $request->service_id],
                    ['name' => 'GHN Standard', 'carrier' => 'GHN', 'estimated_days' => 3, 'cost' => $shippingFee]
                );
                $shipping = Shipping::create([
                    'order_id' => $order->id,
                    'shipping_method_id' => $shippingMethod->id,
                    'estimated_delivery' => $estimatedDelivery,
                    'shipping_fee' => $shippingFee,
                    'tracking_code' => $trackingCode,
                    'status' => 'pending',
                ]);

                $order->update([
                    'total_price' => $totalPrice,
                    'discount_price' => $discountPrice,
                    'final_price' => $finalPrice,
                    'discount_id' => $order->discount_id,
                ]);

                $paymentMethod = PaymentMethod::firstOrCreate(
                    ['name' => $request->payment_method],
                    ['status' => 'active']
                );

                $totalPaymentAmount = $finalPrice + $shippingFee;

                Payment::create([
                    'order_id' => $order->id,
                    'payment_method_id' => $paymentMethod->id,
                    'amount' => $totalPaymentAmount,
                    'status' => 'pending'
                ]);

                if ($request->payment_method === 'COD' && $order->user && $order->user->email) {
                    try {
                        Mail::to($order->user->email)->send(new OrderSuccessMail($order));
                    } catch (\Exception $e) {
                        Log::warning('Send order success mail error: ' . $e->getMessage());
                    }
                }

                $order->load([
                    'shipping',
                    'payments.paymentMethod'
                ]);

                $orders[] = $this->formatOrderResponse($order);
            }

            DB::commit();

            return response()->json([
                'message' => 'Đơn hàng đã được tạo thành công',
                'orders' => $orders
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order store error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Có lỗi xảy ra khi tạo đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $user = auth()->user();
            $order = Order::with([
                'orderItems.product',
                'orderItems.productVariant',
                'user',
                'address',
                'payments.paymentMethod',
                'shipping'
            ])->where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $this->formatOrderResponse($order)
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Order not found', [
                'order_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng với ID: ' . $id
            ], 404);
        } catch (\Exception $e) {
            Log::error('Order show error', [
                'order_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy thông tin đơn hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   public function update(Request $request, $id)
    {
        try {
            // Lấy payload thô từ request
            $rawPayload = $request->getContent();
            Log::info('Raw request payload', [
                'order_id' => $id,
                'raw_payload' => $rawPayload,
                'parsed_payload' => $request->all(),
                'request_failure_reason' => $request->input('failure_reason'),
                'request_status' => $request->input('status')
            ]);

            // Kiểm tra xem payload có phải JSON hợp lệ không
            $jsonPayload = json_decode($rawPayload, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Lỗi phân tích JSON payload', [
                    'order_id' => $id,
                    'json_error' => json_last_error_msg(),
                    'raw_payload' => $rawPayload
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Payload JSON không hợp lệ',
                    'error' => json_last_error_msg()
                ], 400);
            }

            // Tìm đơn hàng trước để log trạng thái hiện tại
            $order = Order::find($id);
            if (!$order) {
                Log::warning('Đơn hàng không tồn tại', ['order_id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn hàng không tồn tại'
                ], 404);
            }

            // Xác thực dữ liệu
            $validator = Validator::make($request->all(), [
                'status' => ['required', 'string', function ($attribute, $value, $fail) use ($order) {
                    if (!$order->isValidStatus($value)) {
                        $fail('Trạng thái không hợp lệ.');
                    }
                    if (!$order->canTransitionTo($value)) {
                        $fail("Không thể chuyển trạng thái từ '{$order->status}' sang '$value'.");
                    }
                }],
                'note' => 'nullable|string|max:500',
                'failure_reason' => ['required_if:status,failed,failed_delivery,rejected_by_customer', 'string', 'max:255'],
                'tracking_code' => ['required_if:status,shipping', 'nullable', 'string', 'regex:/^[A-Za-z0-9]{6}$/'],
            ], [
                'status.required' => 'Trạng thái đơn hàng là bắt buộc',
                'status.string' => 'Trạng thái đơn hàng phải là chuỗi',
                'note.string' => 'Ghi chú phải là chuỗi ký tự',
                'note.max' => 'Ghi chú không được vượt quá 500 ký tự',
                'failure_reason.required_if' => 'Lý do thất bại là bắt buộc khi trạng thái là giao thất bại, giao không thành công hoặc khách từ chối nhận',
                'failure_reason.string' => 'Lý do thất bại phải là chuỗi',
                'failure_reason.max' => 'Lý do thất bại không được vượt quá 255 ký tự',
                'tracking_code.required_if' => 'Mã vận đơn là bắt buộc khi trạng thái là đang giao',
                'tracking_code.string' => 'Mã vận đơn phải là chuỗi',
                'tracking_code.regex' => 'Mã vận đơn phải gồm 6 ký tự chữ cái hoặc số',
            ]);

            if ($validator->fails()) {
                Log::warning('Xác thực thất bại', [
                    'order_id' => $id,
                    'current_status' => $order->status,
                    'requested_status' => $request->input('status'),
                    'errors' => $validator->errors()->toArray(),
                    'payload' => $request->all()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Chuẩn bị dữ liệu để cập nhật
            $updateData = [
                'status' => $request->input('status'),
                'note' => $request->input('note'),
                'updated_at' => now(),
            ];

            // Xử lý failure_reason
            if (in_array($request->input('status'), ['failed', 'failed_delivery', 'rejected_by_customer'])) {
                $failureReason = trim(strip_tags($request->input('failure_reason', '')));
                if (empty($failureReason)) {
                    Log::warning('failure_reason rỗng hoặc không được gửi', [
                        'order_id' => $id,
                        'request_data' => $request->all(),
                        'json_payload' => $jsonPayload
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Lý do thất bại là bắt buộc.',
                        'errors' => ['failure_reason' => ['Lý do thất bại không được để trống.']]
                    ], 422);
                }
                $updateData['failure_reason'] = $failureReason;
                Log::info('Chuẩn bị cập nhật failure_reason', [
                    'order_id' => $id,
                    'failure_reason' => $failureReason
                ]);
            } else {
                $updateData['failure_reason'] = null;
                Log::info('Xóa failure_reason', [
                    'order_id' => $id,
                    'failure_reason' => null
                ]);
            }

            // Xử lý tracking_code nếu trạng thái là shipping
            if ($request->input('status') === 'shipping' && $request->filled('tracking_code')) {
                $order->shipping()->updateOrCreate(
                    ['order_id' => $order->id],
                    ['tracking_code' => $request->input('tracking_code'), 'status' => 'ready_to_pick']
                );
                Log::info('Cập nhật tracking_code', [
                    'order_id' => $id,
                    'tracking_code' => $request->input('tracking_code')
                ]);
            }

            // Cập nhật đơn hàng bằng truy vấn SQL trực tiếp
            $updated = DB::table('orders')
                ->where('id', $id)
                ->update($updateData);
            Log::info('Kết quả cập nhật đơn hàng', [
                'order_id' => $id,
                'updated' => $updated,
                'update_data' => $updateData
            ]);

            if ($updated === 0) {
                Log::warning('Không có bản ghi nào được cập nhật', [
                    'order_id' => $id,
                    'update_data' => $updateData
                ]);
                // Thử cập nhật từng trường riêng lẻ để debug
                $testUpdate = DB::table('orders')
                    ->where('id', $id)
                    ->update(['failure_reason' => $updateData['failure_reason'] ?? null]);
                Log::info('Kết quả thử cập nhật failure_reason riêng', [
                    'order_id' => $id,
                    'test_update' => $testUpdate,
                    'failure_reason' => $updateData['failure_reason'] ?? null
                ]);
            }

            // Làm mới đối tượng $order để lấy dữ liệu mới nhất
            $order = Order::with('user', 'shipping')->findOrFail($id);
            Log::info('Đơn hàng sau khi cập nhật', [
                'order_id' => $id,
                'current_status' => $order->status,
                'current_failure_reason' => $order->failure_reason,
                'user_id' => $order->user_id,
                'user_email' => $order->user ? $order->user->email : null,
                'warning_email_sent' => $order->user ? $order->user->warning_email_sent : null
            ]);

            // Kiểm tra dữ liệu trong cơ sở dữ liệu sau khi cập nhật
            $dbCheck = DB::table('orders')->where('id', $id)->first(['status', 'failure_reason', 'note']);
            Log::info('Dữ liệu từ cơ sở dữ liệu sau khi cập nhật', [
                'order_id' => $id,
                'db_status' => $dbCheck->status,
                'db_failure_reason' => $dbCheck->failure_reason,
                'db_note' => $dbCheck->note
            ]);

            if (isset($updateData['failure_reason']) && $dbCheck->failure_reason !== $updateData['failure_reason']) {
                Log::warning('failure_reason không khớp sau khi cập nhật', [
                    'order_id' => $id,
                    'expected_failure_reason' => $updateData['failure_reason'] ?? null,
                    'actual_failure_reason' => $dbCheck->failure_reason
                ]);
            }

            // Gửi email cảnh báo nếu cần
            $warningEmailSent = false;
            $warningEmailError = null;
            if ($request->input('status') === 'rejected_by_customer' && $order->user) {
                Log::info('Kiểm tra điều kiện gửi email cảnh báo', [
                    'order_id' => $id,
                    'user_id' => $order->user_id,
                    'email' => $order->user->email,
                    'warning_email_sent' => $order->user->warning_email_sent,
                    'failure_reason' => $order->failure_reason,
                    'rejected_orders_count' => Order::where('user_id', $order->user_id)
                        ->where('status', 'rejected_by_customer')
                        ->count()
                ]);

                if (!filter_var($order->user->email, FILTER_VALIDATE_EMAIL)) {
                    Log::warning('Email không hợp lệ', [
                        'order_id' => $id,
                        'user_id' => $order->user_id,
                        'email' => $order->user->email
                    ]);
                }

                if ($order->user->warning_email_sent == 0) {
                    // Đếm số đơn rejected_by_customer trước khi cập nhật trạng thái hiện tại
                    $rejectedOrdersCount = Order::where('user_id', $order->user_id)
                        ->where('status', 'rejected_by_customer')
                        ->where('id', '!=', $id) // Loại trừ đơn hàng hiện tại
                        ->count();
                    Log::info('Số đơn rejected_by_customer trước khi cập nhật', [
                        'order_id' => $id,
                        'user_id' => $order->user_id,
                        'rejected_orders_count' => $rejectedOrdersCount
                    ]);

                    // Nếu cập nhật thành công và đây là đơn rejected_by_customer đầu tiên
                    if ($rejectedOrdersCount == 0 && $updated > 0) {
                        try {
                            Mail::to($order->user->email)->send(new WarningRejectedOrder($order->user, $order));
                            $order->user->update(['warning_email_sent' => 1]);
                            $warningEmailSent = true;
                            Log::info('Gửi email cảnh báo thành công', [
                                'user_id' => $order->user_id,
                                'email' => $order->user->email,
                                'order_id' => $order->id,
                                'failure_reason' => $order->failure_reason
                            ]);
                        } catch (\Exception $e) {
                            $warningEmailError = $e->getMessage();
                            Log::warning('Lỗi gửi email cảnh báo: ' . $e->getMessage(), [
                                'user_id' => $order->user_id,
                                'order_id' => $order->id,
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                    } else {
                        Log::info('Không gửi email vì không phải đơn rejected_by_customer đầu tiên hoặc cập nhật thất bại', [
                            'order_id' => $id,
                            'rejected_orders_count' => $rejectedOrdersCount,
                            'updated' => $updated
                        ]);
                    }
                } else {
                    Log::info('Không gửi email do đã gửi trước đó', [
                        'order_id' => $id,
                        'warning_email_sent' => $order->user->warning_email_sent
                    ]);
                }
            } else {
                Log::info('Không gửi email cảnh báo vì trạng thái không phải rejected_by_customer hoặc user không tồn tại', [
                    'order_id' => $id,
                    'status' => $request->input('status'),
                    'user_exists' => $order->user ? true : false
                ]);
            }

            // Gửi email thông báo trạng thái
            try {
                if ($request->input('status') !== 'rejected_by_customer' && $order->user && $order->user->email) {
                    Mail::to($order->user->email)->send(new OrderStatusUpdatedMail($order, $order->status));
                    Log::info('Gửi email thông báo trạng thái thành công', [
                        'order_id' => $id,
                        'user_id' => $order->user_id,
                        'email' => $order->user->email
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Lỗi gửi mail thông báo trạng thái: ' . $e->getMessage(), [
                    'order_id' => $id,
                    'trace' => $e->getTraceAsString()
                ]);
            }

            $statusMessages = [
                'pending' => 'Đơn hàng đang chờ xử lý',
                'confirmed' => 'Đơn hàng đã được xác nhận',
                'processing' => 'Đơn hàng đang được xử lý',
                'shipped' => 'Đơn hàng đang được giao đến bạn',
                'delivered' => 'Đơn hàng đã được giao thành công',
                'cancelled' => 'Đơn hàng đã bị hủy',
                'failed' => 'Đơn hàng giao thất bại',
                'failed_delivery' => 'Đơn hàng giao không thành công',
                'rejected_by_customer' => 'Đơn hàng bị khách từ chối nhận',
            ];

            $responseData = [
                'success' => true,
                'message' => 'Cập nhật đơn hàng thành công',
                'status_message' => $statusMessages[$request->input('status')] ?? 'Trạng thái đơn hàng đã được cập nhật',
                'warning_email_sent' => $warningEmailSent,
                'warning_email_error' => $warningEmailError,
                'data' => [
                    'id' => $order->id,
                    'status' => $dbCheck->status,
                    'note' => $dbCheck->note,
                    'failure_reason' => $dbCheck->failure_reason,
                    'can_delete' => in_array($dbCheck->status, ['pending', 'cancelled', 'failed', 'failed_delivery', 'rejected_by_customer']),
                    'updated_at' => $order->updated_at->format('d/m/Y H:i:s')
                ]
            ];

            Log::info('Cập nhật đơn hàng thành công', $responseData);
            return response()->json($responseData);
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật đơn hàng', [
                'order_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all(),
                'raw_payload' => $rawPayload,
                'attributes' => isset($order) ? $order->getAttributes() : null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật đơn hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $order = Order::findOrFail($id);

            // Chỉ cho phép xóa đơn hàng ở trạng thái pending hoặc cancelled
            if (!in_array($order->status, ['pending', 'cancelled'])) {
                return response()->json([
                    'message' => 'Chỉ có thể xóa đơn hàng ở trạng thái chờ xử lý hoặc đã hủy'
                ], 400);
            }

            DB::beginTransaction();

            // Xóa các bản ghi liên quan
            $order->orderItems()->delete();
            $order->payments()->delete();
            $order->delete();

            DB::commit();

            return response()->json([
                'message' => 'Xóa đơn hàng thành công'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Có lỗi xảy ra khi xóa đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format order response
     */
    private function formatPrice($price)
    {
        return number_format($price, 0, '', ',');
    }

    protected function formatOrderResponse($order)
    {
        return [
            'id' => $order->id,
            'shipping' => $order->shipping ? [
                'tracking_code' => $order->shipping->tracking_code,
                'status' => $order->shipping->status,
                'estimated_delivery' => $order->shipping->estimated_delivery,
                'shipping_fee' => (int) ($order->shipping->shipping_fee ?? 0),
                'shipping_discount' => (int) ($order->shipping->shipping_discount ?? 0),
            ] : null,
            'user' => [
                'id' => $order->user->id,
                'name' => $order->user->name,
                'email' => $order->user->email
            ],
            'address' => $order->address ? [
                'id' => $order->address->id,
                'name' => $order->address->name,
                'phone' => $order->address->phone,
                'detail' => $order->address->detail,
                'province_id' => $order->address->province_id,
                'district_id' => $order->address->district_id,
                'ward_code' => $order->address->ward_code,
                'detail' => $order->address->detail,
            ] : null,
            'note' => $order->note ?? '',
            'status' => $order->status,
            'total_price' => (int) $order->total_price,
            'discount_price' => (int) $order->discount_price,
            'final_price' => (int) $order->final_price,
            'shipping_method' => $order->shipping_method,
            'created_at' => $order->created_at->format('d/m/Y H:i:s'),
            'updated_at' => $order->updated_at->format('d/m/Y H:i:s'),
            'order_items' => $order->orderItems->map(function ($item) {
                return [
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'thumbnail' => $item->product->thumbnail,
                        'slug' => $item->product->slug,
                        'stock' => $item->product->stock
                    ],
                    'variant' => $item->productVariant ? [
                        'id' => $item->productVariant->id,
                        'name' => $item->productVariant->name,
                        'thumbnail' => $item->productVariant->thumbnail ?? null,
                    ] : null,
                    'price' => (float)$item->price,
                    'quantity' => $item->quantity,
                    'price' => (int) $item->price,
                    'total' => (int) ($item->price * $item->quantity),
                ];
            })->toArray(),
            'payments' => $order->payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'method' => $payment->paymentMethod ? $payment->paymentMethod->name : null,
                    'amount' => (int) $payment->amount,
                    'status' => $payment->status,
                    'created_at' => $payment->created_at ? $payment->created_at->format('d/m/Y H:i') : null
                ];
            })->toArray(),
            'refund' => $order->refund ? [
                'id' => $order->refund->id,
                'order_id' => $order->refund->order_id,
                'amount' => (float)$order->refund->amount,
                'reason' => $order->refund->reason,
                'status' => $order->refund->status,
                'created_at' => $order->refund->created_at ? $order->refund->created_at->format('d/m/Y H:i') : null
            ] : null
        ];
    }

    public function requestRefund(Request $request, $id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        if ($order->refund) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng này đã có yêu cầu hoàn tiền'
            ], 400);
        }

        if (!in_array($order->status, ['failed', 'cancelled', 'returned'])) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng không đủ điều kiện để hoàn tiền'
            ], 400);
        }

        $request->validate([
            'reason' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0|max:' . ($order->final_price - ($order->shipping->shipping_fee ?? 0)),
            'status' => 'required|in:pending,approved,rejected'
        ]);

        $refund = Refund::create([
            'order_id' => $order->id,
            'amount' => $request->amount,
            'reason' => $request->reason,
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Yêu cầu hoàn tiền đã được gửi',
            'data' => [
                'id' => $refund->id,
                'order_id' => $refund->order_id,
                'amount' => (float)$refund->amount,
                'reason' => $refund->reason,
                'status' => $refund->status,
                'created_at' => $refund->created_at ? $refund->created_at->format('d/m/Y H:i') : null
            ]
        ], 200);
    }


    /**
     * Check if status transition is valid
     */
    private function isValidStatusTransition($currentStatus, $newStatus)
    {
        $allowedTransitions = [
            'pending' => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'shipped' => ['delivered', 'cancelled'],
            'delivered' => [],
            'cancelled' => []
        ];

        return in_array($newStatus, $allowedTransitions[$currentStatus] ?? []);
    }

    /**
     * Apply discount to order
     */
    public function applyDiscount(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'discount_code' => 'required|string|max:50'
            ], [
                'discount_code.required' => 'Mã giảm giá không được để trống',
                'discount_code.string' => 'Mã giảm giá phải là chuỗi ký tự',
                'discount_code.max' => 'Mã giảm giá không được vượt quá 50 ký tự'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            $order = Order::findOrFail($id);

            // Kiểm tra trạng thái đơn hàng
            if (!in_array($order->status, ['pending', 'processing'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể áp dụng mã giảm giá cho đơn hàng này'
                ], 400);
            }

            $result = $order->applyDiscount($request->discount_code);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'order_id' => $order->id,
                    'discount_amount' => number_format($result['discount_amount'], 0, '', ',') . ' đ',
                    'final_price' => number_format($result['final_price'], 0, '', ',') . ' đ'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove discount from order
     */
    public function removeDiscount($id)
    {
        try {
            $order = Order::findOrFail($id);

            // Kiểm tra trạng thái đơn hàng
            if (!in_array($order->status, ['pending', 'processing'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa mã giảm giá của đơn hàng này'
                ], 400);
            }

            $result = $order->removeDiscount();

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'order_id' => $order->id,
                    'final_price' => number_format($result['final_price'], 0, '', ',') . ' đ'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Thống kê dashboard cho admin
     */
    public function dashboardStats()
    {
        // Tổng người dùng
        $totalUsers = User::count();
        // Tổng đơn hàng
        $totalOrders = Order::count();
        // Tổng kênh bán hàng (giả sử là tổng số seller)
        $totalSellers = User::where('role', 'seller')->count();
        // Doanh thu từ người bán (tổng final_price các đơn hàng của seller, trạng thái delivered)
        $sellerRevenue = Order::whereHas('user', function ($q) {
            $q->where('role', 'seller');
        })->where('status', 'delivered')->sum('final_price');
        // Tổng doanh thu (tổng final_price các đơn hàng trạng thái delivered)
        $totalRevenue = Order::where('status', 'delivered')->sum('final_price');
        // Tổng thu thập (giả sử là tổng discount_price các đơn hàng delivered)
        $totalDiscount = Order::where('status', 'delivered')->sum('discount_price');

        return response()->json([
            'total_users' => $totalUsers,
            'total_orders' => $totalOrders,
            'total_sellers' => $totalSellers,
            'seller_revenue' => $sellerRevenue,
            'total_revenue' => $totalRevenue,
            'total_discount' => $totalDiscount,
        ]);
    }
}
