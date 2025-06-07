<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
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
            $query = Order::with(['orderItems.product', 'orderItems.productVariant', 'user', 'address', 'payments']);

            // Lọc theo trạng thái
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Lọc theo khoảng thời gian
            if ($request->has('from_date')) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }
            if ($request->has('to_date')) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }

            // Tìm kiếm theo ID đơn hàng
            if ($request->has('order_id')) {
                $query->where('id', $request->order_id);
            }

            // Sắp xếp
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Phân trang
            $perPage = $request->input('per_page', 10);
            $orders = $query->paginate($perPage);

            return response()->json([
                'data' => $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
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
                        'total_price' => number_format($order->total_price, 0, ',', '.') . ' đ',
                        'discount_price' => number_format($order->discount_price, 0, ',', '.') . ' đ',
                        'final_price' => number_format($order->final_price, 0, ',', '.') . ' đ',
                        'shipping_method' => $order->shipping_method,
                        'created_at' => $order->created_at->format('d/m/Y H:i:s'),
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
                                'price' => number_format($item->price, 0, ',', '.') . ' đ',
                                'total' => number_format($item->price * $item->quantity, 0, ',', '.') . ' đ',
                            ];
                        }),
                        'payments' => $order->payments->map(function ($payment) {
                            return [
                                'id' => $payment->id,
                                'method' => $payment->paymentMethod->name,
                                'amount' => number_format($payment->amount, 0, ',', '.') . ' đ',
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
            return response()->json([
                'message' => 'Có lỗi xảy ra khi lấy danh sách đơn hàng: ' . $e->getMessage()
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
            'address_id' => 'required|exists:addresses,id',
            'discount_id' => 'nullable|exists:discounts,id',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:COD,VNPAY,MOMO',
            'shipping_method' => 'nullable|string|in:giao-tiet-kiem,giao-nhanh,other_method',
        ], [
            'user_id.required' => 'ID người dùng là bắt buộc',
            'user_id.exists' => 'ID người dùng không tồn tại',
            'address_id.required' => 'ID địa chỉ là bắt buộc',
            'address_id.exists' => 'ID địa chỉ không tồn tại',
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
            'shipping_method.in' => 'Phương thức vận chuyển không hợp lệ'
        ]);

        try {
            DB::beginTransaction();

            // Create order
            $order = Order::create([
                'user_id' => $request->user_id,
                'address_id' => $request->address_id,
                'discount_id' => $request->discount_id,
                'note' => $request->note ?? '',
                'status' => 'pending',
                'total_price' => 0,
                'discount_price' => 0,
                'final_price' => 0,
                'shipping_method' => $request->shipping_method,
            ]);

            // Create order items
            $totalPrice = 0;
            foreach ($request->items as $item) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
                $totalPrice += $item['price'] * $item['quantity'];
            }

            // Update order prices
            $discountPrice = 0; // Calculate discount if needed
            $finalPrice = $totalPrice - $discountPrice;

            $order->update([
                'total_price' => $totalPrice,
                'discount_price' => $discountPrice,
                'final_price' => $finalPrice,
            ]);

            // Create payment method if not exists
            $paymentMethod = PaymentMethod::firstOrCreate(
                ['name' => $request->payment_method],
                ['status' => 'active']
            );

            // Create payment
            Payment::create([
                'order_id' => $order->id,
                'payment_method_id' => $paymentMethod->id,
                'amount' => $finalPrice,
                'status' => 'pending'
            ]);

            DB::commit();

            // Load relationships
            $order->load(['orderItems.product', 'orderItems.productVariant', 'user', 'address', 'payments.paymentMethod']);

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
            // Xử lý ID - loại bỏ dấu ngoặc nhọn nếu có
            $orderId = trim($id, '{}');
            
            // Tìm đơn hàng
            $order = Order::find($orderId);
            
            // Kiểm tra nếu đơn hàng không tồn tại
            if (!$order) {
                return response()->json([
                    'message' => 'Không tìm thấy đơn hàng với ID: ' . $orderId
                ], 404);
            }

            // Validate dữ liệu đầu vào
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,processing,shipping,completed,cancelled',
                'note' => 'nullable|string|max:500'
            ], [
                'status.required' => 'Trạng thái đơn hàng là bắt buộc',
                'status.in' => 'Trạng thái đơn hàng không hợp lệ',
                'note.string' => 'Ghi chú phải là chuỗi ký tự',
                'note.max' => 'Ghi chú không được vượt quá 500 ký tự'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Kiểm tra logic chuyển trạng thái
            $currentStatus = $order->status;
            $newStatus = $request->status;

            // Kiểm tra các quy tắc chuyển trạng thái
            $validTransitions = [
                'pending' => ['processing', 'cancelled'],
                'processing' => ['shipping', 'cancelled'],
                'shipping' => ['completed', 'cancelled'],
                'completed' => [],
                'cancelled' => []
            ];

            if (!in_array($newStatus, $validTransitions[$currentStatus])) {
                return response()->json([
                    'message' => "Không thể chuyển trạng thái từ '$currentStatus' sang '$newStatus'"
                ], 422);
            }

            // Cập nhật đơn hàng
            $order->status = $request->status;
            if ($request->has('note')) {
                $order->note = $request->note;
            }
            $order->save();

            // Trả về response thành công
            return response()->json([
                'message' => 'Cập nhật đơn hàng thành công',
                'data' => [
                    'id' => $order->id,
                    'status' => $order->status,
                    'note' => $order->note,
                    'updated_at' => $order->updated_at->format('d/m/Y H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra khi cập nhật đơn hàng: ' . $e->getMessage()
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
    private function formatOrderResponse($order)
    {
        return [
            'id' => $order->id,
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
            'total_price' => number_format($order->total_price, 0, ',', '.') . ' đ',
            'discount_price' => number_format($order->discount_price, 0, ',', '.') . ' đ',
            'final_price' => number_format($order->final_price, 0, ',', '.') . ' đ',
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
                    'price' => number_format($item->price, 0, ',', '.') . ' đ',
                    'total' => number_format($item->price * $item->quantity, 0, ',', '.') . ' đ',
                ];
            }),
            'payments' => $order->payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'method' => $payment->paymentMethod->name,
                    'amount' => number_format($payment->amount, 0, ',', '.') . ' đ',
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
            'processing' => ['shipping', 'cancelled'],
            'shipping' => ['completed', 'cancelled'],
            'completed' => [],
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
                    'discount_amount' => number_format($result['discount_amount'], 0, ',', '.') . ' đ',
                    'final_price' => number_format($result['final_price'], 0, ',', '.') . ' đ'
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
                    'final_price' => number_format($result['final_price'], 0, ',', '.') . ' đ'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}