<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserOrderController extends Controller
{
    // Lấy danh sách đơn hàng của người dùng đang đăng nhập
    public function index(Request $request)
    {
        $user = Auth::user();

        $orders = Order::with([
            'orderItems.product',
            'orderItems.productVariant',
            'address',
            'payments.paymentMethod',
        ])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'code' => 'ORD' . str_pad($order->id, 3, '0', STR_PAD_LEFT),
                    'status' => $order->status,
                    'final_price' => number_format($order->final_price, 0, '', ',') . ' đ',
                    'created_at' => $order->created_at->format('d/m/Y H:i:s'),
                    'user' => optional($order->user)->only(['name', 'email']),
                    'address' => optional($order->address)->only(['address', 'phone']),
                    'can_delete' => in_array($order->status, ['pending']),
                    'order_items' => $order->orderItems->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product' => optional($item->product)->only(['id', 'name', 'thumbnail']),
                            'variant' => optional($item->productVariant)->only(['id', 'name']),
                            'quantity' => $item->quantity,
                            'price' => number_format($item->price, 0, '', ',') . ' đ',
                            'total' => number_format($item->price * $item->quantity, 0, '', ',') . ' đ',
                        ];
                    }),
                    'payments' => $order->payments->map(function ($payment) {
                        return [
                            'id' => $payment->id,
                            'method' => optional($payment->paymentMethod)->name,
                            'amount' => number_format($payment->amount, 0, '', ',') . ' đ',
                            'status' => $payment->status,
                            'created_at' => $payment->created_at->format('d/m/Y H:i:s'),
                        ];
                    }),
                ];
            }),
        ]);
    }

    // Hủy đơn hàng (chỉ khi trạng thái là 'pending')
    public function cancel(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Không có quyền hủy đơn này'], 403);
        }

        if (!in_array($order->status, ['pending'])) {
            return response()->json(['message' => 'Không thể hủy đơn hàng trong trạng thái hiện tại'], 400);
        }

        $order->status = 'canceled';
        $order->save();

        return response()->json(['message' => 'Đã hủy đơn hàng thành công']);
    }

    // Mua lại đơn hàng
    public function reorder(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Không có quyền thực hiện'], 403);
        }

        $items = $order->orderItems->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
            ];
        });

        return response()->json([
            'message' => 'Lấy danh sách sản phẩm mua lại thành công',
            'items' => $items,
        ]);
    }
}
