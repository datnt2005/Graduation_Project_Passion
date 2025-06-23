<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;




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
            ->with(['reply', 'media']) // Thêm media vào here
            ->withCount('likes')
            ->get();

        $totalLikes = $reviews->sum('likes_count');

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

        $list = $reviews->map(function ($review) {
            $images = $review->media
                ->where('media_type', 'image')
                ->map(fn($m) => [
                    'id' => $m->id,
                    'url' => Storage::disk('r2')->url($m->media_url),
                ])
                ->values();

            $videos = $review->media
                ->where('media_type', 'video')
                ->map(fn($m) => [
                    'id' => $m->id,
                    'url' => Storage::disk('r2')->url($m->media_url),
                ])
                ->values();


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
                    'created_at' => $review->reply->created_at->toISOString(),
                ] : null,
                'images' => $images,
                'videos' => $videos,
                'color' => 'Không rõ',
                'created_at' => $review->created_at->toISOString(),
                'usageTime' => '1 tuần trước',
                'likes_count' => $review->likes_count,
            ];
        });

        return response()->json([
            'summary' => $summary,
            'list' => $list,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'content' => 'required|string|min:10|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'videos.*' => 'nullable|mimes:mp4,mkv,avi|max:10240',  // Thêm validation cho video
        ], [
            'images.*.image' => 'Tệp phải là hình ảnh.',
            'images.*.mimes' => 'Hình ảnh phải có định dạng jpeg, png, jpg, gif, svg hoặc webp.',
            'images.*.max' => 'Hình ảnh không được vượt quá 2MB.',
            'videos.*.mimes' => 'Tệp video phải có định dạng mp4, mkv, avi.',
            'videos.*.max' => 'Video không được vượt quá 10MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đăng nhập để đánh giá.'
            ], 401);
        }

        $productId = $request->input('product_id');

        // Kiểm tra người dùng đã mua hàng chưa
        $hasPurchased = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.user_id', $user->id)
            ->where('orders.status', 'completed')
            ->where('order_items.product_id', $productId)
            ->exists();

        if (!$hasPurchased) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chỉ có thể đánh giá khi đã mua sản phẩm này.'
            ], 403);
        }

        // Kiểm tra trùng đánh giá
        $existingReview = DB::table('reviews')
            ->where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã đánh giá sản phẩm này rồi.'
            ], 409);
        }

        try {
            // Tạo đánh giá
            $review = Review::create([
                'user_id' => $user->id,
                'product_id' => $productId,
                'rating' => $request->input('rating'),
                'content' => $request->input('content'),
                'status' => 'approved',
            ]);

            // Lưu ảnh/video vào bảng review_media
            if ($request->hasFile('images') || $request->hasFile('videos')) {
                $mediaUrls = [];

                // Xử lý ảnh
                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $file) {
                        $filename = 'reviews/' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        Storage::disk('r2')->put($filename, file_get_contents($file));
                        $mediaUrls[] = ['media_url' => $filename, 'media_type' => 'image'];
                    }
                }

                // Xử lý video
                if ($request->hasFile('videos')) {
                    foreach ($request->file('videos') as $file) {
                        $filename = 'reviews/' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        Storage::disk('r2')->put($filename, file_get_contents($file));
                        $mediaUrls[] = ['media_url' => $filename, 'media_type' => 'video'];
                    }
                }

                // Lưu vào bảng review_media
                foreach ($mediaUrls as $media) {
                    $review->media()->create($media);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Đánh giá đã được gửi thành công.',
                'data' => $review
            ], 201);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lưu đánh giá: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gửi đánh giá thất bại.',
                'error' => env('APP_DEBUG', false) ? $e->getMessage() : null
            ], 500);
        }
    }


    // Thêm đánh giá mới
    public function update(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'content' => 'required|string|min:10|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'kept_images' => 'nullable|array',
            'kept_images.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($review->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Bạn không có quyền sửa đánh giá này.'], 403);
        }

        // Kiểm tra đã mua sản phẩm
        $hasPurchased = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.user_id', $request->user()->id)
            ->where('orders.status', 'completed')
            ->where('order_items.product_id', $review->product_id)
            ->exists();

        if (!$hasPurchased) {
            return response()->json(['message' => 'Bạn cần mua sản phẩm này để chỉnh sửa đánh giá.'], 403);
        }

        $review->update([
            'content' => $request->content,
            'rating' => $request->rating,
        ]);

        try {
            $mediaUrls = [];

            // Xoá ảnh không giữ lại
            $keptImageIds = $request->input('kept_images', []);
            $review->media()
                ->where('media_type', 'image')
                ->whereNotIn('id', $keptImageIds)
                ->delete();

            // Upload ảnh mới
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $filename = 'reviews/' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    Storage::disk('r2')->put($filename, file_get_contents($file));
                    $mediaUrls[] = ['media_url' => $filename, 'media_type' => 'image'];
                }
            }

            // Lưu media
            foreach ($mediaUrls as $media) {
                $review->media()->create($media);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật đánh giá thành công.',
                'review' => $review->load('media'),
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật media: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi khi cập nhật media.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
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

    // ReviewController.php
    public function checkLiked($reviewId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['liked' => false]);
        }

        $liked = ReviewLike::where('user_id', $user->id)
            ->where('review_id', $reviewId)
            ->exists();

        return response()->json(['liked' => $liked]);
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

    public function adminIndex()
    {
        try {
            $reviews = Review::with([
                'product:id,name',
                'product.pics:id,product_id,imagePath',
                'media',
                'reply'
            ])
                ->withCount('likes')
                ->whereNull('parent_id')
                ->latest()
                ->get()
                ->map(function ($review) {
                    $productImage = optional($review->product?->pics->first())->imagePath;

                    return [
                        'id' => $review->id,
                        'product_name' => optional($review->product)->name,
                        'product_image' => $productImage ? Storage::disk('r2')->url($productImage) : null,
                        'content' => $review->content,
                        'rating' => $review->rating,
                        'status' => $review->status,
                        'created_at' => $review->created_at,
                        'likes_count' => $review->likes_count,
                        'images' => $review->media
                            ->where('media_type', 'image')
                            ->map(fn($m) => Storage::disk('r2')->url($m->media_url))
                            ->values(),
                        'reply' => $review->reply ? [
                            'id' => $review->reply->id,
                            'content' => $review->reply->content,
                        ] : null,
                    ];
                });
            return response()->json(['data' => $reviews]);
        } catch (\Exception $e) {
            Log::error('Review fetch failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function adminShow($id)
    {
        try {
            $review = Review::with(['media', 'reply', 'product'])
                ->withCount('likes') // 👈 Thêm dòng này để lấy likes_count
                ->findOrFail($id);

            return response()->json([
                'id' => $review->id,
                'product_id' => $review->product_id,
                'product_name' => optional($review->product)->name ?? 'Không rõ',
                'user_id' => $review->user_id,
                'content' => $review->content,
                'rating' => $review->rating,
                'status' => $review->status,
                'likes_count' => $review->likes_count, // 👈 Dùng đúng trường `likes_count`
                'reply' => $review->reply ? [
                    'id' => $review->reply->id,
                    'content' => $review->reply->content,
                    'created_at' => optional($review->reply->created_at)->toDateTimeString(),
                ] : null,
                'images' => $review->media
                    ->where('media_type', 'image')
                    ->map(fn($m) => [
                        'id' => $m->id,
                        'url' => Storage::disk('r2')->url($m->media_url),
                    ])
                    ->values(),
                'videos' => $review->media
                    ->where('media_type', 'video')
                    ->map(fn($m) => Storage::disk('r2')->url($m->media_url))
                    ->values(),
                'created_at' => $review->created_at->toDateTimeString(),
            ]);
        } catch (\Exception $e) {
            Log::error('adminShow error: ' . $e->getMessage());
            return response()->json(['error' => 'Không tìm thấy đánh giá'], 404);
        }
    }




    public function adminUpdate(Request $request, $id)
    {
        $review = Review::with('media', 'reply')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'content' => 'required|string|min:10|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'status' => 'required|in:approved,pending,rejected',
            'reply' => 'nullable|string|max:1000',
            'kept_images' => 'nullable|array',
            'kept_images.*' => 'integer',
            'images.*' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Cập nhật fields
        $review->update([
            'content' => $request->content,
            'rating' => $request->rating,
            'status' => $request->status,
        ]);

        // Cập nhật hoặc tạo mới phản hồi
        // Trong phần cập nhật hoặc tạo mới phản hồi
        if ($request->filled('reply')) {
            if ($review->reply) {
                $review->reply->update([
                    'content' => $request->input('reply'),
                ]);
            } else {
                $review->reply()->create([
                    'content' => $request->input('reply'),
                    'user_id' => auth()->id(), // hoặc ID admin cố định
                    'status' => 'approved',
                ]);
            }
        }

        // Xóa ảnh không giữ
        $kept = $request->input('kept_images', []);
        $review->media()->where('media_type', 'image')
            ->whereNotIn('id', $kept)
            ->each(function ($m) {
                Storage::disk('r2')->delete($m->media_url);
                $m->delete();
            });

        // Thêm ảnh mới
        if ($files = $request->file('images')) {
            foreach ($files as $file) {
                $path = 'reviews/' . uniqid() . '.' . $file->extension();
                Storage::disk('r2')->put($path, file_get_contents($file));
                $review->media()->create([
                    'media_url' => $path,
                    'media_type' => 'image',
                ]);
            }
        }

        return response()->json(['message' => 'Cập nhật đánh giá thành công']);
    }



    public function adminDestroy($id)
{
    try {
        $review = Review::with('media', 'reply')->findOrFail($id);

        // Xóa media
        foreach ($review->media as $media) {
            Storage::disk('r2')->delete($media->media_url);
            $media->delete();
        }

        // Xóa reply nếu có
        if ($review->reply) {
            $review->reply->delete();
        }

        $review->delete();

        return response()->json(['message' => 'Xóa đánh giá thành công']);
    } catch (\Exception $e) {
        Log::error('Lỗi khi xóa đánh giá: ' . $e->getMessage());
        return response()->json(['error' => 'Không thể xóa đánh giá'], 500);
    }
}

}
