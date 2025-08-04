<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Seller;
use App\Models\Review;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\Shipping;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Thống kê dashboard cho admin
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        $sellerId = $request->input('seller_id');
        if ($user && $user->role === 'admin' && $sellerId) {
            // Lấy stats cho 1 seller cụ thể
            $payoutOrderIds = \App\Models\Payout::where('seller_id', $sellerId)
                ->where('status', 'completed')
                ->pluck('order_id')
                ->unique()
                ->filter();
            $soldOrdersCount = \App\Models\Order::whereIn('id', $payoutOrderIds)->where('status', 'delivered')->count();
            $totalRevenue = \App\Models\Order::whereIn('id', $payoutOrderIds)->where('status', 'delivered')->sum('final_price');
            $deliveredOrderIds = \App\Models\Order::whereIn('id', $payoutOrderIds)->where('status', 'delivered')->pluck('id');
            $orderItems = \App\Models\OrderItem::whereIn('order_id', $deliveredOrderIds)->get();
            $variantIds = $orderItems->pluck('product_variant_id')->unique();
            $variants = \App\Models\ProductVariant::whereIn('id', $variantIds)->get()->keyBy('id');
            $totalCost = 0;
            foreach ($orderItems as $item) {
                $variant = $variants[$item->product_variant_id] ?? null;
                if ($variant) {
                    $totalCost += $variant->cost_price * $item->quantity;
                }
            }
            $totalProfit = $totalRevenue - $totalCost;
            $totalLoss = $totalProfit < 0 ? abs($totalProfit) : 0;
            if ($totalProfit < 0) $totalProfit = 0;
            return response()->json([
                'total_orders' => \App\Models\Order::whereIn('id', $payoutOrderIds)->count(),
                'sold_orders' => $soldOrdersCount,
                'total_revenue' => $totalRevenue,
                'total_cost' => $totalCost,
                'total_profit' => $totalProfit,
                'total_loss' => $totalLoss,
            ]);
        }
        // Tổng người dùng
        $totalUsers = \App\Models\User::count();
        // Tổng đơn hàng
        $totalOrders = \App\Models\Order::count();
        // Tổng kênh bán hàng (giả sử là tổng số seller)
        $totalSellers = \App\Models\User::where('role', 'seller')->count();
        // Doanh thu từ người bán (tổng final_price các đơn hàng của seller, trạng thái delivered)
        $sellerRevenue = \App\Models\Order::whereHas('user', function($q){
            $q->where('role', 'seller');
        })->where('status', 'delivered')->sum('final_price');
        // Tổng doanh thu (tổng final_price các đơn hàng trạng thái delivered)
        $totalRevenue = \App\Models\Order::where('status', 'delivered')->sum('final_price');
        // Tổng giảm giá (tổng discount_price các đơn hàng delivered)
        $totalDiscount = \App\Models\Order::where('status', 'delivered')->sum('discount_price');

        return response()->json([
            'total_users' => $totalUsers,
            'total_orders' => $totalOrders,
            'total_sellers' => $totalSellers,
            'seller_revenue' => $sellerRevenue,
            'total_revenue' => $totalRevenue,
            'total_discount' => $totalDiscount,
        ]);
    }

    /**
     * Lấy danh sách thống kê dashboard (dạng list key-value)
     */
    public function statsList()
    {
        // Đơn hàng đã bán (delivered)
        $soldOrdersCount = \App\Models\Order::where('status', 'delivered')->count();
        // Tổng doanh thu các đơn hàng đã bán
        $totalRevenue = \App\Models\Order::where('status', 'delivered')->sum('final_price');
        // Tổng vốn đã bán (tổng cost_price * quantity của các order_items thuộc đơn hàng delivered)
        $deliveredOrderIds = \App\Models\Order::where('status', 'delivered')->pluck('id');
        $orderItems = \App\Models\OrderItem::whereIn('order_id', $deliveredOrderIds)->get();
        $variantIds = $orderItems->pluck('product_variant_id')->unique();
        $variants = \App\Models\ProductVariant::whereIn('id', $variantIds)->get()->keyBy('id');
        $totalCost = 0;
        foreach ($orderItems as $item) {
            $variant = $variants[$item->product_variant_id] ?? null;
            if ($variant) {
                $totalCost += $variant->cost_price * $item->quantity;
            }
        }
        $totalProfit = $totalRevenue - $totalCost;
        $totalLoss = $totalProfit < 0 ? abs($totalProfit) : 0;
        if ($totalProfit < 0) $totalProfit = 0;
        $stats = [
            [
                'label' => 'Tổng Người Dùng',
                'key' => 'total_users',
                'value' => \App\Models\User::count(),
            ],
            [
                'label' => 'Tổng Đơn Hàng',
                'key' => 'total_orders',
                'value' => \App\Models\Order::count(),
            ],
            [
                'label' => 'Đơn Hàng Đã Bán',
                'key' => 'sold_orders',
                'value' => $soldOrdersCount,
            ],
            [
                'label' => 'Tổng Kênh Bán Hàng',
                'key' => 'total_sellers',
                'value' => \App\Models\User::where('role', 'seller')->count(),
            ],
            [
                'label' => 'Tổng Doanh Thu',
                'key' => 'total_revenue',
                'value' => $totalRevenue,
            ],
            [
                'label' => 'Tổng Vốn Đã Bán',
                'key' => 'total_cost',
                'value' => $totalCost,
            ],
            [
                'label' => 'Tổng Lợi Nhuận',
                'key' => 'total_profit',
                'value' => $totalProfit,
            ],
        ];
        $lossStats = [
            [
                'label' => 'Tổng Lỗ',
                'key' => 'total_loss',
                'value' => $totalLoss,
            ]
        ];
        return response()->json([
            'stats' => $stats,
            'lossStats' => $lossStats
        ]);
    }

    /**
     * API trả về dữ liệu doanh thu, lợi nhuận và số đơn hàng cho dashboard (theo ngày/tuần/tháng/năm)
     */
    public function revenueProfitChart(Request $request)
    {
        $type = $request->input('type', 'month'); // day|week|month|year
        $now = now();
        $labels = [];
        $revenueData = [];
        $profitData = [];
        $orderCountData = []; // Thêm mảng cho số lượng đơn hàng

        if ($type === 'day') {
            for ($i = 6; $i >= 0; $i--) {
                $date = $now->copy()->subDays($i)->format('Y-m-d');
                $labels[] = $now->copy()->subDays($i)->format('d/m');
                $orders = \App\Models\Order::whereDate('created_at', $date)->where('status', 'delivered')->get();
                $orderIds = $orders->pluck('id');
                $orderItems = \App\Models\OrderItem::whereIn('order_id', $orderIds)->get();
                $variantIds = $orderItems->pluck('product_variant_id')->unique();
                $variants = \App\Models\ProductVariant::whereIn('id', $variantIds)->get()->keyBy('id');
                $totalRevenue = $orders->sum('final_price');
                $totalCost = 0;
                foreach ($orderItems as $item) {
                    $variant = $variants[$item->product_variant_id] ?? null;
                    if ($variant) {
                        $totalCost += $variant->cost_price * $item->quantity;
                    }
                }
                $revenueData[] = $totalRevenue;
                $profitData[] = $totalRevenue - $totalCost;
                $orderCountData[] = $orders->count(); // Số lượng đơn hàng trong ngày
            }
        } elseif ($type === 'week') {
            for ($i = 3; $i >= 0; $i--) {
                $start = $now->copy()->subWeeks($i)->startOfWeek();
                $end = $now->copy()->subWeeks($i)->endOfWeek();
                $labels[] = 'Tuần ' . $start->format('W');
                $orders = \App\Models\Order::whereBetween('created_at', [$start, $end])->where('status', 'delivered')->get();
                $orderIds = $orders->pluck('id');
                $orderItems = \App\Models\OrderItem::whereIn('order_id', $orderIds)->get();
                $variantIds = $orderItems->pluck('product_variant_id')->unique();
                $variants = \App\Models\ProductVariant::whereIn('id', $variantIds)->get()->keyBy('id');
                $totalRevenue = $orders->sum('final_price');
                $totalCost = 0;
                foreach ($orderItems as $item) {
                    $variant = $variants[$item->product_variant_id] ?? null;
                    if ($variant) {
                        $totalCost += $variant->cost_price * $item->quantity;
                    }
                }
                $revenueData[] = $totalRevenue;
                $profitData[] = $totalRevenue - $totalCost;
                $orderCountData[] = $orders->count(); // Số lượng đơn hàng trong tuần
            }
        } elseif ($type === 'month') {
            for ($i = 3; $i >= 0; $i--) {
                $month = $now->copy()->subMonths($i);
                $labels[] = 'Tháng ' . $month->format('m');
                $orders = \App\Models\Order::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->where('status', 'delivered')->get();
                $orderIds = $orders->pluck('id');
                $orderItems = \App\Models\OrderItem::whereIn('order_id', $orderIds)->get();
                $variantIds = $orderItems->pluck('product_variant_id')->unique();
                $variants = \App\Models\ProductVariant::whereIn('id', $variantIds)->get()->keyBy('id');
                $totalRevenue = $orders->sum('final_price');
                $totalCost = 0;
                foreach ($orderItems as $item) {
                    $variant = $variants[$item->product_variant_id] ?? null;
                    if ($variant) {
                        $totalCost += $variant->cost_price * $item->quantity;
                    }
                }
                $revenueData[] = $totalRevenue;
                $profitData[] = $totalRevenue - $totalCost;
                $orderCountData[] = $orders->count(); // Số lượng đơn hàng trong tháng
            }
        } else {
            for ($i = 3; $i >= 0; $i--) {
                $year = $now->copy()->subYears($i)->year;
                $labels[] = (string)$year;
                $orders = \App\Models\Order::whereYear('created_at', $year)->where('status', 'delivered')->get();
                $orderIds = $orders->pluck('id');
                $orderItems = \App\Models\OrderItem::whereIn('order_id', $orderIds)->get();
                $variantIds = $orderItems->pluck('product_variant_id')->unique();
                $variants = \App\Models\ProductVariant::whereIn('id', $variantIds)->get()->keyBy('id');
                $totalRevenue = $orders->sum('final_price');
                $totalCost = 0;
                foreach ($orderItems as $item) {
                    $variant = $variants[$item->product_variant_id] ?? null;
                    if ($variant) {
                        $totalCost += $variant->cost_price * $item->quantity;
                    }
                }
                $revenueData[] = $totalRevenue;
                $profitData[] = $totalRevenue - $totalCost;
                $orderCountData[] = $orders->count(); // Số lượng đơn hàng trong năm
            }
        }

        return response()->json([
            'labels' => $labels,
            'revenue' => $revenueData,
            'profit' => $profitData,
            'orderCount' => $orderCountData, // Thêm dữ liệu số lượng đơn hàng
        ]);
    }

    public function sellerStatsList(Request $request)
    {
        try {
            $user = $request->user();
            $seller = \App\Models\Seller::where('user_id', $user->id)->first();
            if (!$seller) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không phải seller hoặc chưa đăng nhập!'
                ], 403);
            }

            // Tính toán các thống kê
            $totalProducts = \App\Models\ProductVariant::whereHas('product', function($query) use ($seller) {
                $query->where('seller_id', $seller->id)
                    ->where('status', '!=', 'trash');
            })->count();

            $totalOrders = \App\Models\Order::whereHas('orderItems.productVariant.product', function($query) use ($seller) {
                $query->where('seller_id', $seller->id);
            })->count();

            $deliveredOrders = \App\Models\Order::whereHas('orderItems.productVariant.product', function($query) use ($seller) {
                $query->where('seller_id', $seller->id);
            })->where('status', 'delivered')->count();

            $totalRevenue = \App\Models\Order::whereHas('orderItems.productVariant.product', function($query) use ($seller) {
                $query->where('seller_id', $seller->id);
            })->where('status', 'delivered')->sum('final_price');

            // Tính tổng vốn và lợi nhuận
            $deliveredOrderIds = \App\Models\Order::whereHas('orderItems.productVariant.product', function($query) use ($seller) {
                $query->where('seller_id', $seller->id);
            })->where('status', 'delivered')->pluck('id');

            $orderItems = \App\Models\OrderItem::whereIn('order_id', $deliveredOrderIds)
                ->whereHas('productVariant.product', function($query) use ($seller) {
                    $query->where('seller_id', $seller->id);
                })->get();

            $totalCost = 0;
            foreach ($orderItems as $item) {
                $variant = $item->productVariant;
                if ($variant) {
                    $totalCost += $variant->cost_price * $item->quantity;
                }
            }

            $totalProfit = $totalRevenue - $totalCost;
            $totalLoss = $totalProfit < 0 ? abs($totalProfit) : 0;
            if ($totalProfit < 0) $totalProfit = 0;

            $stats = [
                [
                    'key' => 'total_products',
                    'label' => 'Tổng sản phẩm',
                    'value' => $totalProducts
                ],
                [
                    'key' => 'total_orders',
                    'label' => 'Tổng đơn hàng',
                    'value' => $totalOrders
                ],
                [
                    'key' => 'delivered_orders',
                    'label' => 'Đơn hàng đã bán',
                    'value' => $deliveredOrders
                ],
                [
                    'key' => 'total_revenue',
                    'label' => 'Tổng doanh thu',
                    'value' => $totalRevenue
                ],
                [
                    'key' => 'total_cost',
                    'label' => 'Tổng vốn đã bán',
                    'value' => $totalCost
                ],
                [
                    'key' => 'total_profit',
                    'label' => 'Tổng lợi nhuận',
                    'value' => $totalProfit
                ]
            ];

            $lossStats = [
                [
                    'key' => 'total_loss',
                    'label' => 'Tổng lỗ',
                    'value' => $totalLoss
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'lossStats' => $lossStats
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy thống kê: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sellerRevenueProfitChart(Request $request)
    {
        try {
            $user = $request->user();
            $seller = \App\Models\Seller::where('user_id', $user->id)->first();
            if (!$seller) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không phải seller hoặc chưa đăng nhập!'
                ], 403);
            }

            $type = $request->get('type', 'month');
            $now = now();
            $labels = [];
            $revenue = [];
            $profit = [];
            $orderCount = [];

            switch ($type) {
                case 'day':
                    for ($i = 6; $i >= 0; $i--) {
                        $date = $now->copy()->subDays($i);
                        $labels[] = $date->format('d/m');

                        $orders = \App\Models\Order::whereDate('created_at', $date->format('Y-m-d'))
                            ->where('status', 'delivered')
                            ->whereHas('orderItems.productVariant.product', function($query) use ($seller) {
                                $query->where('seller_id', $seller->id);
                            })->get();

                        $orderItems = \App\Models\OrderItem::whereIn('order_id', $orders->pluck('id'))
                            ->whereHas('productVariant.product', function($query) use ($seller) {
                                $query->where('seller_id', $seller->id);
                            })->get();

                        $totalRevenue = $orders->sum('final_price');
                        $totalCost = 0;
                        foreach ($orderItems as $item) {
                            $variant = $item->productVariant;
                            if ($variant) {
                                $totalCost += $variant->cost_price * $item->quantity;
                            }
                        }

                        $revenue[] = $totalRevenue;
                        $profit[] = $totalRevenue - $totalCost;
                        $orderCount[] = $orders->count();
                    }
                    break;

                case 'week':
                    for ($i = 3; $i >= 0; $i--) {
                        $start = $now->copy()->subWeeks($i)->startOfWeek();
                        $end = $now->copy()->subWeeks($i)->endOfWeek();
                        $labels[] = 'Tuần ' . $start->format('W');

                        $orders = \App\Models\Order::whereBetween('created_at', [$start, $end])
                            ->where('status', 'delivered')
                            ->whereHas('orderItems.productVariant.product', function($query) use ($seller) {
                                $query->where('seller_id', $seller->id);
                            })->get();

                        $orderItems = \App\Models\OrderItem::whereIn('order_id', $orders->pluck('id'))
                            ->whereHas('productVariant.product', function($query) use ($seller) {
                                $query->where('seller_id', $seller->id);
                            })->get();

                        $totalRevenue = $orders->sum('final_price');
                        $totalCost = 0;
                        foreach ($orderItems as $item) {
                            $variant = $item->productVariant;
                            if ($variant) {
                                $totalCost += $variant->cost_price * $item->quantity;
                            }
                        }

                        $revenue[] = $totalRevenue;
                        $profit[] = $totalRevenue - $totalCost;
                        $orderCount[] = $orders->count();
                    }
                    break;

                case 'month':
                    for ($i = 11; $i >= 0; $i--) {
                        $month = $now->copy()->subMonths($i);
                        $labels[] = $month->format('m/Y');

                        $orders = \App\Models\Order::whereYear('created_at', $month->year)
                            ->whereMonth('created_at', $month->month)
                            ->where('status', 'delivered')
                            ->whereHas('orderItems.productVariant.product', function($query) use ($seller) {
                                $query->where('seller_id', $seller->id);
                            })->get();

                        $orderItems = \App\Models\OrderItem::whereIn('order_id', $orders->pluck('id'))
                            ->whereHas('productVariant.product', function($query) use ($seller) {
                                $query->where('seller_id', $seller->id);
                            })->get();

                        $totalRevenue = $orders->sum('final_price');
                        $totalCost = 0;
                        foreach ($orderItems as $item) {
                            $variant = $item->productVariant;
                            if ($variant) {
                                $totalCost += $variant->cost_price * $item->quantity;
                            }
                        }

                        $revenue[] = $totalRevenue;
                        $profit[] = $totalRevenue - $totalCost;
                        $orderCount[] = $orders->count();
                    }
                    break;

                case 'year':
                    for ($i = 2; $i >= 0; $i--) {
                        $year = $now->copy()->subYears($i)->year;
                        $labels[] = (string)$year;

                        $orders = \App\Models\Order::whereYear('created_at', $year)
                            ->where('status', 'delivered')
                            ->whereHas('orderItems.productVariant.product', function($query) use ($seller) {
                                $query->where('seller_id', $seller->id);
                            })->get();

                        $orderItems = \App\Models\OrderItem::whereIn('order_id', $orders->pluck('id'))
                            ->whereHas('productVariant.product', function($query) use ($seller) {
                                $query->where('seller_id', $seller->id);
                            })->get();

                        $totalRevenue = $orders->sum('final_price');
                        $totalCost = 0;
                        foreach ($orderItems as $item) {
                            $variant = $item->productVariant;
                            if ($variant) {
                                $totalCost += $variant->cost_price * $item->quantity;
                            }
                        }

                        $revenue[] = $totalRevenue;
                        $profit[] = $totalRevenue - $totalCost;
                        $orderCount[] = $orders->count();
                    }
                    break;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'revenue' => $revenue,
                    'profit' => $profit,
                    'orderCount' => $orderCount
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy dữ liệu biểu đồ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách tất cả seller với thống kê doanh thu
     */
    public function getAllSellersStats(Request $request)
    {
        try {
            $query = \App\Models\Seller::with(['user'])
                ->where('verification_status', 'verified');

            // Tìm kiếm
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('store_name', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('email', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        });
                });
            }

            // Sắp xếp
            $sortBy = $request->get('sort', 'revenue_desc');
            switch ($sortBy) {
                case 'revenue_desc':
                    $query->orderByRaw('(SELECT COALESCE(SUM(orders.final_price), 0) FROM orders 
                        INNER JOIN order_items ON orders.id = order_items.order_id 
                        INNER JOIN product_variants ON order_items.product_variant_id = product_variants.id 
                        INNER JOIN products ON product_variants.product_id = products.id 
                        WHERE products.seller_id = sellers.id AND orders.status = "delivered") DESC');
                    break;
                case 'revenue_asc':
                    $query->orderByRaw('(SELECT COALESCE(SUM(orders.final_price), 0) FROM orders 
                        INNER JOIN order_items ON orders.id = order_items.order_id 
                        INNER JOIN product_variants ON order_items.product_variant_id = product_variants.id 
                        INNER JOIN products ON product_variants.product_id = products.id 
                        WHERE products.seller_id = sellers.id AND orders.status = "delivered") ASC');
                    break;
                case 'orders_desc':
                    $query->orderByRaw('(SELECT COUNT(DISTINCT orders.id) FROM orders 
                        INNER JOIN order_items ON orders.id = order_items.order_id 
                        INNER JOIN product_variants ON order_items.product_variant_id = product_variants.id 
                        INNER JOIN products ON product_variants.product_id = products.id 
                        WHERE products.seller_id = sellers.id) DESC');
                    break;
                case 'orders_asc':
                    $query->orderByRaw('(SELECT COUNT(DISTINCT orders.id) FROM orders 
                        INNER JOIN order_items ON orders.id = order_items.order_id 
                        INNER JOIN product_variants ON order_items.product_variant_id = product_variants.id 
                        INNER JOIN products ON product_variants.product_id = products.id 
                        WHERE products.seller_id = sellers.id) ASC');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }

            $sellers = $query->get();

            // Tính toán thống kê cho từng seller
            $sellersWithStats = $sellers->map(function ($seller) {
                // Tổng doanh thu
                $totalRevenue = \App\Models\Order::whereHas('orderItems.productVariant.product', function($query) use ($seller) {
                    $query->where('seller_id', $seller->id);
                })->where('status', 'delivered')->sum('final_price');

                // Tổng đơn hàng
                $totalOrders = \App\Models\Order::whereHas('orderItems.productVariant.product', function($query) use ($seller) {
                    $query->where('seller_id', $seller->id);
                })->count();

                // Đơn hàng đã bán
                $deliveredOrders = \App\Models\Order::whereHas('orderItems.productVariant.product', function($query) use ($seller) {
                    $query->where('seller_id', $seller->id);
                })->where('status', 'delivered')->count();

                // Tổng sản phẩm
                $totalProducts = \App\Models\Product::where('seller_id', $seller->id)
                    ->where('status', '!=', 'trash')
                    ->count();

                return [
                    'id' => $seller->id,
                    'store_name' => $seller->store_name,
                    'user' => [
                        'id' => $seller->user->id,
                        'name' => $seller->user->name,
                        'email' => $seller->user->email,
                        'status' => $seller->user->status,
                    ],
                    'total_revenue' => $totalRevenue,
                    'total_orders' => $totalOrders,
                    'delivered_orders' => $deliveredOrders,
                    'total_products' => $totalProducts,
                    'verification_status' => $seller->verification_status,
                    'created_at' => $seller->created_at,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $sellersWithStats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách seller: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách đơn hàng với thống kê - Tối ưu hóa
     */
    public function getAllOrdersStats(Request $request)
    {
        try {
            $query = Order::with(['user', 'orderItems.productVariant.product.seller', 'shipping']);

            // Filter theo trạng thái
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Filter theo ngày
            if ($request->has('from_date') && $request->from_date) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }
            if ($request->has('to_date') && $request->to_date) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }

            // Tìm kiếm theo tracking code
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->whereHas('shipping', function($q) use ($search) {
                    $q->where('tracking_code', 'like', "%{$search}%");
                });
            }

            // Sắp xếp
            $sortBy = $request->get('sort', 'created_at_desc');
            switch ($sortBy) {
                case 'created_at_desc':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'created_at_asc':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'final_price_desc':
                    $query->orderBy('final_price', 'desc');
                    break;
                case 'final_price_asc':
                    $query->orderBy('final_price', 'asc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }

            $orders = $query->paginate($request->get('per_page', 15));

            // Tối ưu: Chỉ tính stats nếu cần thiết
            $totalStats = null;
            if ($request->get('include_stats', true)) {
                $totalStats = DB::select("
                    SELECT 
                        COUNT(*) as total_orders,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                        SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
                        SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped_orders,
                        SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
                        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                        SUM(CASE WHEN status = 'delivered' THEN final_price ELSE 0 END) as total_revenue,
                        SUM(CASE WHEN status = 'delivered' THEN discount_price ELSE 0 END) as total_discount
                    FROM orders
                ")[0];
            }

            return response()->json([
                'success' => true,
                'data' => $orders->items(),
                'meta' => $orders->toArray(),
                'stats' => $totalStats ? (array)$totalStats : null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách người dùng đang hoạt động
     */
    public function getActiveUsersStats(Request $request)
    {
        try {
            $query = \App\Models\User::with(['seller']);

            // Chỉ lấy người dùng đang hoạt động
            $query->where('status', 'active');

            // Tìm kiếm
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // Filter theo role
            if ($request->has('role') && $request->role) {
                $query->where('role', $request->role);
            }

            // Sắp xếp
            $sortBy = $request->get('sort', 'created_at_desc');
            switch ($sortBy) {
                case 'created_at_desc':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'created_at_asc':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'name_asc':
                    $query->orderBy('name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('name', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }

            $users = $query->paginate($request->get('per_page', 15));

            // Tính toán thống kê cho từng user
            $usersWithStats = $users->getCollection()->map(function ($user) {
                // Tổng đơn hàng của user
                $totalOrders = \App\Models\Order::where('user_id', $user->id)->count();
                
                // Tổng tiền đã mua
                $totalSpent = \App\Models\Order::where('user_id', $user->id)
                    ->where('status', 'delivered')
                    ->sum('final_price');

                // Đơn hàng gần nhất
                $lastOrder = \App\Models\Order::where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                // Số sản phẩm đã review
                $totalReviews = \App\Models\Review::where('user_id', $user->id)->count();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'status' => $user->status,
                    'avatar' => $user->avatar,
                    'is_verified' => $user->is_verified,
                    'total_orders' => $totalOrders,
                    'total_spent' => $totalSpent,
                    'total_reviews' => $totalReviews,
                    'last_order_date' => $lastOrder ? $lastOrder->created_at : null,
                    'created_at' => $user->created_at,
                    'seller' => $user->seller ? [
                        'id' => $user->seller->id,
                        'store_name' => $user->seller->store_name,
                        'verification_status' => $user->seller->verification_status,
                    ] : null,
                ];
            });

            // Tính toán thống kê tổng quan
            $totalStats = [
                'total_users' => \App\Models\User::where('status', 'active')->count(),
                'total_customers' => \App\Models\User::where('status', 'active')->where('role', 'user')->count(),
                'total_sellers' => \App\Models\User::where('status', 'active')->where('role', 'seller')->count(),
                'total_admins' => \App\Models\User::where('status', 'active')->where('role', 'admin')->count(),
                'verified_users' => \App\Models\User::where('status', 'active')->where('is_verified', true)->count(),
                'unverified_users' => \App\Models\User::where('status', 'active')->where('is_verified', false)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $usersWithStats,
                'meta' => $users->toArray(),
                'stats' => $totalStats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách người dùng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy tổng quan thống kê hệ thống - Tối ưu hóa với ít queries hơn
     */
    public function getSystemOverview()
    {
        try {
            // Sử dụng raw queries để tối ưu performance
            $userStats = DB::select("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'active' AND role = 'user' THEN 1 ELSE 0 END) as customers,
                    SUM(CASE WHEN status = 'active' AND role = 'seller' THEN 1 ELSE 0 END) as sellers,
                    SUM(CASE WHEN status = 'active' AND role = 'admin' THEN 1 ELSE 0 END) as admins,
                    SUM(CASE WHEN status = 'active' AND is_verified = 1 THEN 1 ELSE 0 END) as verified
                FROM users
            ")[0];

            $orderStats = DB::select("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing,
                    SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped,
                    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                    SUM(CASE WHEN status = 'delivered' THEN final_price ELSE 0 END) as total_revenue,
                    SUM(CASE WHEN status = 'delivered' THEN discount_price ELSE 0 END) as total_discount
                FROM orders
            ")[0];

            $sellerStats = DB::select("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN verification_status = 'verified' THEN 1 ELSE 0 END) as verified,
                    SUM(CASE WHEN verification_status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN verification_status = 'rejected' THEN 1 ELSE 0 END) as rejected
                FROM sellers
            ")[0];

            $productStats = DB::select("
                SELECT 
                    SUM(CASE WHEN status != 'trash' THEN 1 ELSE 0 END) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                FROM products
            ")[0];

            $reviewStats = DB::select("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                FROM reviews
            ")[0];

            $stats = [
                'users' => [
                    'total' => (int)$userStats->total,
                    'active' => (int)$userStats->active,
                    'customers' => (int)$userStats->customers,
                    'sellers' => (int)$userStats->sellers,
                    'admins' => (int)$userStats->admins,
                    'verified' => (int)$userStats->verified,
                ],
                'orders' => [
                    'total' => (int)$orderStats->total,
                    'pending' => (int)$orderStats->pending,
                    'processing' => (int)$orderStats->processing,
                    'shipped' => (int)$orderStats->shipped,
                    'delivered' => (int)$orderStats->delivered,
                    'cancelled' => (int)$orderStats->cancelled,
                    'total_revenue' => (float)$orderStats->total_revenue,
                    'total_discount' => (float)$orderStats->total_discount,
                ],
                'sellers' => [
                    'total' => (int)$sellerStats->total,
                    'verified' => (int)$sellerStats->verified,
                    'pending' => (int)$sellerStats->pending,
                    'rejected' => (int)$sellerStats->rejected,
                ],
                'products' => [
                    'total' => (int)$productStats->total,
                    'active' => (int)$productStats->active,
                    'pending' => (int)$productStats->pending,
                    'rejected' => (int)$productStats->rejected,
                ],
                'reviews' => [
                    'total' => (int)$reviewStats->total,
                    'approved' => (int)$reviewStats->approved,
                    'pending' => (int)$reviewStats->pending,
                    'rejected' => (int)$reviewStats->rejected,
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy thống kê tổng quan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy dữ liệu biểu đồ đơn hàng theo thời gian
     */
    public function getOrdersChart(Request $request)
    {
        try {
            $type = $request->input('type', 'month');
            $sellerId = $request->input('seller_id');
            $year = $request->input('year');
            
            $labels = [];
            $data = [];
            
            switch ($type) {
                case 'day':
                    // Lấy dữ liệu 30 ngày gần nhất
                    for ($i = 29; $i >= 0; $i--) {
                        $date = Carbon::now()->subDays($i);
                        $labels[] = $date->format('d/m');
                        
                        if ($sellerId) {
                            // Lọc theo seller - join qua order_items và products
                            $count = Order::whereHas('orderItems.product', function($q) use ($sellerId) {
                                $q->where('seller_id', $sellerId);
                            })->whereDate('created_at', $date)->count();
                        } else {
                            // Tất cả đơn hàng
                            $count = Order::whereDate('created_at', $date)->count();
                        }
                        $data[] = $count;
                    }
                    break;
                    
                case 'month':
                    // Tính toán dữ liệu theo tháng một cách nhất quán
                    if ($year) {
                        // Nếu có year parameter, lấy dữ liệu cho năm cụ thể
                        for ($month = 1; $month <= 12; $month++) {
                            $labels[] = 'T' . str_pad($month, 2, '0', STR_PAD_LEFT);
                            
                            if ($sellerId) {
                                // Lọc theo seller - join qua order_items và products
                                $count = Order::whereHas('orderItems.product', function($q) use ($sellerId) {
                                    $q->where('seller_id', $sellerId);
                                })->whereYear('created_at', $year)
                                  ->whereMonth('created_at', $month)
                                  ->count();
                            } else {
                                // Tất cả đơn hàng theo tháng
                                $count = Order::whereYear('created_at', $year)
                                             ->whereMonth('created_at', $month)
                                             ->count();
                            }
                            $data[] = $count;
                        }
                    } else {
                        // Mặc định lấy 12 tháng gần nhất
                        for ($i = 11; $i >= 0; $i--) {
                            $date = Carbon::now()->subMonths($i);
                            $labels[] = 'T' . str_pad($date->month, 2, '0', STR_PAD_LEFT);
                            
                            if ($sellerId) {
                                // Lọc theo seller - join qua order_items và products
                                $count = Order::whereHas('orderItems.product', function($q) use ($sellerId) {
                                    $q->where('seller_id', $sellerId);
                                })->whereYear('created_at', $date->year)
                                  ->whereMonth('created_at', $date->month)
                                  ->count();
                            } else {
                                // Tất cả đơn hàng theo tháng
                                $count = Order::whereYear('created_at', $date->year)
                                             ->whereMonth('created_at', $date->month)
                                             ->count();
                            }
                            $data[] = $count;
                        }
                    }
                    
                    // Debug: Kiểm tra chi tiết từng tháng
                    $monthDetails = [];
                    for ($i = 11; $i >= 0; $i--) {
                        $date = Carbon::now()->subMonths($i);
                        $monthDetails[] = [
                            'index' => $i,
                            'date' => $date->format('Y-m'),
                            'label' => 'T' . str_pad($date->month, 2, '0', STR_PAD_LEFT),
                            'count' => $data[11 - $i] ?? 0
                        ];
                    }
                    
                    \Log::info("Monthly breakdown:", [
                        'labels' => $labels,
                        'data' => $data,
                        'total_sum' => array_sum($data),
                        'month_details' => $monthDetails
                    ]);
                    break;
                    
                case 'year':
                    // Lấy dữ liệu 5 năm gần nhất
                    for ($i = 4; $i >= 0; $i--) {
                        $year = Carbon::now()->subYears($i)->year;
                        $labels[] = $year;
                        
                        if ($sellerId) {
                            // Lọc theo seller - join qua order_items và products
                            $count = Order::whereHas('orderItems.product', function($q) use ($sellerId) {
                                $q->where('seller_id', $sellerId);
                            })->whereYear('created_at', $year)->count();
                        } else {
                            // Tất cả đơn hàng
                            $count = Order::whereYear('created_at', $year)->count();
                        }
                        $data[] = $count;
                    }
                    break;
            }
            
            // Debug: Kiểm tra tổng số đơn hàng
            $totalOrders = Order::count();
            $ordersInLast12Months = Order::where('created_at', '>=', Carbon::now()->subMonths(12))->count();
            $ordersOlderThan12Months = $totalOrders - $ordersInLast12Months;
            
            // Debug: Kiểm tra đơn hàng theo tháng cụ thể
            $monthlyBreakdown = [];
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $monthKey = $date->format('Y-m');
                $monthlyBreakdown[$monthKey] = Order::whereYear('created_at', $date->year)
                                                    ->whereMonth('created_at', $date->month)
                                                    ->count();
            }
            
            // Debug: Kiểm tra tất cả đơn hàng và tháng tạo
            $allOrders = Order::select('id', 'created_at')
                             ->orderBy('created_at', 'desc')
                             ->limit(10)
                             ->get();
            
            $orderDetails = $allOrders->map(function($order) {
                return [
                    'id' => $order->id,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'month' => $order->created_at->format('Y-m')
                ];
            });
            
            \Log::info("Debug Orders Chart:", [
                'total_orders' => $totalOrders,
                'orders_in_last_12_months' => $ordersInLast12Months,
                'orders_older_than_12_months' => $ordersOlderThan12Months,
                'chart_data_sum' => array_sum($data),
                'monthly_breakdown' => $monthlyBreakdown,
                'recent_orders' => $orderDetails
            ]);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'data' => $data
                ],
                'debug' => [
                    'total_orders' => $totalOrders,
                    'orders_in_last_12_months' => $ordersInLast12Months,
                    'orders_older_than_12_months' => $ordersOlderThan12Months,
                    'chart_data_sum' => array_sum($data),
                    'monthly_breakdown' => $monthlyBreakdown,
                    'recent_orders' => $orderDetails
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy dữ liệu biểu đồ đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy dữ liệu biểu đồ doanh thu theo tháng
     */
    public function getRevenueChart(Request $request)
    {
        try {
            $type = $request->input('type', 'month');
            $sellerId = $request->input('seller_id');
            $year = $request->input('year');
            
            $labels = [];
            $data = [];
            
            switch ($type) {
                case 'day':
                    // Lấy dữ liệu 30 ngày gần nhất
                    for ($i = 29; $i >= 0; $i--) {
                        $date = Carbon::now()->subDays($i);
                        $labels[] = $date->format('d/m');
                        
                        if ($sellerId) {
                            // Lọc theo seller - join qua order_items và products
                            $revenue = Order::whereHas('orderItems.product', function($q) use ($sellerId) {
                                $q->where('seller_id', $sellerId);
                            })->whereDate('created_at', $date)
                              ->where('status', 'delivered')
                              ->sum('final_price');
                        } else {
                            // Tất cả đơn hàng
                            $revenue = Order::whereDate('created_at', $date)
                                          ->where('status', 'delivered')
                                          ->sum('final_price');
                        }
                        $data[] = $revenue;
                    }
                    break;
                    
                case 'month':
                    // Tính toán doanh thu theo tháng
                    if ($year) {
                        // Nếu có year parameter, lấy dữ liệu cho năm cụ thể
                        for ($month = 1; $month <= 12; $month++) {
                            $labels[] = 'T' . str_pad($month, 2, '0', STR_PAD_LEFT);
                            
                            if ($sellerId) {
                                // Lọc theo seller - join qua order_items và products
                                $revenue = Order::whereHas('orderItems.product', function($q) use ($sellerId) {
                                    $q->where('seller_id', $sellerId);
                                })->whereYear('created_at', $year)
                                  ->whereMonth('created_at', $month)
                                  ->where('status', 'delivered')
                                  ->sum('final_price');
                            } else {
                                // Tất cả đơn hàng theo tháng
                                $revenue = Order::whereYear('created_at', $year)
                                              ->whereMonth('created_at', $month)
                                              ->where('status', 'delivered')
                                              ->sum('final_price');
                            }
                            $data[] = $revenue;
                        }
                    } else {
                        // Mặc định lấy 12 tháng gần nhất
                        for ($i = 11; $i >= 0; $i--) {
                            $date = Carbon::now()->subMonths($i);
                            $labels[] = 'T' . str_pad($date->month, 2, '0', STR_PAD_LEFT);
                            
                            if ($sellerId) {
                                // Lọc theo seller - join qua order_items và products
                                $revenue = Order::whereHas('orderItems.product', function($q) use ($sellerId) {
                                    $q->where('seller_id', $sellerId);
                                })->whereYear('created_at', $date->year)
                                  ->whereMonth('created_at', $date->month)
                                  ->where('status', 'delivered')
                                  ->sum('final_price');
                            } else {
                                // Tất cả đơn hàng theo tháng
                                $revenue = Order::whereYear('created_at', $date->year)
                                              ->whereMonth('created_at', $date->month)
                                              ->where('status', 'delivered')
                                              ->sum('final_price');
                            }
                            $data[] = $revenue;
                        }
                    }
                    break;
                    
                case 'year':
                    // Lấy dữ liệu 5 năm gần nhất
                    for ($i = 4; $i >= 0; $i--) {
                        $year = Carbon::now()->subYears($i)->year;
                        $labels[] = $year;
                        
                        if ($sellerId) {
                            // Lọc theo seller - join qua order_items và products
                            $revenue = Order::whereHas('orderItems.product', function($q) use ($sellerId) {
                                $q->where('seller_id', $sellerId);
                            })->whereYear('created_at', $year)
                              ->where('status', 'delivered')
                              ->sum('final_price');
                        } else {
                            // Tất cả đơn hàng
                            $revenue = Order::whereYear('created_at', $year)
                                          ->where('status', 'delivered')
                                          ->sum('final_price');
                        }
                        $data[] = $revenue;
                    }
                    break;
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'data' => $data
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy dữ liệu biểu đồ doanh thu: ' . $e->getMessage()
            ], 500);
        }
    }
}