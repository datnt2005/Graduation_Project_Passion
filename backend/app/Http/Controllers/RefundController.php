<?php

namespace App\Http\Controllers;

use App\Models\Refund;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class RefundController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                Log::warning('Unauthorized access attempt to refunds endpoint', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Chưa đăng nhập. Vui lòng đăng nhập lại.'
                ], 401);
            }

            Log::info('Fetching refunds for user:', [
                'user_id' => $user->id,
                'role' => $user->role,
                'email' => $user->email
            ]);

            $query = Refund::query()
                ->with([
                    'order' => fn($query) => $query->select('id', 'user_id')->with([
                        'shipping' => fn($query) => $query->select('id', 'order_id', 'tracking_code', 'shipping_fee')
                    ]),
                    'user' => fn($query) => $query->select('id', 'name', 'email')
                ])
                ->select('id', 'order_id', 'user_id', 'reason', 'status', 'amount', 'created_at', 'updated_at')
                ->latest();

            // Lọc theo order_id nếu có
            if ($request->has('order_id') && !empty($request->order_id)) {
                $query->where('order_id', $request->order_id);
            }

            // Chỉ lấy yêu cầu hoàn tiền của user hiện tại nếu không phải admin
            if ($user->role !== 'admin') {
                $query->where('user_id', $user->id);
            }

            $perPage = $request->query('per_page', 10);
            $refunds = $query->paginate($perPage);

            Log::info('Refunds retrieved:', [
                'count' => $refunds->total(),
                'data' => $refunds->items()
            ]);

            $formattedRefunds = $refunds->getCollection()->map(function ($refund) {
                return [
                    'id' => $refund->id,
                    'order_id' => $refund->order_id,
                    'user_id' => $refund->user_id,
                    'reason' => $refund->reason,
                    'status' => $refund->status,
                    'amount' => (float)$refund->amount,
                    'created_at' => $refund->created_at ? $refund->created_at->toISOString() : null,
                    'updated_at' => $refund->updated_at ? $refund->updated_at->toISOString() : null,
                    'order' => $refund->order ? [
                        'id' => $refund->order->id,
                        'shipping' => $refund->order->shipping ? [
                            'tracking_code' => $refund->order->shipping->tracking_code,
                            'shipping_fee' => (float)($refund->order->shipping->shipping_fee ?? 0),
                        ] : null,
                    ] : null,
                    'user' => $refund->user ? [
                        'id' => $refund->user->id,
                        'name' => $refund->user->name,
                        'email' => $refund->user->email,
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedRefunds,
                'pagination' => [
                    'current_page' => $refunds->currentPage(),
                    'last_page' => $refunds->lastPage(),
                    'per_page' => $refunds->perPage(),
                    'total' => $refunds->total(),
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching refunds', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải danh sách hoàn tiền: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                Log::warning('Unauthorized access attempt to refund details', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'refund_id' => $id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Chưa đăng nhập. Vui lòng đăng nhập lại.'
                ], 401);
            }

            $query = Refund::with([
                'order' => fn($query) => $query->select('id', 'user_id')->with([
                    'shipping' => fn($query) => $query->select('id', 'order_id', 'tracking_code', 'shipping_fee')
                ]),
                'user' => fn($query) => $query->select('id', 'name', 'email')
            ])->select('id', 'order_id', 'user_id', 'reason', 'status', 'amount', 'created_at', 'updated_at');

            // Nếu không phải admin, chỉ cho phép xem yêu cầu hoàn tiền của chính user
            if ($user->role !== 'admin') {
                $query->where('user_id', $user->id);
            }

            $refund = $query->findOrFail($id);

            Log::info('Refund details retrieved:', [
                'refund_id' => $refund->id,
                'user_id' => $user->id,
                'role' => $user->role
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $refund->id,
                    'order_id' => $refund->order_id,
                    'user_id' => $refund->user_id,
                    'reason' => $refund->reason,
                    'status' => $refund->status,
                    'amount' => (float)$refund->amount,
                    'created_at' => $refund->created_at ? $refund->created_at->toISOString() : null,
                    'updated_at' => $refund->updated_at ? $refund->updated_at->toISOString() : null,
                    'order' => $refund->order ? [
                        'id' => $refund->order->id,
                        'shipping' => $refund->order->shipping ? [
                            'tracking_code' => $refund->order->shipping->tracking_code,
                            'shipping_fee' => (float)($refund->order->shipping->shipping_fee ?? 0),
                        ] : null,
                    ] : null,
                    'user' => $refund->user ? [
                        'id' => $refund->user->id,
                        'name' => $refund->user->name,
                        'email' => $refund->user->email,
                    ] : null,
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching refund details', [
                'refund_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy thông tin hoàn tiền: ' . $e->getMessage()
            ], 404);
        }
    }



    public function store(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'user_id' => 'required|exists:users,id',
            'reason' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'status' => 'nullable|in:pending,approved,rejected'
        ]);

        // Kiểm tra số tiền hoàn
        $order = Order::findOrFail($data['order_id']);
        $maxRefundAmount = max((floatval($order->final_price) - floatval($order->shipping?->shipping_fee ?? 0)), 0);
        if ($data['amount'] > $maxRefundAmount) {
            return response()->json([
                'success' => false,
                'message' => "Số tiền hoàn không được vượt quá " . number_format($maxRefundAmount, 0, '', ',') . " đ"
            ], 422);
        }

        // Kiểm tra xem đơn hàng đã có yêu cầu hoàn tiền chưa
        if (Refund::where('order_id', $data['order_id'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng này đã có yêu cầu hoàn tiền.'
            ], 422);
        }

        // Đặt status mặc định là 'pending'
        $data['status'] = $data['status'] ?? 'pending';

        // Tạo yêu cầu hoàn tiền
        $refund = Refund::create($data);

        // Cập nhật trạng thái đơn hàng nếu status là approved
        if ($data['status'] === 'approved') {
            $order->update(['status' => 'refunded', 'note' => $data['reason']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Yêu cầu hoàn tiền đã được gửi',
            'data' => $refund
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $refund = Refund::findOrFail($id);
        $data = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'reason' => 'nullable|string',
            'amount' => 'nullable|numeric|min:0'
        ]);

        // Kiểm tra số tiền hoàn nếu được cung cấp
        if (isset($data['amount'])) {
            $order = Order::findOrFail($refund->order_id);
            $maxRefundAmount = max((floatval($order->final_price) - floatval($order->shipping?->shipping_fee ?? 0)), 0);
            if ($data['amount'] > $maxRefundAmount) {
                return response()->json([
                    'success' => false,
                    'message' => "Số tiền hoàn không được vượt quá " . number_format($maxRefundAmount, 0, '', ',') . " đ"
                ], 422);
            }
        }

        $refund->update($data);

        // Cập nhật trạng thái đơn hàng nếu status là approved
        if ($data['status'] === 'approved') {
            $order = Order::findOrFail($refund->order_id);
            $order->update(['status' => 'refunded', 'note' => $data['reason'] ?? $refund->reason]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái hoàn tiền thành công',
            'data' => $refund
        ]);
    }

    public function approve($id)
    {
        try {
            $refund = Refund::findOrFail($id);
            if ($refund->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Yêu cầu hoàn tiền không ở trạng thái chờ xử lý'
                ], 400);
            }

            $refund->status = 'approved';
            $refund->save();

            // Cập nhật trạng thái đơn hàng
            $order = Order::findOrFail($refund->order_id);
            $order->status = 'refunded';
            $order->note = $refund->reason;
            $order->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $refund->id,
                    'status' => $refund->status,
                    'reason' => $refund->reason,
                    'amount' => (float) $refund->amount,
                    'order_id' => $refund->order_id,
                    'created_at' => $refund->created_at->toISOString(),
                    'updated_at' => $refund->updated_at->toISOString()
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error approving refund', [
                'refund_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi duyệt yêu cầu hoàn tiền: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reject($id)
    {
        try {
            $refund = Refund::findOrFail($id);
            if ($refund->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Yêu cầu hoàn tiền không ở trạng thái chờ xử lý'
                ], 400);
            }

            $refund->status = 'rejected';
            $refund->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $refund->id,
                    'status' => $refund->status,
                    'reason' => $refund->reason,
                    'amount' => (float) $refund->amount,
                    'order_id' => $refund->order_id,
                    'created_at' => $refund->created_at->toISOString(),
                    'updated_at' => $refund->updated_at->toISOString()
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error rejecting refund', [
                'refund_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi từ chối yêu cầu hoàn tiền: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $refund = Refund::findOrFail($id);
        $refund->delete();
        return response()->json([
            'success' => true,
            'message' => 'Đã xóa yêu cầu hoàn tiền'
        ]);
    }

    public function getByOrderId($id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                Log::warning('Unauthorized access attempt to refund by order endpoint', [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'order_id' => $id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Chưa đăng nhập. Vui lòng đăng nhập lại.'
                ], 401);
            }

            $query = Refund::with([
                'order' => function ($query) {
                    $query->with('shipping');
                },
                'user'
            ])->where('order_id', $id);

            if ($user->role !== 'admin') {
                $query->where('user_id', $user->id);
            }

            $refund = $query->first();

            if (!$refund) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'Không tìm thấy yêu cầu hoàn tiền cho đơn hàng này.'
                ], 200);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $refund->id,
                    'order_id' => $refund->order_id,
                    'user_id' => $refund->user_id,
                    'reason' => $refund->reason,
                    'status' => $refund->status,
                    'amount' => (float)$refund->amount,
                    'created_at' => $refund->created_at ? $refund->created_at->toISOString() : null,
                    'updated_at' => $refund->updated_at ? $refund->updated_at->toISOString() : null,
                    'order' => $refund->order ? [
                        'id' => $refund->order->id,
                        'shipping' => $refund->order->shipping ? [
                            'tracking_code' => $refund->order->shipping->tracking_code,
                            'shipping_fee' => (float)($refund->order->shipping->shipping_fee ?? 0),
                        ] : null,
                    ] : null,
                    'user' => $refund->user ? [
                        'id' => $refund->user->id,
                        'name' => $refund->user->name,
                        'email' => $refund->user->email,
                    ] : null,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching refund by order_id', [
                'order_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy thông tin hoàn tiền: ' . $e->getMessage()
            ], 500);
        }
    }

    public function process(Request $request, $orderId)
    {
        $data = $request->validate([
            'reason' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'status' => 'nullable|in:pending,approved,rejected'
        ]);

        // Kiểm tra đơn hàng
        $order = Order::where('id', $orderId)->where('user_id', Auth::id())->firstOrFail();
        $maxRefundAmount = max(($order->final_price - ($order->shipping?->shipping_fee ?? 0)), 0);
        if ($data['amount'] > $maxRefundAmount) {
            return response()->json([
                'success' => false,
                'message' => "Số tiền hoàn không được vượt quá " . number_format($maxRefundAmount, 0, '', ',') . " đ"
            ], 422);
        }

        // Kiểm tra xem đơn hàng đã có yêu cầu hoàn tiền chưa
        if (Refund::where('order_id', $orderId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng này đã có yêu cầu hoàn tiền.'
            ], 422);
        }

        // Thêm user_id từ người dùng hiện tại
        $data['order_id'] = $orderId;
        $data['user_id'] = Auth::id();
        $data['status'] = $data['status'] ?? 'pending';

        // Tạo yêu cầu hoàn tiền
        $refund = Refund::create($data);

        // Cập nhật trạng thái đơn hàng nếu status là approved
        if ($data['status'] === 'approved') {
            $order->update(['status' => 'refunded', 'note' => $data['reason']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Yêu cầu hoàn tiền đã được gửi',
            'data' => [
                'id' => $refund->id,
                'order_id' => $refund->order_id,
                'user_id' => $refund->user_id,
                'amount' => (float)$refund->amount,
                'status' => $refund->status,
                'reason' => $refund->reason,
                'created_at' => $refund->created_at ? $refund->created_at->toISOString() : null
            ]
        ], 201);
    }
}
