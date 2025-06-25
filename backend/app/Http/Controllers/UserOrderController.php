<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
                    'final_price' => number_format((float) $order->final_price, 0, '', ',') . ' đ',
                    'created_at' => $order->created_at->format('d/m/Y H:i:s'),
                    'user' => optional($order->user)->only(['name', 'email']),
                    'address' => optional($order->address)->only(['address', 'phone']),
                    'can_delete' => in_array($order->status, ['pending']),
                    'order_items' => $order->orderItems->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product' => optional($item->product)->only(['id', 'name', 'thumbnail', 'slug']),
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

    // show đơn hàng
    public function show(Order $order)
    {
        // 1. Kiểm tra quyền truy cập
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Không có quyền xem đơn này'], 403);
        }

        // 2. Load các quan hệ cần thiết (không load ward/district/province)
        $order->load([
            'user:id,name,email',
            'address', // không cần gọi .ward, .district, .province
            'orderItems.product.productPic',
            'orderItems.productVariant',
            'payments',
        ]);

        // 3. Trả dữ liệu chi tiết đơn hàng
        return response()->json([
            'id' => $order->id,
            'status' => $order->status,
            'note' => $order->note,
            'created_at' => $order->created_at->format('d/m/Y H:i'),
            'total_price' => $order->total_price,
            'discount_price' => $order->discount_price,
            'shipping_fee' => $order->shipping_fee,
            'final_price' => $order->final_price,

            'user' => [
                'name' => $order->user->name ?? '',
                'email' => $order->user->email ?? '',
            ],

            'address' => $order->address ? [
                'name' => $order->address->name,
                'phone' => $order->address->phone,
                'detail' => $order->address->detail,
                'province_id' => $order->address->province_id,
                'district_id' => $order->address->district_id,
                'ward_code' => $order->address->ward_code,
            ] : null,

            'items' => $order->orderItems->map(function ($item) {
                $product = $item->product;
                $image = optional($product->productPic->first())->url;
                $slug = $item->slug ?? $product->slug;
                return [
                    'product_name' => $product->name ?? '',
                    'variant' => $item->productVariant->name ?? '',
                    'image_url' => $image ? asset('storage/' . $image) : asset('images/no-image.png'),
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->quantity * $item->price,
                    'slug' => $slug,
                ];
            })->values(),

            'payments' => $order->payments->map(function ($payment) {
                return [
                    'method' => $payment->method,
                    'amount' => $payment->amount,
                    'status' => $payment->status,
                    'created_at' => $payment->created_at->format('d/m/Y H:i'),
                ];
            })->values(),
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

        $order->status = 'cancelled';
        $order->save();

        return response()->json(['message' => 'Đã hủy đơn hàng thành công']);
    }

}
