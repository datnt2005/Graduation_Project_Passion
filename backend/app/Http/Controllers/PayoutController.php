<?php

namespace App\Http\Controllers;

use App\Models\Payout;
use App\Models\Order;
use App\Models\Seller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayoutController extends Controller
{
    /**
     * Lấy danh sách payout đã duyệt cho admin
     */
    public function approvedList(Request $request)
    {
        $user = auth()->user();
        \Log::info('approvedList debug', [
            'user_id' => $user?->id,
            'role' => $user?->role,
            'email' => $user?->email,
            'request_token' => $request->bearerToken(),
        ]);
        try {
            $query = Payout::with(['order.shipping', 'seller.user'])
                ->whereIn('status', ['completed', 'failed'])
                ->latest('transferred_at');

            if ($user && $user->role === 'seller') {
                $seller = Seller::where('user_id', $user->id)->first();
                \Log::info('approvedList seller', [
                    'seller_id' => $seller?->id,
                    'user_id' => $user->id
                ]);
                if ($seller) {
                    $query->where('seller_id', $seller->id);
                } else {
                    // Nếu không tìm thấy seller, trả về mảng rỗng
                    return response()->json([
                        'success' => true,
                        'data' => [],
                        'meta' => [],
                        'message' => 'Không có payout nào.'
                    ]);
                }
            } else if ($request->has('seller_id')) {
                $query->where('seller_id', $request->seller_id);
            }

            // Phân trang
            $perPage = $request->input('per_page', 10);
            $payouts = $query->paginate($perPage);

            if ($payouts->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'meta' => [
                        'current_page' => $payouts->currentPage(),
                        'last_page' => $payouts->lastPage(),
                        'per_page' => $payouts->perPage(),
                        'total' => $payouts->total(),
                    ],
                    'message' => 'Không có payout nào.'
                ]);
            }

            // Format response
            $formattedPayouts = $payouts->through(function($payout) {
                return [
                    'id' => $payout->id,
                    'amount' => $payout->amount,
                    'created_at' => $payout->created_at,
                    'transferred_at' => $payout->transferred_at,
                    'order' => $payout->order ? [
                        'id' => $payout->order->id,
                        'shipping' => [
                            'tracking_code' => $payout->order->shipping?->tracking_code
                        ]
                    ] : null,
                    'seller' => $payout->seller ? [
                        'id' => $payout->seller->id,
                        'store_name' => $payout->seller->store_name,
                        'user' => [
                            'name' => $payout->seller->user->name,
                            'email' => $payout->seller->user->email
                        ]
                    ] : null,
                    'note' => $payout->note,
                    'status' => $payout->status
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedPayouts->items(),
                'meta' => [
                    'current_page' => $formattedPayouts->currentPage(),
                    'from' => $formattedPayouts->firstItem(),
                    'last_page' => $formattedPayouts->lastPage(),
                    'path' => $formattedPayouts->path(),
                    'per_page' => $formattedPayouts->perPage(),
                    'to' => $formattedPayouts->lastItem(),
                    'total' => $formattedPayouts->total(),
                ],
                'message' => 'Lấy danh sách payout thành công'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in PayoutController@approvedList: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách payout: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy thống kê payout cho admin
     */
    public function stats(Request $request)
    {
        try {
            $query = Payout::where('status', 'completed');

            // Filter theo seller nếu có
            if ($request->has('seller_id')) {
                $query->where('seller_id', $request->seller_id);
            }

            // Tổng số payout
            $totalPayouts = $query->count();

            // Tổng số tiền đã thanh toán
            $totalAmount = $query->sum('amount');

            // Tổng chiết khấu admin (5%)
            $totalCommission = $totalAmount * 5 / 95;

            // Thống kê theo tháng này
            $thisMonth = Carbon::now()->startOfMonth();
            $monthlyStats = $query->whereMonth('transferred_at', $thisMonth->month)
                ->whereYear('transferred_at', $thisMonth->year)
                ->get();
            $monthlyPayouts = $monthlyStats->count();
            $monthlyAmount = $monthlyStats->sum('amount');
            $monthlyCommission = $monthlyAmount * 5 / 95;

            // Thống kê duyệt tự động vs thủ công
            $autoApprovedPayouts = $query->where('note', 'like', '%Duyệt tự động%')->count();
            $manualApprovedPayouts = $totalPayouts - $autoApprovedPayouts;
            $autoApprovedAmount = $query->where('note', 'like', '%Duyệt tự động%')->sum('amount');
            $manualApprovedAmount = $totalAmount - $autoApprovedAmount;

            return response()->json([
                'success' => true,
                'data' => [
                    'total_payouts' => $totalPayouts,
                    'total_amount' => $totalAmount,
                    'total_commission' => $totalCommission,
                    'monthly_payouts' => $monthlyPayouts,
                    'monthly_amount' => $monthlyAmount,
                    'monthly_commission' => $monthlyCommission,
                    'auto_approved_payouts' => $autoApprovedPayouts,
                    'manual_approved_payouts' => $manualApprovedPayouts,
                    'auto_approved_amount' => $autoApprovedAmount,
                    'manual_approved_amount' => $manualApprovedAmount,
                    'auto_approval_rate' => $totalPayouts > 0 ? round(($autoApprovedPayouts / $totalPayouts) * 100, 2) : 0
                ],
                'message' => 'Lấy thống kê payout thành công'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in PayoutController@stats: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy thống kê payout: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy dữ liệu biểu đồ payout cho admin
     */
    public function chart(Request $request)
    {
        try {
            $type = $request->input('type', 'month'); // day, month, year
            $now = Carbon::now();
            $labels = [];
            $data = [];

            switch ($type) {
                case 'day':
                    // 7 ngày gần nhất
                    for ($i = 6; $i >= 0; $i--) {
                        $date = $now->copy()->subDays($i);
                        $labels[] = $date->format('d/m');
                        
                        $amount = Payout::where('status', 'completed')
                            ->whereDate('transferred_at', $date->format('Y-m-d'))
                            ->when($request->has('seller_id'), function($q) use ($request) {
                                $q->where('seller_id', $request->seller_id);
                            })
                            ->sum('amount');
                        $data[] = round($amount * 5 / 95); // Chiết khấu 5%
                    }
                    break;

                case 'month':
                    // 12 tháng gần nhất
                    for ($i = 11; $i >= 0; $i--) {
                        $date = $now->copy()->subMonths($i);
                        $labels[] = $date->format('m/Y');
                        
                        $amount = Payout::where('status', 'completed')
                            ->whereYear('transferred_at', $date->year)
                            ->whereMonth('transferred_at', $date->month)
                            ->when($request->has('seller_id'), function($q) use ($request) {
                                $q->where('seller_id', $request->seller_id);
                            })
                            ->sum('amount');
                        $data[] = round($amount * 5 / 95);
                    }
                    break;

                case 'year':
                    // 5 năm gần nhất
                    for ($i = 4; $i >= 0; $i--) {
                        $year = $now->copy()->subYears($i)->year;
                        $labels[] = (string)$year;
                        
                        $amount = Payout::where('status', 'completed')
                            ->whereYear('transferred_at', $year)
                            ->when($request->has('seller_id'), function($q) use ($request) {
                                $q->where('seller_id', $request->seller_id);
                            })
                            ->sum('amount');
                        $data[] = round($amount * 5 / 95);
                    }
                    break;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'data' => $data
                ],
                'message' => 'Lấy dữ liệu biểu đồ payout thành công'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in PayoutController@chart: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy dữ liệu biểu đồ payout: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Trả về payout theo id (chưa hỗ trợ)
     */
    public function show($id)
    {
        $user = auth()->user();
        $payout = Payout::with(['order.shipping', 'seller.user'])->find($id);
        if (!$payout) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy payout.'
            ], 404);
        }
        if ($user->role === 'seller') {
            $seller = Seller::where('user_id', $user->id)->first();
            if (!$seller || $payout->seller_id !== $seller->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền xem payout này.'
                ], 403);
            }
        }
        // Trả về chi tiết payout
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $payout->id,
                'amount' => $payout->amount,
                'transferred_at' => $payout->transferred_at,
                'order' => $payout->order ? [
                    'id' => $payout->order->id,
                    'shipping' => [
                        'tracking_code' => $payout->order->shipping?->tracking_code
                    ]
                ] : null,
                'seller' => $payout->seller ? [
                    'id' => $payout->seller->id,
                    'store_name' => $payout->seller->store_name,
                    'user' => [
                        'name' => $payout->seller->user->name,
                        'email' => $payout->seller->user->email
                    ]
                ] : null,
                'note' => $payout->note,
                'status' => $payout->status
            ]
        ]);
    }

    /**
     * Seller nhận payout (tự động)
     */
    public function sellerReceive($id)
    {
        $user = auth()->user();
        if ($user->role !== 'seller') {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền nhận payout!'], 403);
        }
        $payout = \App\Models\Payout::findOrFail($id);
        if ($payout->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Payout không ở trạng thái chờ nhận!'], 400);
        }
        // Kiểm tra seller có quyền nhận payout này không
        $seller = \App\Models\Seller::where('user_id', $user->id)->first();
        if (!$seller || $payout->seller_id !== $seller->id) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền nhận payout này!'], 403);
        }
        $payout->status = 'completed';
        $payout->transferred_at = now();
        $payout->seller_confirmed_at = now();
        $payout->save();
        return response()->json(['success' => true, 'message' => 'Nhận tiền payout thành công!', 'data' => $payout]);
    }

    /**
     * Duyệt payout tự động khi seller cập nhật trạng thái thành delivered
     * 
     * Logic: 
     * - 80% payout sẽ được duyệt tự động để tăng tốc độ thanh toán
     * - 20% còn lại cần admin duyệt thủ công để đảm bảo an toàn và kiểm soát
     * - Tỷ lệ này có thể điều chỉnh bằng cách thay đổi giá trị rand(1, 100) <= 80
     */
    public function autoApprovePayout($orderId, $sellerId)
    {
        try {
            // Tìm payout cho order và seller này
            $payout = Payout::where('order_id', $orderId)
                ->where('seller_id', $sellerId)
                ->where('status', 'pending')
                ->first();

            if (!$payout) {
                Log::info('Không tìm thấy payout pending để duyệt tự động', [
                    'order_id' => $orderId,
                    'seller_id' => $sellerId
                ]);
                return false;
            }

            // Duyệt payout tự động với xác suất 80% (để vẫn có 20% cần admin duyệt thủ công)
            $shouldAutoApprove = rand(1, 100) <= 80;
            
            if ($shouldAutoApprove) {
                $payout->status = 'completed';
                $payout->transferred_at = now();
                $payout->note = $payout->note ? $payout->note . ' (Duyệt tự động)' : 'Duyệt tự động';
                $payout->save();

                Log::info('Payout được duyệt tự động thành công', [
                    'payout_id' => $payout->id,
                    'order_id' => $orderId,
                    'seller_id' => $sellerId,
                    'amount' => $payout->amount,
                    'transferred_at' => $payout->transferred_at
                ]);

                return true;
            } else {
                Log::info('Payout được giữ lại để admin duyệt thủ công', [
                    'payout_id' => $payout->id,
                    'order_id' => $orderId,
                    'seller_id' => $sellerId,
                    'amount' => $payout->amount
                ]);

                return false;
            }
        } catch (\Exception $e) {
            Log::error('Lỗi khi duyệt payout tự động: ' . $e->getMessage(), [
                'order_id' => $orderId,
                'seller_id' => $sellerId,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Admin duyệt payout thủ công
     */
    public function approve($id)
    {
        $user = auth()->user();
        if ($user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền duyệt payout!'], 403);
        }
        $payout = \App\Models\Payout::findOrFail($id);
        if ($payout->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Payout không ở trạng thái chờ duyệt!'], 400);
        }
        $payout->status = 'completed';
        $payout->transferred_at = now();
        $payout->save();
        return response()->json(['success' => true, 'message' => 'Duyệt payout thành công!', 'data' => $payout]);
    }

    /**
     * Admin cập nhật trạng thái payout (pending, completed, failed)
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if ($user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền cập nhật payout!'], 403);
        }
        $payout = \App\Models\Payout::findOrFail($id);
        $status = $request->input('status');
        if (!in_array($status, ['pending', 'completed', 'failed'])) {
            return response()->json(['success' => false, 'message' => 'Trạng thái không hợp lệ!'], 422);
        }
        $payout->status = $status;
        if ($status === 'completed') {
            $payout->transferred_at = now();
        } elseif ($status === 'pending') {
            $payout->transferred_at = null;
        }
        $payout->save();
        return response()->json(['success' => true, 'message' => 'Cập nhật payout thành công!', 'data' => $payout]);
    }

    /**
     * Admin tạo payout thủ công
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if ($user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền tạo payout!'], 403);
        }
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'shop_id' => 'required|exists:sellers,id',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,completed,failed',
            'note' => 'nullable|string'
        ]);
        // Kiểm tra đã có payout cho order + shop này chưa
        $existing = \App\Models\Payout::where('order_id', $request->order_id)
            ->where('seller_id', $request->shop_id)
            ->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Payout đã tồn tại cho đơn hàng này!'], 409);
        }
        $payout = new \App\Models\Payout();
        $payout->order_id = $request->order_id;
        $payout->seller_id = $request->shop_id;
        $payout->amount = $request->amount;
        $payout->status = $request->status;
        $payout->note = $request->note;
        if ($request->status === 'completed') {
            $payout->transferred_at = now();
        }
        $payout->save();
        return response()->json(['success' => true, 'message' => 'Tạo payout thành công!', 'data' => $payout]);
    }

    /**
     * Xử lý khi đơn hàng bị hoàn tiền/hủy
     * Cập nhật trạng thái payout và ghi log
     */
    public function handleOrderRefund($orderId, $sellerId, $reason = 'Đơn hàng bị hoàn tiền')
    {
        try {
            DB::beginTransaction();
            
            // Tìm payout của đơn hàng này (bất kể trạng thái nào)
            $payout = Payout::where('order_id', $orderId)
                ->where('seller_id', $sellerId)
                ->first();
            
            if (!$payout) {
                Log::info('Không tìm thấy payout để xử lý hoàn tiền', [
                    'order_id' => $orderId,
                    'seller_id' => $sellerId
                ]);
                DB::rollBack();
                return false;
            }
            
            // Cập nhật trạng thái payout thành 'failed'
            $payout->status = 'failed';
            $payout->note = $payout->note ? $payout->note . ' | ' . $reason . ' - ' . now()->format('d/m/Y H:i:s') : $reason . ' - ' . now()->format('d/m/Y H:i:s');
            $payout->save();
            
            Log::info('Đã xử lý hoàn tiền payout', [
                'payout_id' => $payout->id,
                'order_id' => $orderId,
                'seller_id' => $sellerId,
                'amount' => $payout->amount,
                'old_status' => $payout->getOriginal('status'),
                'new_status' => 'failed',
                'reason' => $reason
            ]);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi xử lý hoàn tiền payout', [
                'order_id' => $orderId,
                'seller_id' => $sellerId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
