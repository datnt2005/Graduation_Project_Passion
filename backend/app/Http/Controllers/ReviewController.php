<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;



class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $productId = $request->query('product_id');
        $reviews = Review::where('product_id', $productId)->whereNull('parent_id')->with(['replies.user'])->where('status', 'approved')->latest()->get();
        return response()->json($reviews);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'user_id' => 'required|exists:users,id',
            'content' => 'required|string|min:10|max:1000',
            'rating' => 'required|integer|min:1|max:5',
        ], [
            'product_id.required' => 'Thiếu mã sản phẩm.',
            'product_id.exists' => 'Sản phẩm không tồn tại.',
            'user_id.required' => 'Thiếu người dùng.',
            'user_id.exists' => 'Người dùng không tồn tại.',
            'content.required' => 'Vui lòng nhập nội dung đánh giá.',
            'content.min' => 'Nội dung đánh giá quá ngắn (ít nhất 10 ký tự).',
            'content.max' => 'Nội dung đánh giá quá dài (tối đa 1000 ký tự).',
            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
            'rating.integer' => 'Số sao không hợp lệ.',
            'rating.min' => 'Đánh giá ít nhất 1 sao.',
            'rating.max' => 'Đánh giá tối đa 5 sao.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Kiểm tra người dùng đã mua sản phẩm này hay chưa
        $hasPurchased = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.user_id', $request->user_id)
            ->where('orders.status', '!=', 'completed') // chỉ tính đơn hàng đã xác nhận/trả tiền
            ->where('order_items.product_id', $request->product_id)
            ->exists();

        if (!$hasPurchased) {
            return response()->json([
                'message' => 'Bạn chỉ có thể đánh giá sản phẩm khi đã mua hàng.',
            ], 403);
        }

        // Nếu đã mua rồi, tạo đánh giá
        $review = Review::create([
            'product_id' => $request->product_id,
            'user_id' => $request->user_id,
            'content' => $request->content,
            'rating' => $request->rating,
            'parent_id' => null,
            'status' => 'approved',
        ]);

        return response()->json([
            'message' => 'Gửi đánh giá thành công.',
            'review' => $review
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        // Validate đầu vào
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'content' => 'required|string|min:10|max:1000',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $userId = $request->user_id;

        // Kiểm tra quyền sở hữu đánh giá
        if ($review->user_id != $userId) {
            return response()->json(['message' => 'Bạn không có quyền sửa đánh giá này.'], 403);
        }

        // Kiểm tra đã mua hàng chưa
        $hasPurchased = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.user_id', $userId)
            ->where('order_items.product_id', $review->product_id)
            ->whereNotIn('orders.status', ['completed']) // Đảm bảo đã thanh toán
            ->exists();

        if (!$hasPurchased) {
            return response()->json(['message' => 'Bạn cần mua sản phẩm này mới có thể sửa đánh giá.'], 403);
        }

        // Cập nhật đánh giá
        $review->update([
            'content' => $request->content,
            'rating' => $request->rating,
        ]);

        return response()->json(['message' => 'Cập nhật đánh giá thành công', 'review' => $review]);
    }



    public function destroy(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = \App\Models\User::find($request->user_id);

        if ($user->id !== $review->user_id && $user->role !== 'admin') {
            return response()->json(['message' => 'Bạn không có quyền xóa đánh giá này.'], 403);
        }

        $review->delete();
        return response()->json(['message' => 'Xóa đánh giá thành công.']);
    }


    // public function like($id)
    // {
    //     $user = Auth::user();
    //     $review = Review::findOrFail($id);

    //     $like = DB::table('review_likes')
    //         ->where('user_id', $user->id)
    //         ->where('review_id', $id)
    //         ->first();

    //     if ($like) {
    //         // Hủy like
    //         DB::table('review_likes')->where('id', $like->id)->delete();
    //         $review->decrement('likes');

    //         return response()->json(['message' => 'Đã hủy like.']);
    //     } else {
    //         // Like
    //         DB::table('review_likes')->insert([
    //             'user_id' => $user->id,
    //             'review_id' => $id,
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);
    //         $review->increment('likes');

    //         return response()->json(['message' => 'Đã like.']);
    //     }
    // }


    public function like(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        $userId = $request->input('user_id');
        if (!$userId) {
            return response()->json(['message' => 'Bạn cần đăng nhập để like đánh giá'], 400);
        }

        $existingLike = DB::table('review_likes')
            ->where('user_id', $userId)
            ->where('review_id', $id)
            ->first();

        if ($existingLike) {
            // Hủy like
            DB::table('review_likes')->where('id', $existingLike->id)->delete();
            $review->decrement('likes');

            return response()->json(['message' => 'Đã hủy like']);
        } else {
            // Thêm like
            DB::table('review_likes')->insert([
                'user_id' => $userId,
                'review_id' => $id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $review->increment('likes');

            return response()->json(['message' => 'Đã like']);
        }
    }



    public function reply(Request $request, $id)
    {
        $parent = Review::findOrFail($id);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'content' => 'required|string|min:3'
        ]);

        $user = \App\Models\User::find($request->user_id);

        if (!$user || $user->role !== 'seller') {
            return response()->json(['message' => 'Chỉ người bán mới được trả lời đánh giá.'], 403);
        }

        $reply = Review::create([
            'product_id' => $parent->product_id,
            'user_id' => $user->id,
            'parent_id' => $parent->id,
            'content' => $request->content,
            'rating' => 0,
            'status' => 'approved',
        ]);

        return response()->json(['message' => 'Trả lời thành công', 'reply' => $reply]);
    }
}
