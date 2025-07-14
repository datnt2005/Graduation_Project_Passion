<?php

namespace App\Http\Controllers;

use App\Models\Payout;
use App\Models\Seller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PayoutController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $seller = Seller::where('user_id', $user->id)->firstOrFail();
            $payouts = Payout::where('seller_id', $seller->id)
                ->with(['order', 'order.shipping'])
                ->orderBy('created_at', 'desc')
                ->get();
            return response()->json([
                'data' => $payouts->map(function ($payout) {
                    return [
                        'id' => $payout->id,
                        'order_id' => $payout->order_id,
                        'shop_id' => $payout->seller_id,
                        'amount' => (float)$payout->amount,
                        'status' => $payout->status,
                        'note' => $payout->note ?? 'Payout cho đơn hàng ' . ($payout->order->shipping->tracking_code ?? $payout->order_id),
                        'created_at' => $payout->created_at ? \Carbon\Carbon::parse($payout->created_at)->format('d/m/Y H:i:s') : null,
                        'transferred_at' => $payout->transferred_at ? \Carbon\Carbon::parse($payout->transferred_at)->format('d/m/Y H:i:s') : null,
                    ];
                })
            ], 200);
        } catch (\Exception $e) {
            Log::error('Payout index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Lỗi khi lấy danh sách payout',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tạo mới payout
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|exists:orders,id',
                'shop_id' => 'required|exists:sellers,id',
                'amount' => 'required|numeric|min:0',
                'status' => 'nullable|in:pending,completed,failed',
                'note' => 'nullable|string'
            ]);

            $user = Auth::user();
            if ($user->role === 'seller') {
                $seller = Seller::where('user_id', $user->id)->firstOrFail();
                if ($request->shop_id !== $seller->id) {
                    return response()->json([
                        'message' => 'Bạn không có quyền tạo payout cho shop này'
                    ], 403);
                }
            }

            $order = Order::findOrFail($request->order_id);
            if ($order->payout_id) {
                return response()->json([
                    'message' => 'Đơn hàng đã có payout'
                ], 400);
            }

            $payout = Payout::create([
                'seller_id' => $request->shop_id,
                'order_id' => $request->order_id,
                'amount' => $request->amount,
                'status' => $request->status ?? 'pending',
                'note' => $request->note,
                'created_at' => \Carbon\Carbon::now(),
                'transferred_at' => $request->status === 'completed' ? \Carbon\Carbon::now() : null,
            ]);

            $order->update([
                'payout_id' => $payout->id,
                'payout_status' => $payout->status,
                'payout_amount' => $payout->amount
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tạo payout thành công',
                'data' => [
                    'id' => $payout->id,
                    'order_id' => $payout->order_id,
                    'shop_id' => $payout->seller_id,
                    'amount' => (float)$payout->amount,
                    'status' => $payout->status,
                    'note' => $payout->note,
                    'created_at' => $payout->created_at ? \Carbon\Carbon::parse($payout->created_at)->format('d/m/Y H:i:s') : null,
                    'transferred_at' => $payout->transferred_at ? \Carbon\Carbon::parse($payout->transferred_at)->format('d/m/Y H:i:s') : null,
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Payout creation error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hiển thị chi tiết payout
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $payout = Payout::with(['order', 'order.shipping'])->findOrFail($id);
            $user = Auth::user();
            if ($user->role === 'seller') {
                $seller = Seller::where('user_id', $user->id)->firstOrFail();
                if ($payout->seller_id !== $seller->id) {
                    return response()->json([
                        'message' => 'Bạn không có quyền xem payout này'
                    ], 403);
                }
            }
            return response()->json([
                'data' => [
                    'id' => $payout->id,
                    'order_id' => $payout->order_id,
                    'shop_id' => $payout->seller_id,
                    'amount' => (float)$payout->amount,
                    'status' => $payout->status,
                    'note' => $payout->note,
                    'created_at' => $payout->created_at ? \Carbon\Carbon::parse($payout->created_at)->format('d/m/Y H:i:s') : null,
                    'transferred_at' => $payout->transferred_at ? \Carbon\Carbon::parse($payout->transferred_at)->format('d/m/Y H:i:s') : null,
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Payout show error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Lỗi khi lấy chi tiết payout',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Cập nhật payout
     *
     * @param \Illuminate\Http\Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,completed,failed',
                'transferred_at' => 'nullable|date',
                'amount' => 'nullable|numeric|min:0' // Thêm validation cho amount
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $payout = Payout::findOrFail($id);
            $user = Auth::user();
            if ($user->role === 'seller') {
                $seller = Seller::where('user_id', $user->id)->firstOrFail();
                if ($payout->seller_id !== $seller->id) {
                    return response()->json([
                        'message' => 'Bạn không có quyền cập nhật payout này'
                    ], 403);
                }
            }

            // Cập nhật trạng thái
            $payout->status = $request->status;

            // Tính lại amount nếu trạng thái là completed
            if ($request->status === 'completed' && $payout->order_id) {
                $order = Order::findOrFail($payout->order_id);
                $payout->amount = max(($order->final_price - ($order->shipping->shipping_fee ?? 0)) * 0.95, 0);
            } elseif ($request->has('amount')) {
                $payout->amount = $request->amount; // Cho phép cập nhật amount nếu được gửi
            }

            // Cập nhật transferred_at
            $payout->transferred_at = $request->status === 'completed' ? ($request->transferred_at ? \Carbon\Carbon::parse($request->transferred_at)->format('Y-m-d H:i:s') : \Carbon\Carbon::now()->format('Y-m-d H:i:s')) : null;
            $payout->save();

            // Cập nhật order
            if ($payout->order_id) {
                $order = Order::findOrFail($payout->order_id);
                $order->update([
                    'payout_status' => $payout->status,
                    'payout_amount' => $payout->amount
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật payout thành công',
                'data' => [
                    'id' => $payout->id,
                    'order_id' => $payout->order_id,
                    'shop_id' => $payout->seller_id,
                    'amount' => (float)$payout->amount,
                    'status' => $payout->status,
                    'note' => $payout->note,
                    'created_at' => $payout->created_at ? \Carbon\Carbon::parse($payout->created_at)->format('d/m/Y H:i:s') : null,
                    'transferred_at' => $payout->transferred_at ? \Carbon\Carbon::parse($payout->transferred_at)->format('d/m/Y H:i:s') : null,
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Payout update error: ' . $e->getMessage(), [
                'payout_id' => $id,
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payout: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách payout đã duyệt
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listApproved(Request $request)
    {
        try {
            $user = Auth::user();
            $query = Payout::with(['order', 'order.shipping'])
                ->where('status', 'completed')
                ->orderByDesc('transferred_at');

            if ($user->role === 'seller') {
                $seller = Seller::where('user_id', $user->id)->firstOrFail();
                $query->where('seller_id', $seller->id);
            }

            $payouts = $query->get();

            return response()->json([
                'data' => $payouts->map(function ($payout) {
                    return [
                        'id' => $payout->id,
                        'order_id' => $payout->order_id,
                        'shop_id' => $payout->seller_id,
                        'amount' => (float)$payout->amount,
                        'status' => $payout->status,
                        'note' => $payout->note ?? 'Payout cho đơn hàng ' . ($payout->order->shipping->tracking_code ?? $payout->order_id),
                        'created_at' => $payout->created_at ? \Carbon\Carbon::parse($payout->created_at)->format('d/m/Y H:i:s') : null,
                        'transferred_at' => $payout->transferred_at ? \Carbon\Carbon::parse($payout->transferred_at)->format('d/m/Y H:i:s') : null,
                    ];
                })
            ], 200);
        } catch (\Exception $e) {
            Log::error('Payout list-approved error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Lỗi khi lấy danh sách payout đã duyệt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function approve(Request $request, $id)
{
    try {
        $payout = Payout::find($id);
        if (!$payout) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy payout với ID: ' . $id
            ], 404);
        }

        $user = Auth::user();
        if ($user->role === 'seller') {
            $seller = Seller::where('user_id', $user->id)->firstOrFail();
            if ($payout->seller_id !== $seller->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền duyệt payout này'
                ], 403);
            }
        }

        if ($payout->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Payout đã được duyệt trước đó'
            ], 400);
        }

        $payout->update([
            'status' => 'completed',
            'transferred_at' => Carbon::now()->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s')
        ]);

        // Cập nhật trạng thái payout trong order
        $order = Order::find($payout->order_id);
        if ($order) {
            $order->update([
                'payout_status' => 'completed',
                'payout_amount' => $payout->amount,
                'transferred_at' => $payout->transferred_at
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Duyệt payout thành công',
            'data' => [
                'id' => $payout->id,
                'order_id' => $payout->order_id,
                'shop_id' => $payout->seller_id,
                'amount' => (float)$payout->amount,
                'status' => $payout->status,
                'created_at' => $payout->created_at ? Carbon::parse($payout->created_at)->format('d/m/Y H:i:s') : null,
                'transferred_at' => $payout->transferred_at ? Carbon::parse($payout->transferred_at)->format('d/m/Y H:i:s') : null,
            ]
        ], 200);
    } catch (\Exception $e) {
        Log::error('Payout approval error: ' . $e->getMessage(), [
            'payout_id' => $id,
            'user_id' => Auth::id(),
            'request_data' => $request->all(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi duyệt payout: ' . $e->getMessage()
        ], 500);
    }
}
}
