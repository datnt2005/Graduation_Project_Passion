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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
                        'final_price' => number_format($order->final_price, 0, '', ',') . ' đ',
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
                                'price' => number_format($item->price, 0, '', ',') . ' đ',
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
            \Log::error('Order index error: ' . $e->getMessage());
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
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'discount_id' => 'nullable|exists:discounts,id',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:COD,VNPAY,MOMO',
            'address_id' => 'required|exists:addresses,id',
            'service_id' => 'required|integer|min:1',
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
            'service_id.integer' => 'Phương thức giao hàng không hợp lệ',
        ]);

        try {
            DB::beginTransaction();

            // Tạo đơn hàng
            $order = Order::create([
                'user_id' => $request->user_id,
                'address_id' => $request->address_id,
                'discount_id' => $request->discount_id,
                'note' => $request->note ?? '',
                'status' => 'pending',
                'total_price' => 0,
                'discount_price' => 0,
                'final_price' => 0,
            ]);

            // Order items
            $totalPrice = 0;
            foreach ($request->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
                $totalPrice += $item['price'] * $item['quantity'];
            }

            // Discount
            $discountPrice = 0;
            if ($request->discount_id) {
                $discount = Discount::find($request->discount_id);
                if ($discount) {
                    $discountPrice = $discount->discount_type === 'percentage'
                        ? $totalPrice * ($discount->discount_value / 100)
                        : $discount->discount_value;
                    $discountPrice = min($discountPrice, $totalPrice);

                    DiscountUser::create([
                        'discount_id' => $discount->id,
                        'user_id' => $request->user_id,
                        'is_used' => true,
                    ]);
                }
            }

            $finalPrice = $totalPrice - $discountPrice;

            $order->update([
                'total_price' => $totalPrice,
                'discount_price' => $discountPrice,
                'final_price' => $finalPrice,
            ]);

            // Payment
            $paymentMethod = PaymentMethod::firstOrCreate(
                ['name' => $request->payment_method],
                ['status' => 'active']
            );

            Payment::create([
                'order_id' => $order->id,
                'payment_method_id' => $paymentMethod->id,
                'amount' => $finalPrice,
                'status' => 'pending'
            ]);

            // Giao hàng
            $address = Address::find($request->address_id);
            $ghn = new GHNService();

            // Lưu ý: bạn cần sửa GHNService để truyền service_id vào payload
            $ghnOrder = $ghn->createShippingOrder($order, $address, $request->service_id, $request->payment_method);

            // Shipping method
            $shippingMethod = ShippingMethod::firstOrCreate(
                ['id' => $request->service_id],
                ['name' => $ghnOrder['service_type_id'] ?? 'GHN', 'carrier' => 'GHN', 'estimated_days' => 3, 'cost' => $ghnOrder['total_fee'] ?? 0]
            );

            // Shipping
            Shipping::create([
                'order_id' => $order->id,
                'shipping_method_id' => $shippingMethod->id,
                'estimated_delivery' => $ghnOrder['expected_delivery_time'] ?? null,
                'shipping_fee' => $ghnOrder['total_fee'] ?? 0,
                'tracking_code' => $ghnOrder['order_code'] ?? null,
                'status' => 'pending',
            ]);

            DB::commit();

            $order->load([
                'orderItems.product',
                'orderItems.productVariant',
                'user',
                'address',
                'payments.paymentMethod',
                'shipping.shippingMethod'
            ]);

            return response()->json([
                'message' => 'Đơn hàng đã được tạo thành công',
                'data' => $this->formatOrderResponse($order)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
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
            $order = Order::with(['orderItems.product', 'orderItems.productVariant', 'user', 'address', 'payments.paymentMethod'])
                ->findOrFail($id);

            return response()->json([
                'data' => $this->formatOrderResponse($order)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra khi lấy thông tin đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            \Log::info('Updating order', [
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
                \Log::warning('Validation failed', [
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

            \Log::info('Status transition check', [
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

            \Log::info('Order updated successfully', $responseData);
            return response()->json($responseData);
        } catch (\Exception $e) {
            \Log::error('Order update error', [
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
            ] : null,
            'user' => [
                'id' => $order->user->id,
                'name' => $order->user->name,
                'email' => $order->user->email,
            ],
            'address' => [
                'id' => $order->address->id,
                'address' => $order->address->address,
                'phone' => $order->address->phone,
            ],
            'note' => $order->note ?? '',
            'status' => $order->status,
            'total_price' => $this->formatPrice($order->total_price) . ' đ',
            'discount_price' => $this->formatPrice($order->discount_price) . ' đ',
            'final_price' => $this->formatPrice($order->final_price) . ' đ',
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
                    ] : null,
                    'quantity' => $item->quantity,
                    'price' => $this->formatPrice($item->price) . ' đ',
                    'total' => $this->formatPrice($item->price * $item->quantity) . ' đ',
                ];
            }),
            'payments' => $order->payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'method' => $payment->paymentMethod->name,
                    'amount' => $this->formatPrice($payment->amount) . ' đ',
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
        $totalUsers = \App\Models\User::count();
        // Tổng đơn hàng
        $totalOrders = \App\Models\Order::count();
        // Tổng kênh bán hàng (giả sử là tổng số seller)
        $totalSellers = \App\Models\User::where('role', 'seller')->count();
        // Doanh thu từ người bán (tổng final_price các đơn hàng của seller, trạng thái delivered)
        $sellerRevenue = \App\Models\Order::whereHas('user', function($q){
            $q->where('role', 'seller');
        })->where('status', 'delivered')->sum('final_price');
        // Tổng doanh thu (tổng final_price các đơn hàng trạng thái delivered)
        $totalRevenue = \App\Models\Order::where('status', 'delivered')->sum('final_price');
        // Tổng thu thập (giả sử là tổng discount_price các đơn hàng delivered)
        $totalDiscount = \App\Models\Order::where('status', 'delivered')->sum('discount_price');

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
