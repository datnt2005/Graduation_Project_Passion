<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Mail\OrderStatusUpdatedMail;
use Illuminate\Support\Facades\Mail;
use App\Models\Payout;

class SellerOrderController extends Controller
{
    /**
     * Lấy danh sách đơn hàng của seller hiện tại
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $seller = Seller::where('user_id', $user->id)->first();
        if (!$seller) {
            return response()->json([
                'success' => false,
                'message' => 'Người dùng không phải là seller.'
            ], 403);
        }

        // Lấy tất cả order_id có ít nhất 1 sản phẩm thuộc seller này
        $orderIds = OrderItem::whereHas('product', function ($q) use ($seller) {
            $q->where('seller_id', $seller->id);
        })->pluck('order_id')->unique();

        $ordersQuery = Order::with([
            'orderItems.product',
            'orderItems.productVariant',
            'address',
            'payments.paymentMethod',
            'shipping',
        ])->whereIn('id', $orderIds);

        // Filter
        if ($request->has('status') && $request->status) {
            $ordersQuery->where('status', $request->status);
        }
        if ($request->has('from_date') && $request->from_date) {
            $ordersQuery->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $ordersQuery->whereDate('created_at', '<=', $request->to_date);
        }
        if ($request->has('order_id') && $request->order_id) {
            $ordersQuery->whereHas('shipping', function($q) use ($request) {
                $q->where('tracking_code', 'like', '%' . $request->order_id . '%');
            });
        }

        $orders = $ordersQuery->orderBy('created_at', 'desc')->paginate($request->input('per_page', 10));

        return response()->json([
            'data' => $orders->map(function ($order) use ($seller) {
                // Chỉ trả về các order_items thuộc seller này
                $filteredItems = $order->orderItems->filter(function ($item) use ($seller) {
                    return $item->product && $item->product->seller_id == $seller->id;
                })->values();
                $payout = \App\Models\Payout::where('order_id', $order->id)->first();
                // Tính tổng tiền sản phẩm của seller hiện tại
                $sellerTotal = $filteredItems->sum(function ($item) {
                    return $item->price * $item->quantity;
                });
                return [
                    'id' => $order->id,
                    'user' => [
                        'id' => $order->user->id,
                        'name' => $order->user->name,
                        'email' => $order->user->email,
                    ],
                    'address' => $order->address,
                    'note' => $order->note,
                    'status' => $order->status,
                    'total_price' => $order->total_price,
                    'discount_price' => $order->discount_price,
                    'final_price' => $order->final_price,
                    'shipping' => $order->shipping,
                    'created_at' => $order->created_at,
                    'order_items' => $filteredItems->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product' => [
                                'id' => $item->product->id,
                                'name' => $item->product->name,
                                'thumbnail' => ($item->productVariant && $item->productVariant->thumbnail)
                                    ? $item->productVariant->thumbnail
                                    : ($item->product->thumbnail ?: 'no-image.png'),
                            ],
                            'variant' => $item->productVariant ? [
                                'id' => $item->productVariant->id,
                                'name' => ($item->productVariant->attributes && $item->productVariant->attributes->count())
                                    ? $item->productVariant->attributes->map(function($attr) {
                                        $value = $attr->values->where('id', $attr->pivot->value_id)->first();
                                        return $value ? $value->value : null;
                                    })->filter()->implode(' - ')
                                    : $item->productVariant->name,
                            ] : null,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'total' => $item->price * $item->quantity,
                        ];
                    }),
                    'payments' => $order->payments->map(function ($payment) {
                        return [
                            'id' => $payment->id,
                            'method' => $payment->paymentMethod ? $payment->paymentMethod->name : null,
                            'amount' => $payment->amount,
                            'status' => $payment->status,
                            'created_at' => $payment->created_at,
                        ];
                    }),
                    'payout_amount' => $payout ? (float)$payout->amount : 0,
                    'estimated_payout' => round($sellerTotal * 0.95, 2),
                    'payout_status' => $payout ? $payout->status : null,
                    'transferred_at' => $payout ? $payout->transferred_at : null,
                ];
            }),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        $seller = Seller::where('user_id', $user->id)->first();
        if (!$seller) {
            return response()->json(['success' => false, 'message' => 'Bạn không phải seller!'], 403);
        }

        $order = Order::with('user', 'shipping', 'orderItems.product', 'orderItems.productVariant')->findOrFail($id);

        // Chỉ cho phép cập nhật nếu seller có sản phẩm trong order này
        $hasProduct = $order->orderItems()->whereHas('product', function($q) use ($seller) {
            $q->where('seller_id', $seller->id);
        })->exists();
        if (!$hasProduct) {
            return response()->json(['success' => false, 'message' => 'Không có quyền cập nhật đơn này!'], 403);
        }

        $oldStatus = $order->status;
        $order->status = $request->input('status');
        $order->save();

        // Tự động tạo payout khi đơn hàng chuyển sang delivered lần đầu
        if ($oldStatus !== 'delivered' && $order->status === 'delivered') {
            // Lấy seller thực sự của sản phẩm trong order (trường hợp nhiều seller thì payout cho từng seller riêng, ở đây demo payout cho seller đầu tiên)
            $orderSeller = $order->orderItems->first()->product->seller ?? null;
            if ($orderSeller) {
                $shippingFee = $order->shipping ? $order->shipping->shipping_fee : 0;
                $baseAmount = $order->final_price - $shippingFee;
                if ($baseAmount < 0) $baseAmount = 0;
                $adminFee = round($baseAmount * 0.05, 2);
                $sellerAmount = round($baseAmount * 0.95, 2);
                Payout::create([
                    'seller_id' => $orderSeller->id,
                    'order_id' => $order->id,
                    'amount' => $sellerAmount,
                    'status' => 'pending',
                ]);
                // (Tùy chọn) Log admin fee
                \Log::info('Admin fee for order '.$order->id.': '.$adminFee);
            }
        }

        // Gửi mail thông báo cập nhật trạng thái
        try {
            if ($order->user && $order->user->email) {
                Mail::to($order->user->email)->send(new OrderStatusUpdatedMail($order, $oldStatus));
            }
        } catch (\Exception $e) {
            \Log::error('Send mail error: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $user = Auth::user();
        $seller = Seller::where('user_id', $user->id)->first();
        if (!$seller) {
            return response()->json(['success' => false, 'message' => 'Bạn không phải seller!'], 403);
        }

        $order = Order::with([
            'orderItems.product',
            'orderItems.productVariant',
            'address',
            'payments.paymentMethod',
            'shipping',
        ])->findOrFail($id);

        // Kiểm tra seller có sản phẩm trong order này không
        $hasProduct = $order->orderItems()->whereHas('product', function($q) use ($seller) {
            $q->where('seller_id', $seller->id);
        })->exists();
        if (!$hasProduct) {
            return response()->json(['success' => false, 'message' => 'Không có quyền xem đơn này!'], 403);
        }

        $filteredItems = $order->orderItems->filter(function ($item) use ($seller) {
            return $item->product && $item->product->seller_id == $seller->id;
        })->values();
        $payout = \App\Models\Payout::where('order_id', $order->id)->first();

        return response()->json([
            'id' => $order->id,
            'user' => [
                'id' => $order->user->id,
                'name' => $order->user->name,
                'email' => $order->user->email,
            ],
            'address' => $order->address,
            'note' => $order->note,
            'status' => $order->status,
            'total_price' => $order->total_price,
            'discount_price' => $order->discount_price,
            'final_price' => $order->final_price,
            'shipping' => $order->shipping,
            'created_at' => $order->created_at,
            'order_items' => $filteredItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'thumbnail' => ($item->productVariant && $item->productVariant->thumbnail)
                            ? $item->productVariant->thumbnail
                            : ($item->product->thumbnail ?: 'no-image.png'),
                    ],
                    'variant' => $item->productVariant ? [
                        'id' => $item->productVariant->id,
                        'name' => ($item->productVariant->attributes && $item->productVariant->attributes->count())
                            ? $item->productVariant->attributes->map(function($attr) {
                                $value = $attr->values->where('id', $attr->pivot->value_id)->first();
                                return $value ? $value->value : null;
                            })->filter()->implode(' - ')
                            : $item->productVariant->name,
                    ] : null,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->price * $item->quantity,
                ];
            }),
            'payments' => $order->payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'method' => $payment->paymentMethod ? $payment->paymentMethod->name : null,
                    'amount' => $payment->amount,
                    'status' => $payment->status,
                    'created_at' => $payment->created_at,
                ];
            }),
            'payout_amount' => $payout ? (float)$payout->amount : 0,
            'estimated_payout' => round($filteredItems->sum(function ($item) {
                return $item->price * $item->quantity;
            }) * 0.95, 2),
            'payout_status' => $payout ? $payout->status : null,
            'transferred_at' => $payout ? $payout->transferred_at : null,
        ]);
    }
} 