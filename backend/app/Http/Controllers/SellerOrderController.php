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
use App\Models\StockMovement;

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

        $order = Order::with(
            'user',
            'shipping',
            'orderItems.product',
            'orderItems.productVariant.inventories',
            'payments.paymentMethod'
        )->findOrFail($id);

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

        // Tự động trừ kho khi chuyển sang trạng thái "processing" với đơn hàng COD
        try {
            if ($oldStatus !== 'processing' && $status === 'processing') {
                $firstPaymentMethod = optional($order->payments->first())->paymentMethod->name ?? null;
                $isCod = strtolower((string) $firstPaymentMethod) === 'cod';

                if ($isCod) {
                    // Tránh trừ kho nhiều lần cho cùng một đơn
                    $variantIds = $order->orderItems->pluck('product_variant_id')->filter()->values();
                    $alreadyDeducted = $variantIds->isNotEmpty() && StockMovement::whereIn('product_variant_id', $variantIds)
                        ->where('action_type', 'export')
                        ->where('note', 'like', '%#' . $order->id . '%')
                        ->exists();

                    if (!$alreadyDeducted) {
                        foreach ($order->orderItems as $item) {
                            $variant = $item->productVariant;
                            if (!$variant) continue;
                            $quantityToDeduct = (int) $item->quantity;
                            $inventories = $variant->inventories()->orderBy('id')->get();
                            foreach ($inventories as $inventory) {
                                if ($quantityToDeduct <= 0) break;
                                $deduct = (int) min($inventory->quantity, $quantityToDeduct);
                                if ($deduct > 0) {
                                    $inventory->quantity -= $deduct;
                                    $inventory->last_updated = now();
                                    $inventory->save();
                                    $quantityToDeduct -= $deduct;
                                }
                            }
                            // Cập nhật tổng tồn kho cho variant
                            $variant->quantity = $variant->inventories()->sum('quantity');
                            $variant->save();

                            // Ghi lịch sử
                            StockMovement::create([
                                'product_variant_id' => $variant->id,
                                'action_type' => 'export',
                                'quantity' => (int) $item->quantity,
                                'note' => 'Xuất kho cho đơn hàng #' . $order->id,
                                'created_by' => $seller->user_id,
                                'created_by_type' => 'seller',
                            ]);
                        }
                        \Log::info('Đã trừ kho cho đơn COD khi vào processing (seller cập nhật)', [
                            'order_id' => $order->id,
                            'seller_id' => $seller->id,
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Lỗi khi trừ kho cho đơn COD ở trạng thái processing (seller cập nhật)', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

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
                $payout = Payout::create([
                    'seller_id' => $seller->id,
                    'order_id' => $order->id,
                    'amount' => $amount,
                    'status' => 'pending',
                ]);

                // Thử duyệt payout tự động
                $payoutController = new \App\Http\Controllers\PayoutController();
                $autoApproved = $payoutController->autoApprovePayout($order->id, $seller->id);
                
                if ($autoApproved) {
                    \Log::info('Payout được duyệt tự động sau khi seller cập nhật trạng thái delivered', [
                        'order_id' => $order->id,
                        'seller_id' => $seller->id,
                        'payout_id' => $payout->id,
                        'amount' => $amount
                    ]);
                } else {
                    \Log::info('Payout được giữ lại để admin duyệt thủ công', [
                        'order_id' => $order->id,
                        'seller_id' => $seller->id,
                        'payout_id' => $payout->id,
                        'amount' => $amount
                    ]);
                }
            }
        }

        // Xử lý hoàn tiền khi đơn hàng chuyển sang trạng thái hoàn tiền/hủy
        if (in_array($status, ['refunded', 'cancelled', 'failed', 'returned'])) {
            $payoutController = new \App\Http\Controllers\PayoutController();
            $reason = match($status) {
                'refunded' => 'Đơn hàng bị hoàn tiền',
                'cancelled' => 'Đơn hàng bị hủy',
                'failed' => 'Đơn hàng thất bại',
                'returned' => 'Đơn hàng bị trả lại',
                default => 'Đơn hàng bị hoàn tiền'
            };
            
            $payoutController->handleOrderRefund($order->id, $seller->id, $reason);
            
            \Log::info('Đã xử lý hoàn tiền payout khi seller cập nhật trạng thái', [
                'order_id' => $order->id,
                'seller_id' => $seller->id,
                'old_status' => $oldStatus,
                'new_status' => $status,
                'reason' => $reason
            ]);
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

    /**
     * Xóa hàng loạt các đơn hàng đã hủy
     */
    public function bulkDelete(Request $request)
    {
        $user = Auth::user();
        $seller = Seller::where('user_id', $user->id)->first();
        if (!$seller) {
            return response()->json([
                'success' => false,
                'message' => 'Người dùng không phải là seller.'
            ], 403);
        }

        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'required|integer|exists:orders,id'
        ]);

        $orderIds = $request->order_ids;

        // Kiểm tra xem các đơn hàng có chứa sản phẩm của seller này không
        $validOrderIds = OrderItem::whereIn('order_id', $orderIds)
            ->whereHas('product', function ($q) use ($seller) {
                $q->where('seller_id', $seller->id);
            })
            ->pluck('order_id')
            ->unique()
            ->toArray();

        if (empty($validOrderIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng hợp lệ để xóa.'
            ], 400);
        }

        // Kiểm tra xem tất cả đơn hàng có trạng thái 'cancelled' không
        $cancelledOrders = Order::whereIn('id', $validOrderIds)
            ->where('status', 'cancelled')
            ->pluck('id')
            ->toArray();

        if (empty($cancelledOrders)) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể xóa các đơn hàng có trạng thái "Đã hủy".'
            ], 400);
        }

        // Xóa các order_items thuộc seller này trong các đơn hàng đã hủy
        $deletedItems = OrderItem::whereIn('order_id', $cancelledOrders)
            ->whereHas('product', function ($q) use ($seller) {
                $q->where('seller_id', $seller->id);
            })
            ->delete();

        // Kiểm tra xem còn order_items nào trong các đơn hàng này không
        $remainingItems = OrderItem::whereIn('order_id', $cancelledOrders)->count();

        // Nếu không còn order_items nào, xóa luôn đơn hàng
        if ($remainingItems === 0) {
            Order::whereIn('id', $cancelledOrders)->delete();
        }

        return response()->json([
            'success' => true,
            'message' => "Đã xóa thành công {$deletedItems} sản phẩm từ " . count($cancelledOrders) . " đơn hàng đã hủy.",
            'deleted_items_count' => $deletedItems,
            'deleted_orders_count' => count($cancelledOrders)
        ]);
    }
} 