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

        // Validate dữ liệu đầu vào
        $status = $request->input('status');
        if (!$status) {
            return response()->json(['success' => false, 'message' => 'Thiếu trạng thái đơn hàng!'], 422);
        }
        if (in_array($status, ['failed', 'failed_delivery', 'rejected_by_customer'])) {
            if (!$request->filled('failure_reason')) {
                return response()->json(['success' => false, 'message' => 'Vui lòng nhập lý do thất bại!'], 422);
            }
        }
        if ($status === 'shipping') {
            $trackingCode = $request->input('tracking_code');
            if (!$trackingCode || !preg_match('/^[A-Za-z0-9]{6}$/', $trackingCode)) {
                return response()->json(['success' => false, 'message' => 'Mã vận đơn phải gồm 6 ký tự chữ cái hoặc số!'], 422);
            }
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
        $order->status = $status;
        $order->save();

        // Tự động tạo payout khi đơn hàng chuyển sang delivered lần đầu (cho seller này)
        if ($oldStatus !== 'delivered' && $order->status === 'delivered') {
            // Lấy các item thuộc seller này
            $filteredItems = $order->orderItems->filter(function ($item) use ($seller) {
                return $item->product && $item->product->seller_id == $seller->id;
            });
            $totalPrice = $filteredItems->sum(function ($item) {
                return $item->price * $item->quantity;
            });
            $amount = round($totalPrice * 0.95, 2);
            // Chỉ tạo payout nếu chưa có payout cho seller này và order này
            $existingPayout = Payout::where('order_id', $order->id)->where('seller_id', $seller->id)->first();
            if (!$existingPayout && $amount > 0) {
                Payout::create([
                    'seller_id' => $seller->id,
                    'order_id' => $order->id,
                    'amount' => $amount,
                    'status' => 'pending',
                ]);
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

        // Chỉ lấy các item thuộc seller này
        $filteredItems = $order->orderItems->filter(function ($item) use ($seller) {
            return $item->product && $item->product->seller_id == $seller->id;
        })->values();

        $payout = \App\Models\Payout::where('order_id', $order->id)->where('seller_id', $seller->id)->first();

        $totalPrice = $filteredItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

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
            'total_price' => $totalPrice,
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
                                return $value ? $attr->name . ': ' . $value->value : null;
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
            'estimated_payout' => round($totalPrice * 0.95, 2),
            'payout_status' => $payout ? $payout->status : null,
            'transferred_at' => $payout ? $payout->transferred_at : null,
        ]);
    }
} 