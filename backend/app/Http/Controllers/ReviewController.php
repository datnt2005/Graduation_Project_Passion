<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;



class ReviewController extends Controller
{
    public function index(Request $request)
{
    $productId = $request->query('product_id');
    if (!$productId) {
        return response()->json(['message' => 'Thiếu product_id'], 400);
    }

    $reviews = Review::where('product_id', $productId)
        ->whereNull('parent_id')
        ->where('status', 'approved')
        ->orderByDesc('created_at')
        ->with(['reply']) // Không cần with('likes')
        ->withCount('likes') // Thêm lượt like theo dạng số
        ->get();

    // Tính tổng số lượt like của tất cả review
    $totalLikes = $reviews->sum('likes_count');

    // Tóm tắt
    $summary = [
        'rating' => round($reviews->avg('rating'), 1),
        'count' => $reviews->count(),
        'likes_count' => $totalLikes,
        'ratings' => [
            5 => $reviews->where('rating', 5)->count(),
            4 => $reviews->where('rating', 4)->count(),
            3 => $reviews->where('rating', 3)->count(),
            2 => $reviews->where('rating', 2)->count(),
            1 => $reviews->where('rating', 1)->count(),
        ]
    ];

    // Danh sách đánh giá
    $list = $reviews->map(function ($review) {
        return [
            'id' => $review->id,
            'user' => 'Ẩn danh',
            'joined' => 'Tháng 1, 2024',
            'totalReviews' => 5,
            'purchased' => true,
            'rating' => $review->rating,
            'content' => $review->content,
            'reply' => $review->reply ? [
                'id' => $review->reply->id,
                'content' => $review->reply->content,
                'created_at' => $review->reply->created_at->format('d/m/Y'),
            ] : null,
            'images' => [],
            'color' => 'Không rõ',
            'date' => Carbon::parse($review->created_at)->format('d/m/Y'),
            'usageTime' => '1 tuần trước',
            'likes_count' => $review->likes_count, // dùng đúng tên field from withCount()
        ];
    });

    return response()->json([
        'summary' => $summary,
        'list' => $list,
    ]);
}


    // Thêm đánh giá mới
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'content' => 'required|string|min:10|max:1000',
            'rating' => 'required|integer|min:1|max:5',
        ], [
            'product_id.required' => 'Thiếu mã sản phẩm.',
            'product_id.exists' => 'Sản phẩm không tồn tại.',
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

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Bạn cần đăng nhập để đánh giá.'], 401);
        }

        $userId = $user->id;

        // Kiểm tra người dùng đã mua sản phẩm này chưa (đơn hàng đã thanh toán)
        $hasPurchased = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.user_id', $userId)
            ->where('orders.status', 'completed')
            ->where('order_items.product_id', $request->product_id)
            ->exists();

        if (!$hasPurchased) {
            return response()->json([
                'message' => 'Bạn chỉ có thể đánh giá sản phẩm khi đã mua hàng.',
            ], 403);
        }

        // Tạo đánh giá mới
        $review = Review::create([
            'product_id' => $request->product_id,
            'user_id' => $userId,
            'content' => $request->content,
            'rating' => $request->rating,
            'parent_id' => null,
            'status' => 'approved', // hoặc để 'pending' nếu cần duyệt
        ]);

        return response()->json([
            'message' => 'Gửi đánh giá thành công.',
            'review' => $review
        ], 201);
    }

    // Cập nhật đánh giá
    public function update(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        $request->validate([
            'content' => 'required|string|min:10|max:1000',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $userId = $request->user()->id;

        // Kiểm tra quyền chỉnh sửa
        if ($review->user_id != $userId) {
            return response()->json(['message' => 'Bạn không có quyền sửa đánh giá này.'], 403);
        }

        // Kiểm tra đã mua hàng hay chưa
        $hasPurchased = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.user_id', $userId)
            ->where('orders.status', 'completed')
            ->where('order_items.product_id', $review->product_id)
            ->exists();

        if (!$hasPurchased) {
            return response()->json(['message' => 'Bạn cần mua sản phẩm này mới có thể sửa đánh giá.'], 403);
        }

        $review->update([
            'content' => $request->content,
            'rating' => $request->rating,
        ]);

        return response()->json(['message' => 'Cập nhật đánh giá thành công', 'review' => $review]);
    }


    // Xóa đánh giá
    public function destroy(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        $userId = $request->user()->id;

        // Chỉ chủ đánh giá mới được xóa
        if ($userId !== $review->user_id) {
            return response()->json(['message' => 'Bạn không có quyền xóa đánh giá này.'], 403);
        }

        $review->delete();

        return response()->json(['message' => 'Xóa đánh giá thành công.']);
    }

    public function reply(Request $request, $reviewId)
    {
        $request->validate([
            'content' => 'required|string|min:3',
        ]);

        $user = Auth::user();

        // ✅ Kiểm tra quyền người bán
        if (!$user || $user->role !== 'seller') {
            return response()->json(['message' => 'Chỉ người bán mới được trả lời đánh giá.'], 403);
        }

        $parentReview = Review::with('reply')->findOrFail($reviewId);

        // ✅ Nếu đã có phản hồi thì từ chối
        if ($parentReview->reply) {
            return response()->json(['message' => 'Đánh giá này đã được phản hồi.'], 400);
        }

        // ✅ Tạo phản hồi
        $reply = Review::create([
            'product_id' => $parentReview->product_id,
            'user_id'    => $user->id,
            'parent_id'  => $parentReview->id,
            'content'    => $request->content,
            'rating'     => 0,
            'status'     => 'approved',
        ]);

        return response()->json([
            'message' => 'Phản hồi thành công.',
            'reply'   => [
                'id'         => $reply->id,
                'content'    => $reply->content,
                'created_at' => $reply->created_at->format('d/m/Y'),
            ]
        ]);
    }


    public function like($reviewId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Vui lòng đăng nhập để thích.'], 401);
        }

        $review = Review::findOrFail($reviewId);

        // Kiểm tra đã like chưa
        $liked = ReviewLike::where('user_id', $user->id)->where('review_id', $reviewId)->first();

        if ($liked) {
            return response()->json(['message' => 'Bạn đã thích đánh giá này.'], 400);
        }

        // Tạo like mới
        ReviewLike::create([
            'user_id' => $user->id,
            'review_id' => $reviewId,
        ]);

        return response()->json([
            'message' => 'Đã thích đánh giá.',
            'likes' => $review->likes()->count()
        ]);
    }

    public function unlike($reviewId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Vui lòng đăng nhập để thích.'], 401);
        }

        $review = Review::findOrFail($reviewId);

        // Kiem tra da like chua
        $liked = ReviewLike::where('user_id', $user->id)->where('review_id', $reviewId)->first();

        if (!$liked) {
            return response()->json(['message' => 'Bạn chua thích đánh giá này.'], 400);
        }

        // Xoa like
        $liked->delete();

        return response()->json([
            'message' => 'Đã hủy thích đánh giá.',
            'likes' => $review->likes()->count()
        ]);
    }
}
