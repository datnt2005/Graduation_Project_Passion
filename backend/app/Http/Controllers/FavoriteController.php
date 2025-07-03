<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class FavoriteController extends Controller
{
    /**
     * Thêm hoặc xoá sản phẩm khỏi danh sách yêu thích của người dùng.
     */
    public function toggle(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
            ], [
                'product_id.required' => 'Thiếu mã sản phẩm.',
                'product_id.exists' => 'Sản phẩm không tồn tại.',
            ]);

            $userId = Auth::id();
            $productId = $validated['product_id'];

            $existing = Wishlist::where('user_id', $userId)
                ->where('product_id', $productId)
                ->first();

            if ($existing) {
                $existing->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Đã xoá khỏi yêu thích',
                    'favorited' => false
                ]);
            } else {
                Wishlist::create([
                    'user_id' => $userId,
                    'product_id' => $productId,
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'Đã thêm vào yêu thích',
                    'favorited' => true
                ]);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách sản phẩm yêu thích của người dùng.
     */
    public function list()
    {
        try {
            $userId = Auth::id();
            $favorites = Wishlist::with('product')->where('user_id', $userId)->get();

            return response()->json([
                'success' => true,
                'data' => $favorites
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy danh sách yêu thích',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
