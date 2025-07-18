<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

    /**
     * Thống kê dashboard cho seller hiện tại
     */
    public function sellerStatsList(Request $request)
    {
        $seller = \App\Models\Seller::where('user_id', $request->user()->id)->first();
        if (!$seller) {
            return response()->json([
                'stats' => [
                    [ 'label' => 'Tổng Đơn Hàng', 'key' => 'total_orders', 'value' => 0 ],
                    [ 'label' => 'Đơn Hàng Đã Bán', 'key' => 'sold_orders', 'value' => 0 ],
                    [ 'label' => 'Tổng Doanh Thu', 'key' => 'total_revenue', 'value' => 0 ],
                    [ 'label' => 'Tổng Vốn Đã Bán', 'key' => 'total_cost', 'value' => 0 ],
                    [ 'label' => 'Tổng Lợi Nhuận', 'key' => 'total_profit', 'value' => 0 ],
                ],
                'lossStats' => [
                    [ 'label' => 'Tổng Lỗ', 'key' => 'total_loss', 'value' => 0 ]
                ]
            ]);
        }
        $sellerId = $seller->id;
        $payoutOrderIds = \App\Models\Payout::where('seller_id', $sellerId)
            ->where('status', 'completed')
            ->pluck('order_id')
            ->unique()
            ->filter();
        // Đơn hàng đã bán (delivered) của seller đã payout
        $soldOrdersCount = \App\Models\Order::whereIn('id', $payoutOrderIds)->where('status', 'delivered')->count();
        // Tổng doanh thu các đơn hàng đã bán của seller đã payout
        $totalRevenue = \App\Models\Order::whereIn('id', $payoutOrderIds)->where('status', 'delivered')->sum('final_price');
        // Tổng vốn đã bán (tổng cost_price * quantity của các order_items thuộc đơn hàng delivered của seller đã payout)
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
        $stats = [
            [
                'label' => 'Tổng Đơn Hàng',
                'key' => 'total_orders',
                'value' => \App\Models\Order::whereIn('id', $payoutOrderIds)->count(),
            ],
            [
                'label' => 'Đơn Hàng Đã Bán',
                'key' => 'sold_orders',
                'value' => $soldOrdersCount,
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
     * API trả về dữ liệu doanh thu, lợi nhuận và số đơn hàng cho dashboard seller (theo ngày/tuần/tháng/năm)
     */
    public function sellerRevenueProfitChart(Request $request)
    {
        $seller = \App\Models\Seller::where('user_id', $request->user()->id)->first();
        if (!$seller) {
            return response()->json([
                'labels' => [],
                'revenue' => [],
                'profit' => [],
                'orderCount' => [],
            ]);
        }
        $sellerId = $seller->id;
        $type = $request->input('type', 'month'); // day|week|month|year
        $now = now();
        $labels = [];
        $revenueData = [];
        $profitData = [];
        $orderCountData = [];
        $payoutOrderIds = \App\Models\Payout::where('seller_id', $sellerId)
            ->where('status', 'completed')
            ->pluck('order_id')
            ->unique()
            ->filter();
        if ($type === 'day') {
            for ($i = 6; $i >= 0; $i--) {
                $date = $now->copy()->subDays($i)->format('Y-m-d');
                $labels[] = $now->copy()->subDays($i)->format('d/m');
                $orders = \App\Models\Order::whereIn('id', $payoutOrderIds)
                    ->whereDate('created_at', $date)
                    ->where('status', 'delivered')->get();
                $orderItems = \App\Models\OrderItem::whereIn('order_id', $orders->pluck('id'))->get();
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
                $orderCountData[] = $orders->count();
            }
        } elseif ($type === 'week') {
            for ($i = 3; $i >= 0; $i--) {
                $start = $now->copy()->subWeeks($i)->startOfWeek();
                $end = $now->copy()->subWeeks($i)->endOfWeek();
                $labels[] = 'Tuần ' . $start->format('W');
                $orders = \App\Models\Order::whereIn('id', $payoutOrderIds)
                    ->whereBetween('created_at', [$start, $end])
                    ->where('status', 'delivered')->get();
                $orderItems = \App\Models\OrderItem::whereIn('order_id', $orders->pluck('id'))->get();
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
                $orderCountData[] = $orders->count();
            }
        } elseif ($type === 'month') {
            for ($i = 3; $i >= 0; $i--) {
                $month = $now->copy()->subMonths($i);
                $labels[] = 'Tháng ' . $month->format('m');
                $orders = \App\Models\Order::whereIn('id', $payoutOrderIds)
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->where('status', 'delivered')->get();
                $orderItems = \App\Models\OrderItem::whereIn('order_id', $orders->pluck('id'))->get();
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
                $orderCountData[] = $orders->count();
            }
        } else {
            for ($i = 3; $i >= 0; $i--) {
                $year = $now->copy()->subYears($i)->year;
                $labels[] = (string)$year;
                $orders = \App\Models\Order::whereIn('id', $payoutOrderIds)
                    ->whereYear('created_at', $year)
                    ->where('status', 'delivered')->get();
                $orderItems = \App\Models\OrderItem::whereIn('order_id', $orders->pluck('id'))->get();
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
                $orderCountData[] = $orders->count();
            }
        }
        return response()->json([
            'labels' => $labels,
            'revenue' => $revenueData,
            'profit' => $profitData,
            'orderCount' => $orderCountData,
        ]);
    }
}