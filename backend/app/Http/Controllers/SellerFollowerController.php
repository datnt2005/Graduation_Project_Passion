<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SellerFollowerController extends Controller
{
    // Follow một seller
    public function follow($sellerId)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn cần đăng nhập để theo dõi cửa hàng.',
                ], 401);
            }

            $seller = Seller::findOrFail($sellerId);

            // Kiểm tra xem người dùng có phải là chủ cửa hàng không
            if ($user->id === $seller->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không thể theo dõi cửa hàng của chính mình.',
                ], 403);
            }

            // Kiểm tra đã theo dõi chưa
            if ($user->followedSellers()->where('seller_id', $sellerId)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã theo dõi cửa hàng này rồi.',
                ], 409);
            }

            $user->followedSellers()->attach($sellerId);
            return response()->json([
                'success' => true,
                'message' => 'Theo dõi thành công!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi theo dõi cửa hàng.',
                'error' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }

    // Bỏ theo dõi một seller
    public function unfollow($sellerId)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn cần đăng nhập để bỏ theo dõi cửa hàng.',
                ], 401);
            }

            $seller = Seller::findOrFail($sellerId);

            // Kiểm tra xem người dùng có phải là chủ cửa hàng không
            if ($user->id === $seller->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không thể bỏ theo dõi cửa hàng của chính mình.',
                ], 403);
            }

            // Kiểm tra xem người dùng có đang theo dõi không
            if (!$user->followedSellers()->where('seller_id', $sellerId)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn chưa theo dõi cửa hàng này.',
                ], 400);
            }

            $user->followedSellers()->detach($sellerId);
            return response()->json([
                'success' => true,
                'message' => 'Đã bỏ theo dõi cửa hàng.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi bỏ theo dõi cửa hàng.',
                'error' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }

    // Danh sách seller mà user này đã theo dõi
    public function myFollows()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn cần đăng nhập để xem danh sách cửa hàng đã theo dõi.',
                ], 401);
            }

            $sellers = $user->followedSellers()->get();
            return response()->json([
                'success' => true,
                'data' => $sellers,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách cửa hàng đã theo dõi.',
                'error' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }

    // Danh sách user đang theo dõi 1 seller
    public function followersOfSeller($sellerId)
    {
        try {
            $seller = Seller::findOrFail($sellerId);
            $users = $seller->followers()->get();
            return response()->json([
                'success' => true,
                'data' => $users,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách người theo dõi.',
                'error' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }
}