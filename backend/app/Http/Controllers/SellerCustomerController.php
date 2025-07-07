<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Product;
use App\Models\Review;

class SellerCustomerController extends Controller
{
    public function index(Request $request)
    {
        $sellerId = auth()->user()->seller->id;
        $type = $request->query('type');

        // === MẶC ĐỊNH: Khách đã mua ===
        if (!$type || $type === 'ordered') {
            $orders = DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('products.seller_id', $sellerId)
                ->whereNull('orders.deleted_at')
                ->select(
                    'orders.user_id',
                    DB::raw('COUNT(DISTINCT orders.id) as order_count'),
                    DB::raw('SUM(order_items.price * order_items.quantity) as total_spent'),
                    DB::raw('MAX(orders.created_at) as last_order_date')
                )
                ->groupBy('orders.user_id')
                ->get();

            $customers = $orders->map(function ($item) {
                $user = User::find($item->user_id);
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'order_count' => (int) $item->order_count,
                    'total_spent' => (float) $item->total_spent,
                    'last_order_date' => $item->last_order_date,
                    'followed' => false,
                    'reviewed' => false,
                    'messaged' => false,
                    'type' => 'ordered'
                ];
            });

            return response()->json($customers);
        }

        // === FOLLOW ONLY ===
        if ($type === 'follow_only') {
            $followers = DB::table('seller_followers')
                ->where('seller_id', $sellerId)
                ->join('users', 'users.id', '=', 'seller_followers.user_id')
                ->select('users.id', 'users.name', 'users.email', 'users.avatar')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar' => $user->avatar,
                        'order_count' => null,
                        'total_spent' => null,
                        'last_order_date' => null,
                        'followed' => true,
                        'reviewed' => false,
                        'messaged' => false,
                        'type' => 'follow_only'
                    ];
                });

            return response()->json($followers);
        }

        // === REVIEWED ===
        if ($type === 'reviewed') {
            $productIds = Product::where('seller_id', $sellerId)->pluck('id');

            $reviews = Review::whereIn('product_id', $productIds)
                ->with('user:id,name,email,avatar')
                ->get()
                ->groupBy('user_id')
                ->map(function ($items, $userId) {
                    $user = $items->first()->user;
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar' => $user->avatar,
                        'order_count' => null,
                        'total_spent' => null,
                        'last_order_date' => null,
                        'followed' => false,
                        'reviewed' => true,
                        'messaged' => false,
                        'type' => 'reviewed'
                    ];
                })->values();

            return response()->json($reviews);
        }

        // === MESSAGED ===
        if ($type === 'messaged') {
            $userIds = DB::table('chat_sessions')
                ->where('seller_id', $sellerId)
                ->pluck('user_id');

            $messaged = User::whereIn('id', $userIds)
                ->select('id', 'name', 'email', 'avatar')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar' => $user->avatar,
                        'order_count' => null,
                        'total_spent' => null,
                        'last_order_date' => null,
                        'followed' => false,
                        'reviewed' => false,
                        'messaged' => true,
                        'type' => 'messaged'
                    ];
                });

            return response()->json($messaged);
        }

        return response()->json(['message' => 'Loại khách hàng không hợp lệ.'], 400);
    }
}
