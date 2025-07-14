<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Discount;
use App\Models\DiscountUser;
use App\Models\Shipping;
use App\Models\ShippingMethod;
use App\Models\Address;
use App\Services\GHNService;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Mail\OrderStatusUpdatedMail;
use App\Mail\OrderSuccessMail;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
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
            $query = Order::with(['orderItems.product', 'orderItems.productVariant', 'user', 'address', 'payments', 'shipping']);

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

            // Lọc theo phương thức thanh toán
            if ($request->has('payment_method') && !empty($request->payment_method)) {
                $query->whereHas('payments.paymentMethod', function ($q) use ($request) {
                    $q->where('name', $request->payment_method);
                });
            }

            // Sắp xếp
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');

            // Validate sort columns to prevent SQL injection
            $allowedSortColumns = ['created_at', 'id', 'status', 'total_price'];
            $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'created_at';

            $query->orderBy($sortBy, $sortOrder);

            // Phân trang
            $perPage = (int)$request->input('per_page', 10);
            // Ensure perPage is between 1 and 100
            $perPage = max(1, min(100, $perPage));

            $orders = $query->paginate($perPage);

            if ($orders->isEmpty()) {
                return response()->json([
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
                'data' => $orders->map(function ($order) {
                    $payout = \App\Models\Payout::where('order_id', $order->id)->first();
                    return [
                        'id' => $order->id,
                        'shipping' => $order->shipping ? [
                            'tracking_code' => $order->shipping->tracking_code,
                            'status' => $order->shipping->status,
                            'estimated_delivery' => $order->shipping->estimated_delivery,
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
                        ] : null,
                        'note' => $order->note ?? '',
                        'status' => $order->status,
                        'can_delete' => in_array($order->status, ['pending', 'cancelled']),
                        'total_price' => number_format($order->total_price, 0, '', ',') . ' đ',
                        'discount_price' => number_format($order->discount_price, 0, '', ',') . ' đ',
                        'final_price' => $order->final_price,
                        'payout_amount' => $payout ? (float)$payout->amount : 0,
                        'payout_id' => $payout ? $payout->id : null,
                        'payout_status' => $payout ? $payout->status : null,
                        'shipping_method' => $order->shipping_method,
                        'created_at' => $order->created_at->format('d/m/Y H:i:s'),
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
                                'price' => $item->price,
                                'total' => number_format($item->price * $item->quantity, 0, '', ',') . ' đ',
                            ];
                        }),
                        'payments' => $order->payments->map(function ($payment) {
                            return [
                                'id' => $payment->id,
                                'method' => $payment->paymentMethod ? $payment->paymentMethod->name : null,
                                'amount' => number_format($payment->amount, 0, '', ',') . ' đ',
                                'status' => $payment->status,
                                'created_at' => $payment->created_at->format('d/m/Y H:i:s'),
                            ];
                        }),
                    ];
                }),
                'meta' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Order index error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Có lỗi xảy ra khi lấy danh sách đơn hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Debug logging
        Log::info('Order store request', [
            'request_data' => $request->all(),
            'user_id' => $request->user_id,
            'items_count' => count($request->items ?? []),
            'payment_method' => $request->payment_method,
            'service_id' => $request->service_id,
        ]);

        $request->validate([
            'user_id' => 'required|exists:users,id',
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
            'user_id.required' => 'ID người dùng là bắt buộc',
            'user_id.exists' => 'ID người dùng không tồn tại',
            'discount_id.exists' => 'ID mã giảm giá không tồn tại',
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

            // 1. Validate dữ liệu sản phẩm
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

                // Kiểm tra tồn kho - chỉ cho ProductVariant
                if ($variant && !($request->skip_stock_check ?? false)) {
                    $stock = $variant->quantity ?? 0;
                    
                    // Debug logging
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
                    // Nếu không có variant hoặc bỏ qua kiểm tra tồn kho
                    Log::info('Skipping stock check', [
                        'product_id' => $item['product_id'],
                        'requested_quantity' => $item['quantity'],
                        'skip_stock_check' => $request->skip_stock_check ?? false,
                        'has_variant' => $variant ? 'yes' : 'no',
                    ]);
                }
            }

            // 2. Nhóm sản phẩm theo seller_id (bỏ qua nếu is_buy_now = true)
            $itemsBySeller = [];
            $isBuyNow = $request->is_buy_now ?? false;
            if ($isBuyNow) {
                // Với buyNow, chỉ có một sản phẩm, không cần nhóm theo seller
                $itemsBySeller[0] = $items; // Giả lập seller_id = 0 cho buyNow
            } else {
                foreach ($items as $item) {
                    $product = $products[$item['product_id']];
                    $sellerId = $product->seller_id;
                    $itemsBySeller[$sellerId][] = $item;
                }
            }

            $orders = [];
            foreach ($itemsBySeller as $sellerId => $sellerItems) {
                // 3. Tạo order
                $order = Order::create([
                    'user_id' => $request->user_id,
                    'address_id' => $request->address_id,
                    'discount_id' => null, // sẽ cập nhật sau nếu có mã shop/admin
                    'note' => $request->note ?? '',
                    'status' => 'pending',
                    'total_price' => 0,
                    'discount_price' => 0,
                    'final_price' => 0,
                    'is_buy_now' => $isBuyNow, // Lưu flag is_buy_now
                ]);

                // 4. Tạo order_items
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

                // 5. Áp dụng discount (giống Shopee)
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
                            $shippingDiscount = $discount; // Chỉ lấy voucher phí ship admin
                        } elseif ($discount->seller_id == $sellerId && ($discount->discount_type === 'percentage' || $discount->discount_type === 'fixed')) {
                            $shopDiscount = $discount;
                        } elseif ($discount->seller_id === null && ($discount->discount_type === 'percentage' || $discount->discount_type === 'fixed')) {
                            $adminDiscount = $discount;
                        }
                    }
                }
                // Áp dụng mã shop nếu có, nếu không thì mã admin (chỉ 1 voucher sản phẩm mỗi shop)
                $usedDiscount = $shopDiscount ?: $adminDiscount;
                if ($usedDiscount) {
                    $discountPrice = $usedDiscount->discount_type === 'percentage'
                        ? $totalPrice * ($usedDiscount->discount_value / 100)
                        : $usedDiscount->discount_value;
                    $discountPrice = min($discountPrice, $totalPrice);
                    DiscountUser::create([
                        'discount_id' => $usedDiscount->id,
                        'user_id' => $request->user_id,
                        'is_used' => true,
                    ]);
                    $order->discount_id = $usedDiscount->id;
                }
                // Tính final_price chỉ cho sản phẩm (không bao gồm phí ship)
                $finalPrice = $totalPrice - $discountPrice;

                // 6. Tạo shipping
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
                    $shippingFee = 30000; // 30,000 VND mặc định
                }
                // Áp dụng mã phí ship admin nếu có
                if ($shippingDiscount) {
                    $shippingFee = max(0, $shippingFee - $shippingDiscount->discount_value);
                    DiscountUser::create([
                        'discount_id' => $shippingDiscount->id,
                        'user_id' => $request->user_id,
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

                // 7. Cập nhật order với final_price (chỉ sản phẩm) và tạo payment với tổng tiền (bao gồm phí ship)
                $order->update([
                    'total_price' => $totalPrice,
                    'discount_price' => $discountPrice,
                    'final_price' => $finalPrice, // Chỉ giá sản phẩm sau discount
                    'discount_id' => $order->discount_id, // Đảm bảo luôn lưu discount_id
                ]);

                // 8. Tạo payment với tổng tiền bao gồm phí ship
                $paymentMethod = PaymentMethod::firstOrCreate(
                    ['name' => $request->payment_method],
                    ['status' => 'active']
                );

                $totalPaymentAmount = $finalPrice + $shippingFee; // Tổng tiền thanh toán bao gồm phí ship

                Payment::create([
                    'order_id' => $order->id,
                    'payment_method_id' => $paymentMethod->id,
                    'amount' => $totalPaymentAmount, // Lưu tổng tiền thanh toán
                    'status' => 'pending'
                ]);

                // 9. Gửi mail xác nhận cho COD
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


    /**
     * Display the specified resource.
     */
    public function show(string $id)
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

            // Trả về đúng formatOrderResponse, không custom lại data
            return response()->json($this->formatOrderResponse($order));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Không thể lấy thông tin đơn hàng: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            Log::info('Updating order', [
                'order_id' => $id,
                'request_data' => $request->all()
            ]);

            // Validate dữ liệu đầu vào
            $validator = Validator::make($request->all(), [
                'status' => 'required|string|in:pending,processing,shipped,delivered,cancelled',
                'note' => 'nullable|string|max:500'
            ], [
                'status.required' => 'Trạng thái đơn hàng là bắt buộc',
                'status.string' => 'Trạng thái đơn hàng phải là chuỗi',
                'status.in' => 'Trạng thái đơn hàng không hợp lệ',
                'note.string' => 'Ghi chú phải là chuỗi ký tự',
                'note.max' => 'Ghi chú không được vượt quá 500 ký tự'
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed', [
                    'errors' => $validator->errors()->toArray()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Tìm đơn hàng
            $order = Order::find($id);

            // Kiểm tra nếu đơn hàng không tồn tại
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng với ID: ' . $id
                ], 404);
            }

            // Kiểm tra logic chuyển trạng thái
            $currentStatus = $order->status;
            $newStatus = $request->status;

            Log::info('Status transition check', [
                'current_status' => $currentStatus,
                'new_status' => $newStatus
            ]);

            // Kiểm tra các quy tắc chuyển trạng thái
            $validTransitions = [
                'pending' => ['processing', 'cancelled'],
                'processing' => ['shipped', 'cancelled'],
                'shipped' => ['delivered', 'cancelled'],
                'delivered' => [],
                'cancelled' => []
            ];

            if (!isset($validTransitions[$currentStatus])) {
                return response()->json([
                    'success' => false,
                    'message' => "Trạng thái hiện tại không hợp lệ: '$currentStatus'"
                ], 422);
            }

            if (!in_array($newStatus, $validTransitions[$currentStatus])) {
                return response()->json([
                    'success' => false,
                    'message' => "Không thể chuyển trạng thái từ '$currentStatus' sang '$newStatus'"
                ], 422);
            }

            // Cập nhật đơn hàng
            $order->status = $newStatus;
            if ($request->has('note')) {
                $order->note = $request->note;
            }
            $order->save();

            // Gửi mail thông báo cập nhật trạng thái
            try {
                if ($order->user && $order->user->email) {
                    Mail::to($order->user->email)->send(new OrderStatusUpdatedMail($order, $currentStatus));
                }
            } catch (\Exception $e) {
                Log::error('Send mail error: ' . $e->getMessage());
            }

            // Tạo thông báo dựa trên trạng thái mới
            $statusMessages = [
                'processing' => 'Đơn hàng đã được xác nhận và đang được xử lý',
                'shipped' => 'Đơn hàng đang được giao đến bạn',
                'delivered' => 'Đơn hàng đã được giao thành công',
                'cancelled' => 'Đơn hàng đã bị hủy'
            ];

            $responseData = [
                'success' => true,
                'message' => 'Cập nhật đơn hàng thành công',
                'status_message' => $statusMessages[$newStatus] ?? 'Trạng thái đơn hàng đã được cập nhật',
                'data' => [
                    'id' => $order->id,
                    'status' => $order->status,
                    'note' => $order->note,
                    'can_delete' => in_array($order->status, ['pending', 'cancelled']),
                    'updated_at' => $order->updated_at->format('d/m/Y H:i:s')
                ]
            ];

            Log::info('Order updated successfully', $responseData);
            return response()->json($responseData);
        } catch (\Exception $e) {
            Log::error('Order update error', [
                'order_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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

    private function formatOrderResponse($order)
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
                'email' => $order->user->email,
            ],
            'address' => [
                'id' => $order->address->id,
                'name' => $order->address->name,
                'phone' => $order->address->phone,
                'province_id' => $order->address->province_id,
                'district_id' => $order->address->district_id,
                'ward_code' => $order->address->ward_code,
                'detail' => $order->address->detail,
            ],
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
                    'id' => $item->id,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'thumbnail' => $item->product->thumbnail,
                    ],
                    'variant' => $item->productVariant ? [
                        'id' => $item->productVariant->id,
                        'name' => $item->productVariant->name,
                        'thumbnail' => $item->productVariant->thumbnail ?? null,
                    ] : null,
                    'quantity' => $item->quantity,
                    'price' => (int) $item->price,
                    'total' => (int) ($item->price * $item->quantity),
                ];
            }),
            'payments' => $order->payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'method' => $payment->paymentMethod ? $payment->paymentMethod->name : null,
                    'amount' => (int) $payment->amount,
                    'status' => $payment->status,
                    'created_at' => $payment->created_at->format('d/m/Y H:i:s'),
                ];
            }),
        ];
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
