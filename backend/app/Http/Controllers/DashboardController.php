<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Thống kê dashboard cho admin
     */
    public function stats()
    {
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
        // Tổng thu thập (giả sử là tổng discount_price các đơn hàng delivered)
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
                'label' => 'Doanh Thu Từ Người Bán',
                'key' => 'seller_revenue',
                'value' => \App\Models\Order::whereHas('user', function($q){ $q->where('role', 'seller'); })->where('status', 'delivered')->sum('final_price'),
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
                'label' => 'Tổng lời',
                'key' => 'total_income',
                'value' => $totalProfit,
            ],
            [
                'label' => 'Tổng tiền giảm giá',
                'key' => 'total_discount',
                'value' => \App\Models\Order::where('status', 'delivered')->sum('discount_price'),
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
     * API trả về dữ liệu doanh thu cho dashboard (theo ngày/tuần/tháng/năm)
     */
   // app/Http/Controllers/DashboardController.php
public function revenueChart(Request $request)
{
    $type = $request->input('type', 'month'); // day|week|month|year
    $now = now();
    $labels = [];
    $revenue = [];
    
    if ($type === 'day') {
        // 7 ngày gần nhất
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i)->format('Y-m-d');
            $labels[] = $now->copy()->subDays($i)->format('d/m');
            $orders = \App\Models\Order::whereDate('created_at', $date)->where('status', 'delivered')->get();
            $revenue[] = $orders->sum('final_price');
        }
    } elseif ($type === 'week') {
        // 4 tuần gần nhất
        for ($i = 3; $i >= 0; $i--) {
            $start = $now->copy()->subWeeks($i)->startOfWeek();
            $end = $now->copy()->subWeeks($i)->endOfWeek();
            $labels[] = 'Tuần ' . $start->format('W');
            $orders = \App\Models\Order::whereBetween('created_at', [$start, $end])->where('status', 'delivered')->get();
            $revenue[] = $orders->sum('final_price');
        }
    } elseif ($type === 'month') {
        // 4 tháng gần nhất
        for ($i = 3; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $labels[] = 'Tháng ' . $month->format('m');
            $orders = \App\Models\Order::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->where('status', 'delivered')->get();
            $revenue[] = $orders->sum('final_price');
        }
    } else {
        // 4 năm gần nhất
        for ($i = 3; $i >= 0; $i--) {
            $year = $now->copy()->subYears($i)->year;
            $labels[] = (string)$year;
            $orders = \App\Models\Order::whereYear('created_at', $year)->where('status', 'delivered')->get();
            $revenue[] = $orders->sum('final_price');
        }
    }

    return response()->json([
        'labels' => $labels,
        'revenue' => $revenue,
    ]);
}

public function revenueProfitChart(Request $request)
{
    $type = $request->input('type', 'month'); // day|week|month|year
    $now = now();
    $labels = [];
    $profitData = [];
    $lossData = [];
    if ($type === 'day') {
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i)->format('Y-m-d');
            $labels[] = $now->copy()->subDays($i)->format('d/m');
            $orderIds = \App\Models\Order::whereDate('created_at', $date)->where('status', 'delivered')->pluck('id');
            $orderItems = \App\Models\OrderItem::whereIn('order_id', $orderIds)->get();
            $variantIds = $orderItems->pluck('product_variant_id')->unique();
            $variants = \App\Models\ProductVariant::whereIn('id', $variantIds)->get()->keyBy('id');
            $profit = 0;
            $loss = 0;
            foreach ($orderItems as $item) {
                $variant = $variants[$item->product_variant_id] ?? null;
                if ($variant) {
                    $sellPrice = $variant->sale_price !== null ? $variant->sale_price : $variant->price;
                    $diff = ($sellPrice - $variant->cost_price) * $item->quantity;
                    if ($diff >= 0) {
                        $profit += $diff;
                    } else {
                        $loss += abs($diff);
                    }
                }
            }
            $profitData[] = $profit;
            $lossData[] = -$loss;
        }
    } elseif ($type === 'week') {
        for ($i = 3; $i >= 0; $i--) {
            $start = $now->copy()->subWeeks($i)->startOfWeek();
            $end = $now->copy()->subWeeks($i)->endOfWeek();
            $labels[] = 'Tuần ' . $start->format('W');
            $orderIds = \App\Models\Order::whereBetween('created_at', [$start, $end])->where('status', 'delivered')->pluck('id');
            $orderItems = \App\Models\OrderItem::whereIn('order_id', $orderIds)->get();
            $variantIds = $orderItems->pluck('product_variant_id')->unique();
            $variants = \App\Models\ProductVariant::whereIn('id', $variantIds)->get()->keyBy('id');
            $profit = 0;
            $loss = 0;
            foreach ($orderItems as $item) {
                $variant = $variants[$item->product_variant_id] ?? null;
                if ($variant) {
                    $sellPrice = $variant->sale_price !== null ? $variant->sale_price : $variant->price;
                    $diff = ($sellPrice - $variant->cost_price) * $item->quantity;
                    if ($diff >= 0) {
                        $profit += $diff;
                    } else {
                        $loss += abs($diff);
                    }
                }
            }
            $profitData[] = $profit;
            $lossData[] = -$loss;
        }
    } elseif ($type === 'month') {
        for ($i = 3; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $labels[] = 'Tháng ' . $month->format('m');
            $orderIds = \App\Models\Order::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->where('status', 'delivered')->pluck('id');
            $orderItems = \App\Models\OrderItem::whereIn('order_id', $orderIds)->get();
            $variantIds = $orderItems->pluck('product_variant_id')->unique();
            $variants = \App\Models\ProductVariant::whereIn('id', $variantIds)->get()->keyBy('id');
            $profit = 0;
            $loss = 0;
            foreach ($orderItems as $item) {
                $variant = $variants[$item->product_variant_id] ?? null;
                if ($variant) {
                    $sellPrice = $variant->sale_price !== null ? $variant->sale_price : $variant->price;
                    $diff = ($sellPrice - $variant->cost_price) * $item->quantity;
                    if ($diff >= 0) {
                        $profit += $diff;
                    } else {
                        $loss += abs($diff);
                    }
                }
            }
            $profitData[] = $profit;
            $lossData[] = -$loss;
        }
    } else {
        for ($i = 3; $i >= 0; $i--) {
            $year = $now->copy()->subYears($i)->year;
            $labels[] = (string)$year;
            $orderIds = \App\Models\Order::whereYear('created_at', $year)->where('status', 'delivered')->pluck('id');
            $orderItems = \App\Models\OrderItem::whereIn('order_id', $orderIds)->get();
            $variantIds = $orderItems->pluck('product_variant_id')->unique();
            $variants = \App\Models\ProductVariant::whereIn('id', $variantIds)->get()->keyBy('id');
            $profit = 0;
            $loss = 0;
            foreach ($orderItems as $item) {
                $variant = $variants[$item->product_variant_id] ?? null;
                if ($variant) {
                    $sellPrice = $variant->sale_price !== null ? $variant->sale_price : $variant->price;
                    $diff = ($sellPrice - $variant->cost_price) * $item->quantity;
                    if ($diff >= 0) {
                        $profit += $diff;
                    } else {
                        $loss += abs($diff);
                    }
                }
            }
            $profitData[] = $profit;
            $lossData[] = -$loss;
        }
    }
    return response()->json([
        'labels' => $labels,
        'profit' => $profitData,
        'loss' => $lossData,
    ]);
}
}