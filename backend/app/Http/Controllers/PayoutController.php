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
                ->where('status', 'completed')
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

            return response()->json([
                'success' => true,
                'data' => [
                    'total_payouts' => $totalPayouts,
                    'total_amount' => $totalAmount,
                    'total_commission' => $totalCommission,
                    'monthly_payouts' => $monthlyPayouts,
                    'monthly_amount' => $monthlyAmount,
                    'monthly_commission' => $monthlyCommission
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
        $payout->save();
        return response()->json(['success' => true, 'message' => 'Nhận tiền payout thành công!', 'data' => $payout]);
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
}
