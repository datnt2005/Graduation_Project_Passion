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
            'shipping',
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
                    'created_at' => optional($order->created_at)->format('d/m/Y H:i:s'),
                    'user' => optional($order->user)->only(['name', 'email']),
                    'address' => $order->address ? [
                        'name' => $order->address->name,
                        'phone' => $order->address->phone,
                        'detail' => $order->address->detail,
                        'province_id' => $order->address->province_id,
                        'district_id' => $order->address->district_id,
                        'ward_code' => $order->address->ward_code,
                        'province_name' => optional($order->address->province)->name ?? null,
                        'district_name' => optional($order->address->district)->name ?? null,
                        'ward_name' => optional($order->address->ward)->name ?? null,
                    ] : null,
                    'can_delete' => in_array($order->status, ['pending']),
                    'shipping' => $order->shipping ? [
                        'tracking_code' => $order->shipping->tracking_code,
                        'status' => $order->shipping->status,
                        'estimated_delivery' => $order->shipping->estimated_delivery,
                    ] : null,
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
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Không có quyền xem đơn này'], 403);
        }

        // Load đủ các quan hệ
        $order->load([
            'user:id,name,email',
            'address',
            'shipping',
            'orderItems.product.productPic',
            'orderItems.productVariant',
            'payments.paymentMethod',
        ]);

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
                'province_name' => optional($order->address->province)->name ?? null,
                'district_name' => optional($order->address->district)->name ?? null,
                'ward_name' => optional($order->address->ward)->name ?? null,
            ] : null,
            'shipping' => $order->shipping ? [
                'tracking_code' => $order->shipping->tracking_code,
                'status' => $order->shipping->status,
                'estimated_delivery' => $order->shipping->estimated_delivery,
            ] : null,
            'order_items' => $order->orderItems->map(function ($item) {
                $product = $item->product;
                $variant = $item->productVariant;
                // Sinh tên variant động từ các thuộc tính
                $variantName = null;
                if ($variant && $variant->attributes && $variant->attributes->count()) {
                    $variantName = $variant->attributes->map(function($attr) {
                        $value = $attr->values->where('id', $attr->pivot->value_id)->first();
                        return $value ? $value->value : null;
                    })->filter()->implode(' - ');
                }
                return [
                    'product' => [
                        'id' => $product->id ?? null,
                        'name' => $product->name ?? '',
                        'thumbnail' => $product->thumbnail
                            ? config('app.media_base_url') . $product->thumbnail
                            : ($product->productPic && $product->productPic->first()
                                ? config('app.media_base_url') . $product->productPic->first()->imagePath
                                : null),
                        'slug' => $product->slug ?? '',
                    ],
                    'variant' => $variant ? [
                        'id' => $variant->id,
                        'name' => $variantName,
                    ] : null,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->quantity * $item->price,
                ];
            })->values(),
            'payments' => $order->payments->map(function ($payment) {
                return [
                    'method' => $payment->paymentMethod ? $payment->paymentMethod->name : null,
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