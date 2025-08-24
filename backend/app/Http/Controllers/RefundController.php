<?php

namespace App\Http\Controllers;

use App\Models\Refund;
use App\Models\Order;
use App\Models\User;
use App\Models\Notification;
use App\Models\NotificationRecipient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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
                    'order' => fn($q) => $q->select('id', 'user_id')->with([
                        'shipping' => fn($q) => $q->select('id', 'order_id', 'tracking_code', 'shipping_fee')
                    ]),
                    'user' => fn($q) => $q->select('id', 'name', 'email')
                ])
                ->select('id', 'order_id', 'user_id', 'reason', 'status', 'amount', 'bank_account_number', 'bank_name', 'created_at', 'updated_at')
                ->latest();

            if ($request->filled('order_id')) {
                $query->where('order_id', $request->order_id);
            }

            if ($user->role !== 'admin') {
                $query->where('user_id', $user->id);
            }

            $refunds = $query->get();

            // Format dữ liệu để log + trả response đồng bộ
            $formattedRefunds = $refunds->map(function ($refund) {
                return [
                    'id' => $refund->id,
                    'order_id' => $refund->order_id,
                    'user_id' => $refund->user_id,
                    'reason' => $refund->reason,
                    'status' => $refund->status,
                    'amount' => (float)$refund->amount,
                    'bank_account_number' => $refund->bank_account_number,
                    'bank_name' => $refund->bank_name,
                    'created_at' => $refund->created_at?->toISOString(),
                    'updated_at' => $refund->updated_at?->toISOString(),
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

            // Log sau khi format
            Log::info('Refunds retrieved:', [
                'count' => $formattedRefunds->count(),
                'data' => $formattedRefunds
            ]);

            return response()->json([
                'success' => true,
                'data' => $formattedRefunds
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
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
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
            ])->select('id', 'order_id', 'user_id', 'reason', 'status', 'amount', 'bank_account_number', 'bank_name', 'created_at', 'updated_at');

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
                    'bank_account_number' => $refund->bank_account_number,
                    'bank_name' => $refund->bank_name,
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
        try {
            $validator = Validator::make($request->all(), [
                'order_id' => 'required|exists:orders,id',
                'user_id' => 'required|exists:users,id',
                'reason' => 'required|string|min:10|max:1000',
                'amount' => 'required|numeric|min:0',
                'bank_account_number' => 'required|string|max:50',
                'bank_name' => 'required|string|max:255',
                'status' => 'nullable|in:pending,approved,rejected'
            ], [
                'order_id.required' => 'Vui lòng chọn đơn hàng.',
                'order_id.exists' => 'Đơn hàng không tồn tại.',
                'user_id.required' => 'Vui lòng chọn người dùng.',
                'user_id.exists' => 'Người dùng không tồn tại.',
                'reason.required' => 'Vui lòng nhập lý do hoàn tiền.',
                'reason.string' => 'Lý do hoàn tiền phải là chuỗi ký tự.',
                'reason.min' => 'Lý do hoàn tiền phải có ít nhất :min ký tự.',
                'reason.max' => 'Lý do hoàn tiền không được vượt quá :max ký tự.',
                'amount.required' => 'Vui lòng nhập số tiền hoàn.',
                'amount.numeric' => 'Số tiền hoàn phải là số.',
                'amount.min' => 'Số tiền hoàn phải lớn hơn hoặc bằng 0.',
                'bank_account_number.required' => 'Vui lòng nhập số tài khoản ngân hàng.',
                'bank_account_number.string' => 'Số tài khoản ngân hàng phải là chuỗi ký tự.',
                'bank_account_number.max' => 'Số tài khoản ngân hàng không được vượt quá :max ký tự.',
                'bank_name.required' => 'Vui lòng nhập tên ngân hàng.',
                'bank_name.string' => 'Tên ngân hàng phải là chuỗi ký tự.',
                'bank_name.max' => 'Tên ngân hàng không được vượt quá :max ký tự.',
                'status.in' => 'Trạng thái không hợp lệ (chỉ chấp nhận: pending, approved, rejected).',
            ]);

            if ($validator->fails()) {
                // Chỉ trả về lỗi đầu tiên, ưu tiên bank_account_number
                $errorMessage = $validator->errors()->first('bank_account_number')
                    ?: $validator->errors()->first();
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                ], 422);
            }

            $data = $validator->validated();

            // Kiểm tra số tiền hoàn
            $order = Order::findOrFail($data['order_id']);
            $maxRefundAmount = max(floatval($order->final_price ?? 0), 0) / 1000;
            if ($data['amount'] > $maxRefundAmount) {
                return response()->json([
                    'success' => false,
                    'message' => "Số tiền hoàn không được vượt quá " . number_format($maxRefundAmount * 1000, 0, ',', '.') . " VND"
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
            $notification = Notification::create([
                'title' => 'Yêu cầu hoàn tiền',
                'content' => "Bạn vừa nhận được một yêu cầu hoàn tiền mới.",
                'type' => 'system',
                'link' => "/admin/orders/list-order",
                'user_id' => $refund->user_id,
                'from_role' => 'admin',
                'channels' => json_encode(['dashboard']),
                'status' => 'sent',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Yêu cầu hoàn tiền đã được gửi',
                'data' => [
                    'id' => $refund->id,
                    'order_id' => $refund->order_id,
                    'user_id' => $refund->user_id,
                    'amount' => (float)$refund->amount,
                    'bank_account_number' => $refund->bank_account_number,
                    'bank_name' => $refund->bank_name,
                    'status' => $refund->status,
                    'reason' => $refund->reason,
                    'created_at' => $refund->created_at ? $refund->created_at->toISOString() : null
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo yêu cầu hoàn tiền: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server khi tạo yêu cầu hoàn tiền.',
            ], 500);
        }
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

            $notification = Notification::create([
                'title' => 'Yêu cầu hoàn tiền',
                'content' => "Yêu cầu hoàn tiền của bạn đã được cập nhật",
                'type' => 'system',
                'link' => "/users/orders",
                'user_id' => $refund->user_id,
                'from_role' => 'customer',
                'channels' => json_encode(['dashboard']),
                'status' => 'sent',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            NotificationRecipient::create([
                'notification_id' => $notification->id,
                'user_id' => $refund->user_id,
                'is_read' => false,
                'is_hidden' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

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
                    'bank_account_number' => $refund->bank_account_number,
                    'bank_name' => $refund->bank_name,
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
        try {
            // 1) Validate input
            $validator = Validator::make($request->all(), [
                'reason'              => 'required|string|min:10|max:1000',
                'amount'              => 'required|numeric|min:0',
                'bank_account_number' => 'required|string|max:50',
                'bank_name'           => 'required|string|max:255',
                'status'              => 'nullable|in:pending,approved,rejected',
            ], [
                'reason.required'              => 'Vui lòng nhập lý do hoàn tiền.',
                'reason.string'                => 'Lý do hoàn tiền phải là chuỗi ký tự.',
                'reason.min'                   => 'Lý do hoàn tiền phải có ít nhất :min ký tự.',
                'reason.max'                   => 'Lý do hoàn tiền không được vượt quá :max ký tự.',
                'amount.required'              => 'Vui lòng nhập số tiền hoàn.',
                'amount.numeric'               => 'Số tiền hoàn phải là số.',
                'amount.min'                   => 'Số tiền hoàn phải lớn hơn hoặc bằng 0.',
                'bank_account_number.required' => 'Vui lòng nhập số tài khoản ngân hàng.',
                'bank_account_number.string'   => 'Số tài khoản ngân hàng phải là chuỗi ký tự.',
                'bank_account_number.max'      => 'Số tài khoản ngân hàng không được vượt quá :max ký tự.',
                'bank_name.required'           => 'Vui lòng nhập tên ngân hàng.',
                'bank_name.string'             => 'Tên ngân hàng phải là chuỗi ký tự.',
                'bank_name.max'                => 'Tên ngân hàng không được vượt quá :max ký tự.',
                'status.in'                    => 'Trạng thái không hợp lệ (chỉ chấp nhận: pending, approved, rejected).',
            ]);

            // Ưu tiên trả lỗi của bank_account_number
            if ($validator->fails()) {
                $errorMessage = $validator->errors()->first('bank_account_number')
                    ?: $validator->errors()->first();

                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                ], 422);
            }

            $data = $validator->validated();

            // 2) Tìm đơn hàng thuộc user hiện tại
            $order = Order::with(['payments.paymentMethod', 'shipping'])
                ->where('id', $orderId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            // Số tiền tối đa có thể hoàn = final_price (VND)
            $maxRefundAmount = max((float) ($order->final_price ?? 0), 0.0);

            Log::info('Processing refund request', [
                'order_id'        => $orderId,
                'user_id'         => Auth::id(),
                'final_price'     => $order->final_price,
                'total_price'     => $order->total_price,
                'shipping_fee'    => optional($order->shipping)->shipping_fee ?? 0,
                'maxRefundAmount' => $maxRefundAmount,
                'requested_amount' => $data['amount'],
                'order_status'    => $order->status,
            ]);

            // 3) Chặn vượt trần
            if ((float)$data['amount'] > $maxRefundAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Số tiền hoàn không được vượt quá ' . number_format($maxRefundAmount, 0, ',', '.') . ' VND',
                ], 422);
            }

            // 4) Không cho tạo trùng yêu cầu
            if (Refund::where('order_id', $orderId)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn hàng này đã có yêu cầu hoàn tiền.',
                ], 422);
            }

            // 5) Xác định loại thanh toán
            $hasPayments = $order->payments && $order->payments->count() > 0;
            $hasCOD = $hasPayments && $order->payments->contains(function ($p) {
                return strtolower(optional($p->paymentMethod)->name ?? '') === 'cod';
            });
            $hasNonCOD = $hasPayments && $order->payments->contains(function ($p) {
                return strtolower(optional($p->paymentMethod)->name ?? '') !== 'cod';
            });

            // 6) Kiểm tra điều kiện đủ để hoàn tiền
            //    - COD: CHỈ khi trạng thái = returned (đã hoàn hàng)
            //    - Online (không COD): khi trạng thái thuộc 1 trong các case sau
            $onlineEligibleStatuses = ['failed', 'failed_delivery', 'rejected_by_customer', 'refunded', 'returned'];
            if ($hasCOD && !$hasNonCOD) {
                // Đơn COD thuần
                if (!in_array($order->status, ['returned', 'refunded'], true)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Đơn hàng COD chỉ được yêu cầu hoàn tiền khi trạng thái là "Đã trả hàng".',
                    ], 422);
                }
            } else {
                // Đơn online (không COD, hoặc pha trộn nhưng có non-COD)
                if (!in_array($order->status, $onlineEligibleStatuses, true)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Chỉ hoàn tiền cho đơn hàng online khi trạng thái là: thất bại / giao không thành công / khách từ chối / đã hoàn trả / đã trả hàng.',
                    ], 422);
                }
            }

            // 7) Tạo refund trong transaction, duyệt -> cập nhật trạng thái đơn
            $refund = DB::transaction(function () use ($data, $order, $orderId) {
                $refund = Refund::create([
                    'order_id'            => $orderId,
                    'user_id'             => Auth::id(),
                    'reason'              => $data['reason'],
                    'amount'              => (float)$data['amount'],  // Frontend đã gửi VND
                    'bank_account_number' => $data['bank_account_number'],
                    'bank_name'           => $data['bank_name'],
                    'status'              => $data['status'] ?? 'pending',
                ]);

                if ($refund->status === 'approved') {
                    // Nếu duyệt ngay -> set trạng thái đơn thành refunded và ghi chú
                    $order->update([
                        'status' => 'refunded',
                        'note'   => $data['reason'],
                    ]);
                }

                return $refund;
            });
            $admins = User::where('role', 'admin')->first();
            $admin_id = $admins ? $admins->id : 1;
            $notification = Notification::create([
                'title' => 'Yêu cầu hoàn tiền',
                'content' => "Bạn vừa nhận được một yêu cầu hoàn tiền mới.",
                'type' => 'system',
                'link' => "/admin/orders/list-order",
                'user_id' => $admin_id,
                'from_role' => 'system',
                'channels' => json_encode(['dashboard']),
                'status' => 'sent',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Yêu cầu hoàn tiền đã được gửi',
                'data'    => [
                    'id'                  => $refund->id,
                    'order_id'            => $refund->order_id,
                    'user_id'             => $refund->user_id,
                    'amount'              => (float) $refund->amount,
                    'bank_account_number' => $refund->bank_account_number,
                    'bank_name'           => $refund->bank_name,
                    'status'              => $refund->status,
                    'reason'              => $refund->reason,
                    'created_at'          => $refund->created_at ? $refund->created_at->toIso8601String() : null,
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error processing refund: ' . $e->getMessage(), [
                'order_id' => $orderId,
                'user_id'  => Auth::id(),
                'trace'    => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lỗi server khi xử lý yêu cầu hoàn tiền.',
            ], 500);
        }
    }
}
