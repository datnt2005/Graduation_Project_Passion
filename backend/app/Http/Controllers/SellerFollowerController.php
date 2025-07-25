<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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

            // Attach user to followers
            $user->followedSellers()->attach($sellerId);

            // Clear cache for this seller
            Cache::store('redis')->tags(['seller', $seller->store_slug])->flush();
            Log::info('Cache cleared for follow', [
                'seller_id' => $sellerId,
                'user_id' => $user->id,
                'store_slug' => $seller->store_slug,
            ]);

            // Update followers count cache
            $followersCountKey = "seller_followers_count_{$seller->store_slug}";
            Cache::store('redis')->put($followersCountKey, $seller->followers()->count(), now()->addHours(24));

            return response()->json([
                'success' => true,
                'message' => 'Theo dõi thành công!',
                'data' => [
                    'is_following' => true,
                    'followers_count' => Cache::store('redis')->get($followersCountKey, 0),
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in follow: ' . $e->getMessage(), ['seller_id' => $sellerId]);
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

            // Detach user from followers
            $user->followedSellers()->detach($sellerId);

            // Clear cache for this seller
            Cache::store('redis')->tags(['seller', $seller->store_slug])->flush();
            Log::info('Cache cleared for unfollow', [
                'seller_id' => $sellerId,
                'user_id' => $user->id,
                'store_slug' => $seller->store_slug,
            ]);

            // Update followers count cache
            $followersCountKey = "seller_followers_count_{$seller->store_slug}";
            Cache::store('redis')->put($followersCountKey, $seller->followers()->count(), now()->addHours(24));

            return response()->json([
                'success' => true,
                'message' => 'Đã bỏ theo dõi cửa hàng.',
                'data' => [
                    'is_following' => false,
                    'followers_count' => Cache::store('redis')->get($followersCountKey, 0),
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in unfollow: ' . $e->getMessage(), ['seller_id' => $sellerId]);
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

            $cacheKey = "user_followed_sellers_{$user->id}";
            Log::debug('Cache key for myFollows', ['cache_key' => $cacheKey]);

            $sellers = Cache::store('redis')->remember($cacheKey, now()->addMinutes(60), function () use ($user) {
                return $user->followedSellers()
                    ->select('sellers.id', 'sellers.store_name', 'sellers.store_slug', 'sellers.user_id')
                    ->with([
                        'user' => fn($q) => $q->select('id', 'avatar'),
                        'business' => fn($q) => $q->select('id', 'name', 'company_address', 'description', 'tax_code'),
                    ])
                    ->get()
                    ->map(function ($seller) {
                        return [
                            'id' => $seller->id,
                            'store_name' => $seller->store_name ?? 'N/A',
                            'store_slug' => $seller->store_slug,
                            'avatar' => $seller->user->avatar ?? 'avatars/default.jpg',
                            'business' => $seller->business ? [
                                'name' => $seller->business->name ?? 'N/A',
                                'address' => $seller->business->company_address ?? 'N/A',
                                'description' => $seller->business->description ?? 'N/A',
                                'tax_code' => $seller->business->tax_code ?? 'N/A',
                            ] : null,
                            'followers_count' => Cache::store('redis')->get("seller_followers_count_{$seller->store_slug}", $seller->followers()->count()),
                        ];
                    })->toArray();
            });

            Log::info('myFollows cache status', [
                'cache_key' => $cacheKey,
                'cache_hit' => Cache::store('redis')->has($cacheKey) ? 'hit' : 'miss',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách cửa hàng đã theo dõi thành công.',
                'data' => $sellers,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in myFollows: ' . $e->getMessage(), ['user_id' => Auth::id()]);
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
            $cacheKey = "seller_followers_{$seller->store_slug}";
            Log::debug('Cache key for followersOfSeller', ['cache_key' => $cacheKey]);

            $users = Cache::store('redis')->tags(['seller', $seller->store_slug])->remember($cacheKey, now()->addMinutes(60), function () use ($seller) {
                return $seller->followers()
                    ->select('users.id', 'users.name', 'users.avatar')
                    ->get()
                    ->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->name ?? 'N/A',
                            'avatar' => $user->avatar ?? 'avatars/default.jpg',
                        ];
                    })->toArray();
            });

            Log::info('followersOfSeller cache status', [
                'cache_key' => $cacheKey,
                'cache_hit' => Cache::store('redis')->has($cacheKey) ? 'hit' : 'miss',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách người theo dõi thành công.',
                'data' => $users,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in followersOfSeller: ' . $e->getMessage(), ['seller_id' => $sellerId]);
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách người theo dõi.',
                'error' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }
}