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
        $user = Auth::user();
        $seller = Seller::findOrFail($sellerId);

        // Kiểm tra đã theo dõi chưa
        if ($user->followedSellers()->where('seller_id', $sellerId)->exists()) {
            return response()->json(['message' => 'Bạn đã theo dõi seller này rồi.'], 409);
        }

        $user->followedSellers()->attach($sellerId);
        return response()->json(['message' => 'Theo dõi thành công!']);
    }

    // Bỏ theo dõi một seller
    public function unfollow($sellerId)
    {
        $user = Auth::user();
        $user->followedSellers()->detach($sellerId);
        return response()->json(['message' => 'Đã bỏ theo dõi seller.']);
    }

    // Danh sách seller mà user này đã theo dõi
    public function myFollows()
    {
        $user = Auth::user();
        $sellers = $user->followedSellers()->get();
        return response()->json($sellers);
    }

    // Danh sách user đang theo dõi 1 seller
    public function followersOfSeller($sellerId)
    {
        $seller = Seller::findOrFail($sellerId);
        $users = $seller->followers()->get();
        return response()->json($users);
    }
}
