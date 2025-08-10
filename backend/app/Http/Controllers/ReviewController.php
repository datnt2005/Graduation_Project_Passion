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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;




class ReviewController extends Controller
{
    public function index(Request $request)
    {
        try {
            $productId = $request->query('product_id');
            if (!$productId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Thiếu product_id'
                ], 400);
            }

            $user = Auth::user();
            $userId = $user ? $user->id : null;

            // Xóa cache cho sản phẩm cụ thể
            Cache::forget('reviews_product_' . $productId);

            $query = Review::with([
                'user',
                'reply',
                'media',
                'orderItem.productVariant.product.seller',
                'orderItem.productVariant.attributeValues.attribute',
                'likes'
            ])
                ->select('reviews.*') // Rõ ràng chọn tất cả cột từ bảng reviews
                ->where('product_id', $productId)
                ->whereNull('parent_id')
                ->where(function ($q) use ($userId) {
                    $q->where('status', 'approved')
                        ->orWhere(function ($sub) use ($userId) {
                            if ($userId) {
                                $sub->where('user_id', $userId)->where('status', 'pending');
                            }
                        });
                })
                ->orderByDesc('created_at');

            // Filter by rating if provided
            if ($request->has('rating')) {
                $query->where('rating', $request->rating);
            }

            // Filter by has_images or has_videos
            if ($request->has('has_images') && $request->has_images) {
                $query->whereHas('media', function ($q) {
                    $q->where('media_type', 'image');
                });
            }
            if ($request->has('has_videos') && $request->has_videos) {
                $query->whereHas('media', function ($q) {
                    $q->where('media_type', 'video');
                });
            }

            // Paginate
            $perPage = $request->input('per_page', 3);
            $reviews = $query->paginate($perPage);

            // Debug: Log dữ liệu reviews
            Log::info('Reviews fetched:', ['reviews' => $reviews->toArray()]);

            // Calculate summary (only for approved reviews)
            $approvedReviews = $reviews->filter(fn($r) => $r->status === 'approved');
            $totalLikes = $approvedReviews->sum(function ($review) {
                return $review->likesCount(); // Sử dụng likesCount() thay vì likes
            });
            $summary = [
                'rating' => round($approvedReviews->avg('rating') ?: 0, 1),
                'count' => $approvedReviews->count(),
                'likes_count' => $totalLikes,
                'ratings' => [
                    5 => $approvedReviews->where('rating', 5)->count(),
                    4 => $approvedReviews->where('rating', 4)->count(),
                    3 => $approvedReviews->where('rating', 3)->count(),
                    2 => $approvedReviews->where('rating', 2)->count(),
                    1 => $approvedReviews->where('rating', 1)->count(),
                ]
            ];

            // Format reviews
            $formattedReviews = $reviews->through(function ($review) {
                $images = $review->media
                    ->where('media_type', 'image')
                    ->map(function ($m) {
                        return [
                            'id' => $m->id,
                            'url' => Storage::disk('r2')->url($m->media_url),
                        ];
                    })
                    ->values();

                $videos = $review->media
                    ->where('media_type', 'video')
                    ->map(function ($m) {
                        return [
                            'id' => $m->id,
                            'url' => Storage::disk('r2')->url($m->media_url),
                        ];
                    })
                    ->values();

                // Format variant
                $variant = $review->orderItem && $review->orderItem->productVariant
                    ? [
                        'id' => $review->orderItem->productVariant->id,
                        'name' => $review->orderItem->productVariant->name ?? 'Không xác định',
                        'color' => $review->orderItem->productVariant->color ?? null,
                        'attributes' => $review->orderItem->productVariant->attributeValues->map(function ($attributeValue) {
                            return [
                                'name' => $attributeValue->attribute->name ?? 'Không xác định',
                                'value' => $attributeValue->value ?? 'Không xác định',
                            ];
                        })->values()->toArray(),
                    ]
                    : [
                        'id' => null,
                        'name' => 'Không xác định',
                        'color' => null,
                        'attributes' => [],
                    ];

                // Debug: Log giá trị likes và likesCount
                Log::info('Review ID: ' . $review->id, [
                    'likes' => $review->likes,
                    'likesCount' => $review->likesCount()
                ]);

                return [
                    'id' => $review->id,
                    'user_id' => $review->user_id,
                    'user' => [
                        'name' => $review->user->name ?? 'Ẩn danh',
                        'avatar' => $review->user->avatar ?? 'avatars/default.jpg',
                    ],
                    'joined' => $review->user->created_at ? $review->user->created_at->format('F, Y') : 'Tháng 1, 2024',
                    'totalReviews' => Review::where('user_id', $review->user_id)->count(),
                    'purchased' => !is_null($review->orderItem),
                    'rating' => $review->rating,
                    'content' => $review->content,
                    'status' => $review->status,
                    'reply' => $review->reply ? [
                        'id' => $review->reply->id,
                        'content' => $review->reply->content,
                        'created_at' => $review->reply->created_at->toISOString(),
                    ] : null,
                    'images' => $images,
                    'videos' => $videos,
                    'color' => $variant['color'],
                    'variant' => $variant,
                    'created_at' => $review->created_at->toISOString(),
                    'usageTime' => $review->created_at->diffForHumans(),
                    'likes_count' => $review->likesCount(), // Sử dụng likesCount() thay vì likes
                ];
            });

            // Debug: Log formatted reviews
            Log::info('Formatted reviews:', ['formatted' => $formattedReviews->toArray()]);

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách đánh giá thành công',
                'data' => [
                    'summary' => $summary,
                    'list' => $formattedReviews->items(),
                ],
                'meta' => [
                    'current_page' => $reviews->currentPage(),
                    'from' => $reviews->firstItem(),
                    'last_page' => $reviews->lastPage(),
                    'path' => $reviews->path(),
                    'per_page' => $reviews->perPage(),
                    'to' => $reviews->lastItem(),
                    'total' => $reviews->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error in ReviewController@index: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách đánh giá: ' . $e->getMessage()
            ], 500);
        }
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'content' => 'required|string|min:10|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'videos.*' => 'nullable|mimes:mp4,mkv,avi|max:10240',
        ], [
            'product_id.required' => 'Mã sản phẩm là bắt buộc.',
            'product_id.exists' => 'Sản phẩm không tồn tại.',
            'content.required' => 'Nội dung đánh giá là bắt buộc.',
            'content.string' => 'Nội dung đánh giá không hợp lệ.',
            'content.min' => 'Nội dung đánh giá phải có ít nhất :min ký tự.',
            'content.max' => 'Nội dung đánh giá không được vượt quá :max ký tự.',
            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
            'rating.integer' => 'Giá trị đánh giá phải là số nguyên.',
            'rating.min' => 'Đánh giá tối thiểu là :min sao.',
            'rating.max' => 'Đánh giá tối đa là :max sao.',
            'images.*.image' => 'Tệp tải lên phải là hình ảnh.',
            'images.*.mimes' => 'Hình ảnh chỉ được chấp nhận định dạng: jpeg, png, jpg, gif, svg, webp.',
            'images.*.max' => 'Dung lượng hình ảnh không được vượt quá 2MB.',
            'videos.*.mimes' => 'Video chỉ được chấp nhận định dạng: mp4, mkv, avi.',
            'videos.*.max' => 'Dung lượng video không được vượt quá 10MB.',
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

        // Tìm order_item_id hợp lệ đầu tiên
        $orderItem = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.user_id', $user->id)
            ->where('orders.status', 'delivered')
            ->where('order_items.product_id', $productId)
            ->select('order_items.id')
            ->first();

        if (!$orderItem) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần mua sản phẩm để đánh giá.'
            ], 422);
        }

        // Kiểm tra trùng đánh giá dựa trên order_item_id
        $existingReview = Review::where('user_id', $user->id)
            ->where('order_item_id', $orderItem->id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã đánh giá sản phẩm này trong đơn hàng này rồi.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $review = Review::create([
                'user_id' => $user->id,
                'product_id' => $productId,
                'order_item_id' => $orderItem->id,
                'rating' => $request->input('rating'),
                'content' => $request->input('content'),
                'status' => 'approved',
            ]);

            if ($request->hasFile('images') || $request->hasFile('videos')) {
                $mediaUrls = [];

                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $file) {
                        $filename = 'reviews/' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        Storage::disk('r2')->put($filename, file_get_contents($file));
                        $mediaUrls[] = ['media_url' => $filename, 'media_type' => 'image'];
                    }
                }

                if ($request->hasFile('videos')) {
                    foreach ($request->file('videos') as $file) {
                        $filename = 'reviews/' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        Storage::disk('r2')->put($filename, file_get_contents($file));
                        $mediaUrls[] = ['media_url' => $filename, 'media_type' => 'video'];
                    }
                }

                foreach ($mediaUrls as $media) {
                    $review->media()->create($media);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đánh giá đã được gửi thành công.',
                'data' => $review
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi lưu đánh giá: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

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
        $user = Auth::user();

        if (!$user || $review->user_id !== $user->id) {
            return response()->json(['message' => 'Bạn không có quyền sửa đánh giá này.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string|min:10|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'videos.*' => 'nullable|mimes:mp4,mov,avi,wmv|max:10240',
            'kept_images' => 'nullable|array',
            'kept_images.*' => 'integer',
            'kept_videos' => 'nullable|array',
            'kept_videos.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $validator->errors()
            ], 422);
        }

        $hasPurchased = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.user_id', $user->id)
            ->where('orders.status', 'delivered')
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

            // Xoá hình ảnh không giữ lại
            $keptImageIds = $request->input('kept_images', []);
            $review->media()
                ->where('media_type', 'image')
                ->whereNotIn('id', $keptImageIds)
                ->delete();

            // Xoá video không giữ lại
            $keptVideoIds = $request->input('kept_videos', []);
            $review->media()
                ->where('media_type', 'video')
                ->whereNotIn('id', $keptVideoIds)
                ->delete();

            // Upload ảnh mới
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $filename = 'reviews/' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    Storage::disk('r2')->put($filename, file_get_contents($file));
                    $mediaUrls[] = ['media_url' => $filename, 'media_type' => 'image'];
                }
            }

            // Upload video mới
            if ($request->hasFile('videos')) {
                foreach ($request->file('videos') as $video) {
                    $filename = 'reviews/videos/' . time() . '_' . uniqid() . '.' . $video->getClientOriginalExtension();
                    Storage::disk('r2')->put($filename, file_get_contents($video));
                    $mediaUrls[] = ['media_url' => $filename, 'media_type' => 'video'];
                }
            }

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

        // Cập nhật cột likes trong bảng reviews
        $review->likes = $review->likes()->count();
        $review->save();

        return response()->json([
            'message' => 'Đã thích đánh giá.',
            'likes' => $review->likes
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

        // Kiểm tra đã like chưa
        $liked = ReviewLike::where('user_id', $user->id)->where('review_id', $reviewId)->first();

        if (!$liked) {
            return response()->json(['message' => 'Bạn chưa thích đánh giá này.'], 400);
        }

        // Xóa like
        $liked->delete();

        // Cập nhật cột likes trong bảng reviews
        $review->likes = $review->likes()->count();
        $review->save();

        return response()->json([
            'message' => 'Đã hủy thích đánh giá.',
            'likes' => $review->likes
        ]);
    }


    public function sellerIndex(Request $request)
    {
        try {
            $userId = auth()->id();
            $perPage = $request->query('per_page', 10);
            $page = $request->query('page', 1);
            $rating = $request->query('rating');
            $status = $request->query('status');
            $hasMedia = filter_var($request->query('has_media', false), FILTER_VALIDATE_BOOLEAN);
            $sortOrder = $request->query('sort_order', 'desc');

            // Query chính
            $query = Review::with([
                'product:id,name,seller_id',
                'product.pics:id,product_id,imagePath',
                'media',
                'reply'
            ])
                ->whereHas('product', function ($q) use ($userId) {
                    $q->whereHas('seller', function ($sub) use ($userId) {
                        $sub->where('user_id', $userId);
                    });
                })
                ->whereNull('parent_id')
                ->withCount('likes');

            // Lọc theo rating
            if ($rating !== null && is_numeric($rating)) {
                $query->where('rating', $rating);
            }

            // Lọc theo status
            if ($status && in_array($status, ['approved', 'pending', 'rejected'])) {
                $query->where('status', $status);
            }

            // Lọc theo has_media
            if ($hasMedia) {
                $query->whereHas('media', function ($q) {
                    $q->where('media_type', 'image');
                });
            }

            // Sắp xếp
            $query->orderBy('created_at', $sortOrder);

            // Phân trang
            $reviews = $query->paginate($perPage, ['*'], 'page', $page);

            // Tính số lượng cho bộ lọc
            $baseQuery = Review::whereHas('product', function ($q) use ($userId) {
                $q->whereHas('seller', function ($sub) use ($userId) {
                    $sub->where('user_id', $userId);
                });
            })->whereNull('parent_id');

            $countByRating = ['all' => $baseQuery->count()];
            $countByStatus = ['all' => $countByRating['all']];
            $countWithMedia = $baseQuery->whereHas('media', function ($q) {
                $q->where('media_type', 'image');
            })->count();

            // Đếm theo rating
            foreach ([1, 2, 3, 4, 5] as $r) {
                $countByRating[$r] = $baseQuery->where('rating', $r)->count();
            }

            // Đếm theo status
            foreach (['approved', 'pending', 'rejected'] as $s) {
                $countByStatus[$s] = $baseQuery->where('status', $s)->count();
            }

            // Định dạng dữ liệu
            $reviews->getCollection()->transform(function ($review) {
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

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách đánh giá thành công.',
                'data' => $reviews->items(),
                'last_page' => $reviews->lastPage(),
                'total' => $reviews->total(),
                'current_page' => $reviews->currentPage(),
                'count_by_rating' => $countByRating,
                'count_by_status' => $countByStatus,
                'count_with_media' => $countWithMedia,
            ]);
        } catch (\Exception $e) {
            Log::error('Review fetch failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách đánh giá.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }


    public function sellerShow($id)
    {
        try {
            $userId = auth()->id();

            $review = Review::with(['media', 'reply', 'product', 'user'])
                ->withCount('likes')
                ->whereHas('product', function ($q) use ($userId) {
                    $q->whereHas('seller', function ($s) use ($userId) {
                        $s->where('user_id', $userId);
                    });
                })
                ->findOrFail($id);

            return response()->json([
                'id' => $review->id,
                'product_id' => $review->product_id,
                'product_name' => optional($review->product)->name ?? 'Không rõ',
                'user_id' => $review->user_id,
                'user_name' => optional($review->user)->name ?? 'Không rõ',
                'content' => $review->content,
                'rating' => $review->rating,
                'status' => $review->status,
                'likes_count' => $review->likes_count,
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
            Log::error('sellerShow error: ' . $e->getMessage());
            return response()->json(['error' => 'Không tìm thấy đánh giá'], 404);
        }
    }


    public function sellerUpdate(Request $request, $id)
    {
        try {
            $userId = auth()->id();

            $review = Review::with('reply')->whereHas('product', function ($q) use ($userId) {
                $q->whereHas('seller', function ($s) use ($userId) {
                    $s->where('user_id', $userId);
                });
            })->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'reply' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            if ($request->filled('reply')) {
                if ($review->reply) {
                    $review->reply->update([
                        'content' => $request->input('reply'),
                        'updated_at' => now(),
                    ]);
                } else {
                    $review->reply()->create([
                        'content' => $request->input('reply'),
                        'user_id' => $userId,
                        'status' => 'approved',
                        'product_id' => $review->product_id,
                        'parent_id' => $review->id,
                        'rating' => 0,
                    ]);
                }
            } else {
                if ($review->reply) {
                    $review->reply->delete();
                }
            }

            return response()->json(['message' => 'Phản hồi đánh giá đã được cập nhật.']);
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật phản hồi đánh giá: ' . $e->getMessage());
            return response()->json([
                'message' => 'Có lỗi khi cập nhật phản hồi.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function sellerDestroy($id)
    {
        try {
            $userId = auth()->id();

            $review = Review::with(['media', 'reply'])
                ->whereHas('product', function ($q) use ($userId) {
                    $q->whereHas('seller', function ($s) use ($userId) {
                        $s->where('user_id', $userId);
                    });
                })
                ->findOrFail($id);

            foreach ($review->media as $media) {
                Storage::disk('r2')->delete($media->media_url);
                $media->delete();
            }

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

public function sellerReviewCounts(Request $request)
{
    try {
        $sellerId = auth('sanctum')->user()->id;

        // Query cơ bản để lấy đánh giá của sản phẩm thuộc về người bán
        $baseQuery = Review::whereHas('product', function ($q) use ($sellerId) {
            $q->whereHas('seller', function ($sub) use ($sellerId) {
                $sub->where('user_id', $sellerId);
            });
        })->whereNull('parent_id');

        // Clone query để tránh thay đổi trạng thái
        $allReviews = $baseQuery->get();

        // Đếm số lượng đánh giá theo số sao
        $countByRating = [
            'all' => $allReviews->count(),
            1 => $allReviews->where('rating', 1)->count(),
            2 => $allReviews->where('rating', 2)->count(),
            3 => $allReviews->where('rating', 3)->count(),
            4 => $allReviews->where('rating', 4)->count(),
            5 => $allReviews->where('rating', 5)->count(),
        ];

        // Đếm số lượng đánh giá theo trạng thái
        $countByStatus = [
            'all' => $allReviews->count(),
            'approved' => $allReviews->where('status', 'approved')->count(),
            'pending' => $allReviews->where('status', 'pending')->count(),
            'rejected' => $allReviews->where('status', 'rejected')->count(),
        ];

        // Đếm số lượng đánh giá có ảnh/video
        $countWithMedia = $baseQuery->whereHas('media', function ($query) {
            $query->whereIn('media_type', ['image', 'video'])->whereNotNull('media_url');
        })->count();

        // Debug log
        \Log::info('sellerReviewCounts data', [
            'seller_id' => $sellerId,
            'reviews' => $allReviews->toArray(),
            'count_by_rating' => $countByRating,
            'count_by_status' => $countByStatus,
            'count_with_media' => $countWithMedia,
        ]);

        return response()->json([
            'success' => true,
            'count_by_rating' => $countByRating,
            'count_by_status' => $countByStatus,
            'count_with_media' => $countWithMedia,
        ], 200);
    } catch (\Exception $e) {
        \Log::error('Lỗi khi lấy số lượng đánh giá: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi lấy số lượng đánh giá: ' . $e->getMessage(),
        ], 500);
    }
}
}
