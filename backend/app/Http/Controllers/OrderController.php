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
use Illuminate\Support\Facades\Schema;
use App\Models\Notification;
use App\Models\NotificationRecipient;


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

            // Log payout processing để debug
            Log::info('Payout processing', [
                'order_id' => $order->id,
                'seller_id' => $sellerId,
                'order_status' => $orderStatus,
                'payout_id' => $order->payout_id,
                'payout_status' => $order->payout_status,
                'payout_amount' => $order->payout_amount,
            ]);

            GhnSyncLog::create([
                'order_id' => $orderId,
                'tracking_code' => $request->tracking_code,
                'ghn_status' => $ghnStatus,
                'success' => true,
                'message' => 'Đồng bộ trạng thái GHN thành công'
            ]);

            // Log GHN sync log creation để debug
            Log::info('GHN sync log created', [
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

            // Log GHN sync log error creation để debug
            Log::info('GHN sync log error created', [
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
                'user',
                'shipping',
                'orderItems.productVariant.product.seller',
                'payments.paymentMethod'
            ])->latest();

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by tracking code
            if ($request->has('tracking_code')) {
                $query->whereHas('shipping', function ($q) use ($request) {
                    $q->where('tracking_code', 'like', '%' . $request->tracking_code . '%');
                });
            }

            // Filter by date range
            if ($request->has('from_date') && $request->from_date) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }
            if ($request->has('to_date') && $request->to_date) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }

            // Filter by seller if user is seller
            $user = Auth::user();
            if ($user && $user->role === 'seller') {
                $seller = Seller::where('user_id', $user->id)->first();
                if ($seller) {
                    $query->whereHas('orderItems.productVariant.product', function ($q) use ($seller) {
                        $q->where('seller_id', $seller->id);
                    });
                }
            }

            // Paginate
            $perPage = $request->input('per_page', 10);
            $orders = $query->paginate($perPage);

            // Format response
            $formattedOrders = $orders->through(function ($order) {
                return [
                    'id' => $order->id,
                    'user' => [
                        'id' => $order->user->id,
                        'name' => $order->user->name,
                        'email' => $order->user->email
                    ],
                    'items' => $order->orderItems->map(function ($item) {
                            return [
                                'name' => $item->productVariant?->product?->name,
                                'variant' => $item->productVariant?->name,
                                'quantity' => $item->quantity,
                                'price' => $item->price
                            ];
                        }),
                    'shipping' => $order->shipping ? [
                        'tracking_code' => $order->shipping->tracking_code,
                        'shipping_method' => $order->shipping->shipping_method,
                        'shipping_fee' => $order->shipping->shipping_fee,
                        'estimated_delivery_date' => $order->shipping->estimated_delivery_date,
                         'address' => $order->shipping->address,
                        'phone' => $order->shipping->phone,
                    ] : null,
                    'status' => $order->status,
                    'payment_method' => $order->payments->first()?->paymentMethod?->name ?? null,
                    'payments' => $order->payments->map(function ($payment) {
                        return [
                            'id' => $payment->id,
                            'method' => $payment->paymentMethod ? $payment->paymentMethod->name : null,
                            'amount' => $payment->amount,
                            'status' => $payment->status,
                            'created_at' => $payment->created_at,
                        ];

                    }),
                    'payment_status' => $order->payment_status,
                    'subtotal' => $order->subtotal,
                    'shipping_fee' => $order->shipping_fee,
                    'discount_price' => $order->discount_price,
                    'final_price' => $order->final_price,
                    'note' => $order->note,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách đơn hàng thành công',
                'data' => $formattedOrders->items(),
                'meta' => [
                    'current_page' => $formattedOrders->currentPage(),
                    'from' => $formattedOrders->firstItem(),
                    'last_page' => $formattedOrders->lastPage(),
                    'path' => $formattedOrders->path(),
                    'per_page' => $formattedOrders->perPage(),
                    'to' => $formattedOrders->lastItem(),
                    'total' => $formattedOrders->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error in OrderController@index: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách đơn hàng: ' . $e->getMessage()
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

        // Chỉ trả về thông báo một lần duy nhất
        $message = '';
        if ($user->status === 'banned') {
            $message = 'Tài khoản của bạn đã bị khóa do có quá nhiều đơn hàng bị từ chối nhận.';
        } elseif ($rejectedOrdersCount >= 2) {
            $message = 'Bạn không thể sử dụng phương thức thanh toán COD vì có quá nhiều đơn hàng bị từ chối nhận.';
        } else {
            $message = 'Bạn có thể sử dụng phương thức thanh toán COD.';
        }
        return response()->json([
            'can_use_cod' => $rejectedOrdersCount < 2,
            'is_account_banned' => $user->status === 'banned',
            'rejected_orders_count' => $rejectedOrdersCount,
            'message' => $message
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
            'store_service_ids' => $request->store_service_ids,
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
            'items.*.seller_id' => 'required|exists:sellers,id',
            'items.*.service_id' => 'required|integer',
            'items.*.shipping_fee' => 'required|numeric|min:0', // Thêm validation cho shipping_fee trong items
            'payment_method' => 'required|string|in:COD,VNPAY,MOMO',
            'address_id' => 'required|exists:addresses,id',
            'store_shipping_fees' => 'required|array',
            'store_shipping_fees.*' => 'required|numeric|min:0',
            'store_service_ids' => 'required|array',
            'store_service_ids.*' => 'required|integer',
            'store_shipping_discounts' => 'nullable|array', // Thêm validation cho shipping discounts
            'store_shipping_discounts.*' => 'nullable|numeric|min:0',
            'store_product_discounts' => 'nullable|array', // Thêm validation cho product discounts
            'store_product_discounts.*' => 'nullable|numeric|min:0',
        ], [
            'store_shipping_fees.*.numeric' => 'Phí vận chuyển phải là số',
            'store_shipping_fees.*.min' => 'Phí vận chuyển phải lớn hơn hoặc bằng 0',
            'store_service_ids.*.exists' => 'Phương thức giao hàng cho cửa hàng không tồn tại',
            'items.*.service_id.exists' => 'Phương thức giao hàng không tồn tại trong items',
            'store_shipping_discounts.*.numeric' => 'Giảm giá vận chuyển phải là số',
            'store_shipping_discounts.*.min' => 'Giảm giá vận chuyển phải lớn hơn hoặc bằng 0',
            'store_product_discounts.*.numeric' => 'Giảm giá sản phẩm phải là số',
            'store_product_discounts.*.min' => 'Giảm giá sản phẩm phải lớn hơn hoặc bằng 0',
        ]);

        // Validation cho mutual exclusivity của discount
        if ($request->discount_ids && is_array($request->discount_ids)) {
            $shopDiscountCount = 0;
            $adminDiscountCount = 0;

            foreach ($request->discount_ids as $did) {
                $discount = \App\Models\Discount::find($did);
                if ($discount) {
                    if ($discount->discount_type === 'shipping_fee') {
                        continue; // Bỏ qua shipping discount
                    }

                    if ($discount->seller_id === null) {
                        $adminDiscountCount++;
                    } else {
                        $shopDiscountCount++;
                    }
                }
            }

            if ($shopDiscountCount > 0 && $adminDiscountCount > 0) {
                return response()->json([
                    'message' => 'Không thể áp dụng cùng lúc mã giảm giá của shop và admin. Vui lòng chọn một loại.',
                    'error_type' => 'mutual_exclusivity'
                ], 400);
            }
        }

        try {
            DB::beginTransaction();

            $items = $request->items;
            $products = Product::whereIn('id', collect($items)->pluck('product_id'))->get()->keyBy('id');
            $address = Address::find($request->address_id);
            $ghn = new GHNService();

            // Log request data để debug
            Log::info('Order creation request data', [
                'user_id' => $request->user()->id,
                'items_count' => count($items),
                'store_shipping_fees' => $request->store_shipping_fees,
                'store_service_ids' => $request->store_service_ids,
                'store_discounts' => $request->store_discounts,
                'store_shipping_discounts' => $request->store_shipping_discounts,
                'store_product_discounts' => $request->store_product_discounts,
                'discount_ids' => $request->discount_ids,
            ]);

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
                        throw new \Exception('Số lượng vượt quá tồn kho: ' . $item['product_id'] . ' (Có: ' + $stock + ', Yêu cầu: ' + $item['quantity']);
                    }
                }
            }

            $itemsBySeller = [];
            $isBuyNow = $request->is_buy_now ?? false;
            if ($isBuyNow) {
                $firstSellerId = $items[0]['seller_id'] ?? null;
                if (!$firstSellerId) {
                    throw new \Exception('Thiếu seller_id trong items khi mua ngay');
                }
                $itemsBySeller[$firstSellerId] = $items;
            } else {
                foreach ($items as $item) {
                    $sellerId = $item['seller_id'];
                    $itemsBySeller[$sellerId][] = $item;
                }
            }

            $orders = [];
            foreach ($itemsBySeller as $sellerId => $sellerItems) {
                $order = Order::create([
                    'user_id' => $request->user()->id,
                    'address_id' => $request->address_id,
                    'discount_id' => null,
                    'note' => $request->store_notes[$sellerId] ?? $request->note ?? '',
                    'status' => 'pending',
                    'total_price' => 0,
                    'discount_price' => 0,
                    'final_price' => 0,
                    'is_buy_now' => $isBuyNow,
                ]);

                // Log order creation để debug
                Log::info('Order created', [
                    'order_id' => $order->id,
                    'seller_id' => $sellerId,
                    'user_id' => $request->user()->id,
                    'address_id' => $request->address_id,
                    'is_buy_now' => $isBuyNow,
                ]);

                $orderItemIds = [];
                $totalPrice = 0;
                foreach ($sellerItems as $item) {
                    $orderItem = OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'product_variant_id' => $item['product_variant_id'] ?? null,
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);
                    $orderItemIds[] = $orderItem->id;
                    $totalPrice += $item['price'] * $item['quantity'];
                }

                // Log order items creation để debug
                Log::info('Order items created', [
                    'order_id' => $order->id,
                    'seller_id' => $sellerId,
                    'items_count' => count($sellerItems),
                    'total_price' => $totalPrice,
                    'order_item_ids' => $orderItemIds,
                ]);

                if (Schema::hasTable('refunds') && Schema::hasColumn('refunds', 'order_item_id')) {
                    $refund = Refund::whereIn('order_item_id', $orderItemIds)->first();
                    if ($refund) {
                        throw new \Exception('Một hoặc nhiều mặt hàng trong đơn hàng đã có yêu cầu hoàn tiền: order_item_id ' . $refund->order_item_id);
                    }
                }

                $discountPrice = 0;
                $appliedDiscountIds = [];
                $shopDiscount = null;
                $adminDiscount = null;
                $shippingDiscount = null;

                // Tìm tất cả discount được áp dụng
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

                // Log discount processing để debug
                Log::info('Discount processing', [
                    'order_id' => $order->id,
                    'seller_id' => $sellerId,
                    'discount_ids' => $request->discount_ids,
                    'shipping_discount' => $shippingDiscount ? $shippingDiscount->id : null,
                    'shop_discount' => $shopDiscount ? $shopDiscount->id : null,
                    'admin_discount' => $adminDiscount ? $adminDiscount->id : null,
                ]);

                // Kiểm tra mutual exclusivity: chỉ áp dụng 1 loại discount (shop hoặc admin)
                $usedDiscount = null;
                if ($shopDiscount && $adminDiscount) {
                    // Nếu có cả 2, ưu tiên shop discount
                    $usedDiscount = $shopDiscount;
                    Log::info("Cả shop và admin discount đều được áp dụng, ưu tiên shop discount", [
                        'order_id' => $order->id,
                        'shop_discount_id' => $shopDiscount->id,
                        'admin_discount_id' => $adminDiscount->id,
                        'selected_discount_id' => $usedDiscount->id
                    ]);
                } elseif ($shopDiscount) {
                    $usedDiscount = $shopDiscount;
                } elseif ($adminDiscount) {
                    $usedDiscount = $adminDiscount;
                }

                // Log discount selection để debug
                Log::info('Discount selection', [
                    'order_id' => $order->id,
                    'seller_id' => $sellerId,
                    'used_discount' => $usedDiscount ? [
                        'id' => $usedDiscount->id,
                        'type' => $usedDiscount->discount_type,
                        'value' => $usedDiscount->discount_value,
                        'seller_id' => $usedDiscount->seller_id,
                    ] : null,
                ]);

                // Tính toán discount price cho sản phẩm
                if ($usedDiscount) {
                    if ($usedDiscount->discount_type === 'percentage') {
                        $discountPrice = $totalPrice * ($usedDiscount->discount_value / 100);
                    } else {
                        $discountPrice = $usedDiscount->discount_value;
                    }

                    // Đảm bảo discount không vượt quá total price
                    $discountPrice = min($discountPrice, $totalPrice);

                    Log::info("Áp dụng discount cho order", [
                        'order_id' => $order->id,
                        'discount_id' => $usedDiscount->id,
                        'discount_type' => $usedDiscount->discount_type,
                        'discount_value' => $usedDiscount->discount_value,
                        'total_price' => $totalPrice,
                        'calculated_discount' => $discountPrice,
                        'seller_id' => $usedDiscount->seller_id
                    ]);

                    // Tăng used_count cho discount
                    if ($usedDiscount) {
                        $usedDiscount->increment('used_count');
                    }
                    $appliedDiscountIds['product'] = $usedDiscount->id;
                }

                // Log final discount calculation để debug
                Log::info('Final discount calculation', [
                    'order_id' => $order->id,
                    'seller_id' => $sellerId,
                    'total_price' => $totalPrice,
                    'discount_price' => $discountPrice,
                    'applied_discount_ids' => $appliedDiscountIds,
                ]);

                // Xử lý shipping discount
                if ($shippingDiscount) {
                    $shippingDiscount->increment('used_count');
                    $appliedDiscountIds['shipping'] = $shippingDiscount->id;
                }

                // Log shipping discount processing để debug
                Log::info('Shipping discount processing', [
                    'order_id' => $order->id,
                    'seller_id' => $sellerId,
                    'shipping_discount' => $shippingDiscount ? [
                        'id' => $shippingDiscount->id,
                        'type' => $shippingDiscount->discount_type,
                        'value' => $shippingDiscount->discount_value,
                    ] : null,
                    'applied_discount_ids' => $appliedDiscountIds,
                ]);

                // Frontend đã tính và gửi discount, backend chỉ cần sử dụng
                $shopDiscount = 0;
                if ($request->has('store_discounts') && isset($request->store_discounts[$sellerId])) {
                    $shopDiscount = floatval($request->store_discounts[$sellerId]);
                }
                $finalPrice = $totalPrice - $shopDiscount;
                $finalPrice = max($finalPrice, 0);

                // Log store discount processing để debug
                Log::info('Store discount processing', [
                    'order_id' => $order->id,
                    'seller_id' => $sellerId,
                    'store_discount' => $shopDiscount,
                    'total_price' => $totalPrice,
                    'final_price' => $finalPrice,
                    'request_store_discounts' => $request->store_discounts ?? 'not_set',
                    'request_store_shipping_discounts' => $request->store_shipping_discounts ?? 'not_set',
                    'request_store_product_discounts' => $request->store_product_discounts ?? 'not_set',
                ]);

                $shopServiceId = (int) ($request->store_service_ids[$sellerId] ?? 0);

                $shippingMethod = ShippingMethod::where('carrier', 'GHN')
                    ->where('id', $shopServiceId)
                    ->first();

                if (!$shippingMethod) {
                    $shippingMethod = ShippingMethod::create([
                        'carrier'            => 'GHN',
                        'id' => $shopServiceId,
                        'name'               => match ($shopServiceId) {
                            53321 => 'GHN Tiêu chuẩn',
                            53322 => 'GHN Nhanh',
                            100039 => 'GHN Hàng Nặng',
                            default => 'GHN Service ' . $shopServiceId,
                        },
                        'estimated_days'     => 3,
                        'status'             => 'active',
                    ]);
                }

                // Log shipping method processing để debug
                Log::info('Shipping method processing', [
                    'order_id' => $order->id,
                    'seller_id' => $sellerId,
                    'shop_service_id' => $shopServiceId,
                    'shipping_method_id' => $shippingMethod->id,
                    'shipping_method_name' => $shippingMethod->name,
                    'carrier' => $shippingMethod->carrier,
                ]);

                $shippingDiscountValue = 0;
                // Kiểm tra store_shipping_discounts trước, nếu không có thì tính từ store_discounts
                if ($request->has('store_shipping_discounts') && isset($request->store_shipping_discounts[$sellerId])) {
                    $shippingDiscountValue = floatval($request->store_shipping_discounts[$sellerId]);
                    Log::info("Sử dụng store_shipping_discounts trực tiếp", [
                        'order_id' => $order->id,
                        'seller_id' => $sellerId,
                        'shipping_discount_value' => $shippingDiscountValue
                    ]);
                } elseif ($request->has('store_discounts') && isset($request->store_discounts[$sellerId])) {
                    // Nếu có store_discounts, cần tách riêng shipping discount
                    $totalStoreDiscount = floatval($request->store_discounts[$sellerId]);
                    $productDiscount = 0;
                    
                    // Tính product discount từ appliedDiscountIds
                    if (isset($appliedDiscountIds['product'])) {
                        $productDiscountId = $appliedDiscountIds['product'];
                        $productDiscountObj = Discount::find($productDiscountId);
                        if ($productDiscountObj) {
                            if ($productDiscountObj->discount_type === 'percentage') {
                                $productDiscount = $totalPrice * ($productDiscountObj->discount_value / 100);
                            } else {
                                $productDiscount = $productDiscountObj->discount_value;
                            }
                            $productDiscount = min($productDiscount, $totalPrice);
                        }
                    }
                    
                    // Shipping discount = total store discount - product discount
                    $shippingDiscountValue = max(0, $totalStoreDiscount - $productDiscount);
                    Log::info("Tính shipping discount từ store_discounts", [
                        'order_id' => $order->id,
                        'seller_id' => $sellerId,
                        'total_store_discount' => $totalStoreDiscount,
                        'product_discount' => $productDiscount,
                        'calculated_shipping_discount' => $shippingDiscountValue
                    ]);
                }
                
                $shippingFee = $request->store_shipping_fees[$sellerId] ?? 0;
                
                // Debug log để kiểm tra shipping discount
                Log::info("Shipping discount debug", [
                    'order_id' => $order->id,
                    'seller_id' => $sellerId,
                    'request_has_store_shipping_discounts' => $request->has('store_shipping_discounts'),
                    'request_has_store_discounts' => $request->has('store_discounts'),
                    'request_has_store_product_discounts' => $request->has('store_product_discounts'),
                    'store_shipping_discounts' => $request->store_shipping_discounts ?? 'not_set',
                    'store_discounts' => $request->store_discounts ?? 'not_set',
                    'store_product_discounts' => $request->store_product_discounts ?? 'not_set',
                    'applied_discount_ids' => $appliedDiscountIds,
                    'shipping_discount_value' => $shippingDiscountValue,
                    'original_shipping_fee' => $shippingFee,
                    'final_shipping_fee' => max(0, $shippingFee - $shippingDiscountValue),
                ]);
                // Trừ discount phí ship đã chia đều cho shop này
                $shippingFee = max(0, $shippingFee - $shippingDiscountValue);
                // Bỏ kiểm tra khớp với shippingMethod->cost
                Log::info("Sử dụng phí vận chuyển từ request cho seller_id: {$sellerId}", [
                    'shipping_fee' => $shippingFee,
                    'service_id' => $shopServiceId,
                ]);

                $trackingCode = null;
                $estimatedDelivery = now()->addDays($shippingMethod->estimated_days ?? 3);
                $maxRetries = 2;
                $retryCount = 0;

                while ($retryCount <= $maxRetries) {
                    try {
                        $ghnOrder = $ghn->createShippingOrder($order, $address, $shopServiceId, $request->payment_method);
                        $trackingCode = $ghnOrder['order_code'] ?? null;
                        $estimatedDelivery = $ghnOrder['expected_delivery_time'] ?? $estimatedDelivery;
                        if (!$trackingCode) {
                            Log::warning('GHN did not return tracking code', [
                                'order_id' => $order->id,
                                'ghn_response' => $ghnOrder,
                            ]);
                            throw new \Exception('GHN không trả về mã vận đơn');
                        }
                        Log::info('GHN createShippingOrder Success', [
                            'order_id' => $order->id,
                            'tracking_code' => $trackingCode,
                            'estimated_delivery' => $estimatedDelivery,
                        ]);
                        break;
                    } catch (\Exception $e) {
                        $retryCount++;
                        if ($retryCount > $maxRetries) {
                            Log::error('GHN API error in OrderController::store after retries', [
                                'order_id' => $order->id,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString(),
                            ]);
                            throw new \Exception("Không thể tạo đơn vận chuyển GHN sau {$maxRetries} lần thử: {$e->getMessage()}");
                        }
                        Log::info("Thử lại GHN createShippingOrder lần {$retryCount}", [
                            'order_id' => $order->id,
                            'error' => $e->getMessage(),
                        ]);
                        sleep(1);
                    }
                }

                // Log GHN order creation để debug
                Log::info('GHN order creation completed', [
                    'order_id' => $order->id,
                    'seller_id' => $sellerId,
                    'tracking_code' => $trackingCode,
                    'estimated_delivery' => $estimatedDelivery,
                    'retry_count' => $retryCount,
                ]);

                // Lưu discount IDs dưới dạng JSON
                if (!empty($appliedDiscountIds)) {
                    $order->discount_id = $appliedDiscountIds;
                }

                // Log discount IDs saving để debug
                Log::info('Discount IDs saving', [
                    'order_id' => $order->id,
                    'seller_id' => $sellerId,
                    'applied_discount_ids' => $appliedDiscountIds,
                    'discount_id_saved' => $order->discount_id,
                ]);

                $shipping = Shipping::create([
                    'order_id' => $order->id,
                    'shipping_method_id' => $shippingMethod->id,
                    'estimated_delivery' => $estimatedDelivery,
                    'shipping_fee' => $shippingFee,
                    'shipping_discount' => $shippingDiscountValue,
                    'tracking_code' => $trackingCode,
                    'status' => 'pending',
                ]);

                // Log shipping record để debug
                Log::info('Shipping record created', [
                    'order_id' => $order->id,
                    'seller_id' => $sellerId,
                    'shipping_fee' => $shippingFee,
                    'shipping_discount' => $shippingDiscountValue,
                    'final_shipping_fee' => $shippingFee - $shippingDiscountValue,
                    'tracking_code' => $trackingCode,
                ]);

                $order->update([
                    'total_price' => $totalPrice,
                    'discount_price' => $shopDiscount,
                    'final_price' => $finalPrice + $shippingFee + $shippingDiscountValue,
                ]);

                // Log order update để debug
                Log::info('Order updated', [
                    'order_id' => $order->id,
                    'seller_id' => $sellerId,
                    'total_price' => $totalPrice,
                    'discount_price' => $shopDiscount,
                    'shipping_fee' => $shippingFee,
                    'shipping_discount' => $shippingDiscountValue,
                    'final_price' => $finalPrice + $shippingFee + $shippingDiscountValue,
                ]);

                $paymentMethod = PaymentMethod::firstOrCreate(
                    ['name' => $request->payment_method],
                    ['status' => 'active']
                );

                $totalPaymentAmount = $finalPrice + $shippingFee + $shippingDiscountValue;

                Payment::create([
                    'order_id' => $order->id,
                    'payment_method_id' => $paymentMethod->id,
                    'amount' => $totalPaymentAmount,
                    'status' => 'pending'
                ]);

                // Log payment record để debug
                Log::info('Payment record created', [
                    'order_id' => $order->id,
                    'seller_id' => $sellerId,
                    'payment_method' => $request->payment_method,
                    'amount' => $totalPaymentAmount,
                    'breakdown' => [
                        'final_price' => $finalPrice,
                        'shipping_fee' => $shippingFee,
                        'shipping_discount' => $shippingDiscountValue,
                    ]
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

                $seller = Seller::find($sellerId);
                if ($seller && $seller->user_id) {
                    $notification = Notification::create([
                        'title' => 'Bạn có đơn hàng mới',
                        'content' => "Bạn vừa nhận được một đơn hàng mới từ người dùng.",
                        'type' => 'system',
                        'link' => "seller/orders/list-order",
                        'user_id' => $request->user()->id,
                        'from_role' => 'customer',
                        'channels' => json_encode(['dashboard']),
                        'status' => 'sent',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    NotificationRecipient::create([
                        'notification_id' => $notification->id,
                        'user_id' => $seller->user_id,
                        'is_read' => false,
                        'is_hidden' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Log notification creation để debug
                    Log::info('Notification created for seller', [
                        'order_id' => $order->id,
                        'seller_id' => $sellerId,
                        'seller_user_id' => $seller->user_id,
                        'notification_id' => $notification->id,
                    ]);
                }

                $orders[] = $this->formatOrderResponse($order);

                // Log order response formatting để debug
                Log::info('Order response formatted', [
                    'order_id' => $order->id,
                    'seller_id' => $sellerId,
                    'formatted_order' => [
                        'id' => $order->id,
                        'final_price' => $order->final_price,
                        'shipping' => $order->shipping ? [
                            'shipping_fee' => $order->shipping->shipping_fee,
                            'shipping_discount' => $order->shipping->shipping_discount,
                        ] : null,
                    ]
                ]);
            }

            DB::commit();

            // Log final order data để debug
            Log::info('Orders created successfully', [
                'orders_count' => count($orders),
                'orders_summary' => collect($orders)->map(function($order) {
                    return [
                        'id' => $order['id'],
                        'final_price' => $order['final_price'],
                        'shipping' => $order['shipping'] ? [
                            'shipping_fee' => $order['shipping']['shipping_fee'],
                            'shipping_discount' => $order['shipping']['shipping_discount'],
                        ] : null,
                    ];
                })->toArray()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đơn hàng đã được tạo thành công',
                'orders' => $orders
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'request_has_store_shipping_discounts' => $request->has('store_shipping_discounts'),
                'request_has_store_discounts' => $request->has('store_discounts'),
                'request_has_store_product_discounts' => $request->has('store_product_discounts'),
            ]);
            return response()->json([
                'message' => 'Có lỗi xảy ra khi tạo đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    public function upsertShippingMethod(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
                'name' => 'required|string|max:255',
                'carrier' => 'required|string|max:255',
                'estimated_days' => 'required|integer|min:1',
                'cost' => 'required|numeric|min:0',
                'status' => 'required|in:active,inactive',
            ]);

            // Xác thực service_id với GHN
            $ghn = new GHNService();
            $seller = Seller::where('user_id', Auth::id())->first();
            if ($seller) {
                $services = $ghn->getServices($seller->id, $seller->district_id);
                $validServiceIds = array_column($services['data'] ?? [], 'service_id');
                if (!in_array($request->id, $validServiceIds)) {
                    Log::warning("Invalid service_id: {$request->id} for seller {$seller->id}", [
                        'available_services' => $validServiceIds,
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => "Dịch vụ vận chuyển service_id {$request->id} không được hỗ trợ",
                    ], 400);
                }
            }

            // So sánh cost với GHN (nếu có thông tin để tính phí)
            if ($seller) {
                try {
                    $feeData = [
                        'seller_id' => $seller->id,
                        'from_district_id' => $seller->district_id,
                        'from_ward_code' => $seller->ward_id,
                        'to_district_id' => $seller->district_id, // Dùng cùng quận để kiểm tra
                        'to_ward_code' => $seller->ward_id,
                        'service_id' => $request->id,
                        'weight' => 1000,
                        'length' => 25,
                        'width' => 25,
                        'height' => 25,
                    ];
                    $ghnFee = $ghn->calculateFee($feeData)['data']['total'] / 100;
                    if (abs($ghnFee - $request->cost) > 0.01) {
                        Log::warning("Phí vận chuyển không khớp với GHN", [
                            'service_id' => $request->id,
                            'request_cost' => $request->cost,
                            'ghn_cost' => $ghnFee,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning("Không thể kiểm tra phí GHN: {$e->getMessage()}", [
                        'service_id' => $request->id,
                    ]);
                }
            }

            $shippingMethod = ShippingMethod::updateOrCreate(
                ['id' => $request->id],
                [
                    'name' => $request->name,
                    'carrier' => $request->carrier,
                    'estimated_days' => $request->estimated_days,
                    'cost' => $request->cost,
                    'status' => $request->status,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật hoặc tạo phương thức giao hàng thành công',
                'data' => $shippingMethod,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật/tạo shipping method: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật/tạo phương thức giao hàng',
                'error' => $e->getMessage(),
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
                'shipping',
                'refund'
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
                'error' => env('APP_DEBUG', false) ? $e->getMessage() : null
            ], 500);
        }
    }

    public function GetShipping(Request $request)
    {
        try {
            $shippingMethods = ShippingMethod::where('status', 'active')->get(['id', 'name', 'cost', 'estimated_days']);

            if ($shippingMethods->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Không tìm thấy phương thức giao hàng nào'
                ], 200);
            }

            return response()->json([
                'success' => true,
                'data' => $shippingMethods->map(function ($method) {
                    return [
                        'id' => $method->id,
                        'name' => $method->name,
                        'cost' => (float) $method->cost,
                        'estimated_days' => (int) $method->estimated_days,
                    ];
                })->toArray(),
                'message' => 'Lấy danh sách phương thức giao hàng thành công'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi lấy danh sách phương thức giao hàng: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách phương thức giao hàng',
                'error' => $e->getMessage(),
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
            // Lưu trạng thái cũ trước khi cập nhật
            $oldStatus = $order->status;

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

            // Tự động tạo payout cho từng shop khi đơn hàng chuyển sang delivered
            if ($oldStatus !== 'delivered' && $order->status === 'delivered') {
                // Lấy tất cả seller_id trong order
                $sellerIds = $order->orderItems->pluck('product.seller_id')->unique()->filter();
                foreach ($sellerIds as $sellerId) {
                    // Kiểm tra đã có payout cho order + seller này chưa
                    $existing = \App\Models\Payout::where('order_id', $order->id)
                        ->where('seller_id', $sellerId)
                        ->first();
                    if (!$existing) {
                        // Tính tổng tiền cho seller này trong order
                        $sellerItems = $order->orderItems->filter(function ($item) use ($sellerId) {
                            return $item->product && $item->product->seller_id == $sellerId;
                        });
                        $totalPrice = $sellerItems->sum(function ($item) {
                            return $item->price * $item->quantity;
                        });
                        $amount = max($totalPrice * 0.95, 0); // 5% admin fee
                        \App\Models\Payout::create([
                            'order_id' => $order->id,
                            'seller_id' => $sellerId,
                            'amount' => $amount,
                            'status' => 'pending',
                            'note' => 'Payout tự động cho đơn hàng ' . ($order->shipping?->tracking_code ?? $order->id),
                        ]);
                    }
                }
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

            $statusText = $statusMessages[$order->status] ?? 'Đơn hàng đã được cập nhật';

            // Tạo bản ghi notification
            $notification = Notification::create([
                'title' => 'Cập nhật trạng thái đơn hàng',
                'content' => "Đơn hàng #{$order->id} của bạn hiện đang ở trạng thái: {$statusText}.",
                'type' => 'order',
                'link' => "users/orders",
                'user_id' => $order->user_id,
                'from_role' => 'system',
                'channels' => json_encode(['dashboard']),
                'status' => 'sent',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Gán người nhận
            NotificationRecipient::create([
                'notification_id' => $notification->id,
                'user_id' => $order->user->id,
                'is_read' => false,
                'is_hidden' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

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
                $price = (float) ($item->price ?? 0);
                return [
                    'id' => $item->id,
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
                    'quantity' => $item->quantity,
                    'price' => $price,
                    'total' => $price * $item->quantity,
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
                'bank_account_number' => $order->refund->bank_account_number,
                'bank_name' => $order->refund->bank_name,
                'status' => $order->refund->status,
                'created_at' => $order->refund->created_at ? $order->refund->created_at->toISOString() : null,
            ] : null
        ];
    }

    public function requestRefund(Request $request, $id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        // Kiểm tra xem đơn hàng đã có yêu cầu hoàn tiền cho bất kỳ order_item nào chưa
        $orderItemIds = $order->orderItems()->pluck('id');
        $existingRefund = Refund::whereIn('order_item_id', $orderItemIds)->first();
        if ($existingRefund) {
            return response()->json([
                'success' => false,
                'message' => 'Một hoặc nhiều mặt hàng trong đơn hàng này đã có yêu cầu hoàn tiền'
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
            'status' => 'required|in:pending,approved,rejected',
            'order_item_id' => 'required|exists:order_items,id', // Thêm validate cho order_item_id
        ]);

        $orderItem = OrderItem::where('order_id', $order->id)
            ->where('id', $request->order_item_id)
            ->firstOrFail();

        $refund = Refund::create([
            'order_item_id' => $orderItem->id,
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'reason' => $request->reason,
            'status' => $request->status,
            'images' => $request->images ?? null,
            'admin_note' => $request->admin_note ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Yêu cầu hoàn tiền đã được gửi',
            'data' => [
                'id' => $refund->id,
                'order_item_id' => $refund->order_item_id,
                'user_id' => $refund->user_id,
                'amount' => (float) $refund->amount,
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
            'pending' => ['processing', 'shipping', 'cancelled'], // Cho phép sang processing, shipping, cancelled
            'processing' => ['shipping', 'shipped', 'delivered', 'cancelled'], // Cho phép sang shipping, shipped, delivered, cancelled
            'shipping' => ['delivered', 'cancelled'],
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

    /**
     * Lấy danh sách đơn hàng cho admin dashboard
     */
    public function adminList(Request $request)
    {
        try {
            $query = Order::with([
                'shipping',
                'user',
                'orderItems.productVariant.product',
                'payments.paymentMethod'
            ])->latest();

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by tracking code
            if ($request->has('tracking_code')) {
                $query->whereHas('shipping', function ($q) use ($request) {
                    $q->where('tracking_code', 'like', '%' . $request->tracking_code . '%');
                });
            }

            // Filter by date range
            if ($request->has('from_date')) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }
            if ($request->has('to_date')) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }

            // Paginate
            $perPage = $request->input('per_page', 10);
            $orders = $query->paginate($perPage);

            // Format response
            $formattedOrders = $orders->through(function ($order) {
                // Lấy seller_id từ sản phẩm đầu tiên trong order
                $sellerId = null;
                if ($order->orderItems && $order->orderItems->count() > 0) {
                    $firstProduct = $order->orderItems->first()->product;
                    $sellerId = $firstProduct ? $firstProduct->seller_id : null;
                }
                // Lấy payout cho seller này (nếu có)
                $payout = null;
                if ($sellerId) {
                    $payout = \App\Models\Payout::where('order_id', $order->id)
                        ->where('seller_id', $sellerId)
                        ->first();
                }
                return [
                    'id' => $order->id,
                    'user' => [
                        'id' => $order->user->id,
                        'name' => $order->user->name,
                        'email' => $order->user->email
                    ],
                    'shipping' => $order->shipping ? [
                        'tracking_code' => $order->shipping->tracking_code,
                        'shipping_method' => $order->shipping->shipping_method,
                        'shipping_fee' => $order->shipping->shipping_fee,
                        'estimated_delivery_date' => $order->shipping->estimated_delivery_date,
                    ] : null,
                    'status' => $order->status,
                    'payment_method' => $order->payments->first()?->paymentMethod?->name ?? null,
                    'payment_status' => $order->payment_status,
                    'subtotal' => $order->subtotal,
                    'shipping_fee' => $order->shipping_fee,
                    'discount_price' => $order->discount_price,
                    'final_price' => $order->final_price,
                    'note' => $order->note,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                    'shop_id' => $sellerId,
                    'payout_id' => $payout ? $payout->id : null,
                    'payout_amount' => $payout ? (float)$payout->amount : 0,
                    'payout_status' => $payout ? $payout->status : null,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách đơn hàng thành công',
                'data' => $formattedOrders->items(),
                'meta' => [
                    'current_page' => $formattedOrders->currentPage(),
                    'from' => $formattedOrders->firstItem(),
                    'last_page' => $formattedOrders->lastPage(),
                    'path' => $formattedOrders->path(),
                    'per_page' => $formattedOrders->perPage(),
                    'to' => $formattedOrders->lastItem(),
                    'total' => $formattedOrders->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error in OrderController@adminList: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách sellers cho admin dashboard
     */
    public function adminSellerList()
    {
        try {
            $sellers = \App\Models\Seller::with(['user' => function ($q) {
                $q->select('id', 'name', 'email');
            }])
                ->select('id', 'user_id', 'store_name', 'created_at')
                ->latest()
                ->get()
                ->map(function ($seller) {
                    return [
                        'id' => $seller->id,
                        'store_name' => $seller->store_name ?: 'Shop #' . $seller->id,
                        'user' => [
                            'id' => $seller->user->id,
                            'name' => $seller->user->name ?: $seller->user->email,
                            'email' => $seller->user->email
                        ],
                        'created_at' => $seller->created_at
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $sellers,
                'message' => 'Lấy danh sách sellers thành công'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in adminSellerList: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách sellers: ' . $e->getMessage()
            ], 500);
        }
    }


 public function reorderDetail($id)
{
    try {
        $user = auth()->user();

        $order = Order::with([
            'orderItems.product:id,name,slug',
            'orderItems.productVariant:id,product_id,thumbnail,quantity,sku,price,sale_price'
        ])
        ->where('id', $id)
        ->where('user_id', $user->id)
        ->firstOrFail();

        $item = $order->orderItems->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sản phẩm trong đơn hàng!'
            ], 404);
        }

        $product = optional($item->product);
        $variant = optional($item->productVariant);

        return response()->json([
            'success' => true,
            'data' => [
                'product_id'         => $product->id,
                'product_name'       => $product->name,
                'product_slug'       => $product->slug,
                'product_thumbnail'  => $product->thumbnail,
                'variant_id'         => $variant->id,
                'variant_name'       => $variant->name,
                'variant_thumbnail'  => $variant->thumbnail,
                'variant_stock'      => (int) $variant->quantity,
                'variant_sku'        => $variant->sku,
                'variant_price'      => $variant->price,
                'variant_sale_price' => $variant->sale_price,
                'quantity'           => $item->quantity
            ]
        ], 200);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy đơn hàng!'
        ], 404);

    } catch (\Exception $e) {
        \Log::error('Reorder detail error', [
            'order_id' => $id,
            'user_id' => auth()->id(),
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
        ], 500);
    }
}

public function printInvoice($id)
{
    try {
        $user = auth()->user();

        $query = Order::with([
            'orderItems.product',
            'orderItems.productVariant',
            'user',
            'address',
            'payments.paymentMethod',
            'shipping'
        ])->where('id', $id);

        if ($user->role === 'user') {
            $query->where('user_id', $user->id);
        }

        $order = $query->firstOrFail();

       $token = env('GHN_TOKEN');
$apiUrl = rtrim(env('GHN_API_URL'), '/');

// Hàm tiện ích để gọi GHN API
function ghnGet($url, $params = [])
{
    return Http::withHeaders([
        'Token' => env('GHN_TOKEN')
    ])->get($url, $params);
}

// Lấy tên tỉnh
$provinceName = null;
if (!empty($order->address?->province_id)) {
    $res = ghnGet("$apiUrl/master-data/province");
    if ($res->successful()) {
        $province = collect($res->json('data'))
            ->firstWhere('ProvinceID', (int)$order->address->province_id);
        $provinceName = $province['ProvinceName'] ?? null;
    }
}

// Lấy tên quận/huyện
$districtName = null;
if (!empty($order->address?->district_id)) {
    $res = Http::withHeaders([
        'Token' => $token
    ])->post("$apiUrl/master-data/district", [
        'province_id' => (int)$order->address->province_id
    ]);
    if ($res->successful()) {
        $district = collect($res->json('data'))
            ->firstWhere('DistrictID', (int)$order->address->district_id);
        $districtName = $district['DistrictName'] ?? null;
    }
}

// Lấy tên phường/xã
$wardName = null;
if (!empty($order->address?->ward_code)) {
    $res = Http::withHeaders([
        'Token' => $token
    ])->post("$apiUrl/master-data/ward", [
        'district_id' => (int)$order->address->district_id
    ]);
    if ($res->successful()) {
        $ward = collect($res->json('data'))
            ->firstWhere('WardCode', (string)$order->address->ward_code);
        $wardName = $ward['WardName'] ?? null;
    }
}

// Gắn vào dữ liệu trả về
$invoiceData['customer']['province_name'] = $provinceName;
$invoiceData['customer']['district_name'] = $districtName;
$invoiceData['customer']['ward_name'] = $wardName;



        $invoiceData = [
            'order_id' => $order->id,
            'customer' => [
                'name' => $order->user->name,
                'phone' => $order->address->phone ?? $order->user->phone ?? null,
                'address_detail' => $order->address->detail ?? null,
                'province_name' => $provinceName,
                'district_name' => $districtName,
                'ward_name' => $wardName,
            ],
            'items' => $order->orderItems->map(function ($item) {
                return [
                    'product_name' => $item->product->name,
                    'variant' => $item->productVariant->name ?? null,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->price * $item->quantity,
                ];
            }),
            'subtotal' => $order->subtotal,
            'shipping_fee' => $order->shipping_fee,
            'discount' => $order->discount_price,
            'final_price' => $order->final_price,
            'payment_method' => $order->payments->first()?->paymentMethod?->name ?? null,
            'status' => $order->status,
            'created_at' => $order->created_at->format('d/m/Y H:i'),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Lấy thông tin hóa đơn thành công',
            'data' => $invoiceData
        ], 200);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy đơn hàng'
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Có lỗi xảy ra khi lấy hóa đơn',
            'error' => env('APP_DEBUG', false) ? $e->getMessage() : null
        ], 500);
    }
}





}
