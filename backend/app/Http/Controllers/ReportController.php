<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\PostComment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type');

        $query = Report::query()->with(['reporter']);

        if ($type === 'review') {
            $query->with(['review.product', 'review.user']);
        } elseif ($type === 'post_comment') {
            $query->with(['postComment.user', 'postComment.media']);
        } elseif ($type === 'product') {
            $query->with(['product']);
        }
        $reports = $query->when($type, fn($q) => $q->where('type', $type))
            ->latest()
            ->get()
            ->map(function ($report) {
                $data = [
                    'id' => $report->id,
                    'type' => $report->type,
                    'reason' => $report->reason,
                    'status' => $report->status,
                    'reporter' => $report->reporter->name ?? 'Ẩn danh',
                    'created_at' => $report->created_at,
                ];

                if ($report->type === 'review' && $report->review) {
                    $data['content'] = $report->review->content;
                    $data['user_name'] = $report->review->user->name ?? null;
                    $data['product'] = $report->review->product->name ?? null;
                }

                if ($report->type === 'post_comment' && $report->postComment) {
                    $data['content'] = $report->postComment->content;
                    $data['user_name'] = $report->postComment->user->name ?? null;
                    $data['media'] = $report->postComment->media->map(function ($m) {
                        return [
                            'url' => Storage::disk('r2')->url($m->media_url),
                            'type' => $m->media_type,
                        ];
                    });
                }

                if ($report->type === 'product' && $report->product) {
                    $data['product'] = $report->product->name ?? null;
                }

                return $data;
            });

        return response()->json(['success' => true, 'data' => $reports]);
    }

    public function show($id)
    {
        $report = Report::with([
            'reporter',
            'review.product',
            'review.user',
            'review.reply',
            'review.media',
            'postComment.user',
            'postComment.media'
        ])->find($id);

        if (!$report) {
            return response()->json(['message' => 'Không tìm thấy'], 404);
        }

        $data = [
            'report_id' => $report->id,
            'reason' => $report->reason,
            'status' => $report->status,
            'reported_at' => $report->created_at,
            'reporter' => $report->reporter->name ?? 'Ẩn danh',
        ];

        if ($report->type === 'review' && $report->review) {
            $report->review->loadCount('likes');
            $data['review'] = [
                'id' => $report->review->id,
                'content' => $report->review->content,
                'rating' => $report->review->rating,
                'likes_count' => $report->review->likes_count ?? 0,
                'status' => $report->review->status,
                'created_at' => $report->review->created_at,
                'product_name' => optional($report->review->product)->name,
                'product_image' => optional($report->review->product)->image,
                'user_name' => optional($report->review->user)->name,
                'reply' => $report->review->reply,
                'media' => $report->review->media->map(function ($m) {
                    return [
                        'id' => $m->id,
                        'url' => Storage::disk('r2')->url($m->media_url),
                        'type' => $m->media_type,
                    ];
                })->values(),
            ];
        }

        if ($report->type === 'post_comment' && $report->postComment) {
            $data['post_comment'] = [
                'content' => $report->postComment->content,
                'user_name' => $report->postComment->user->name ?? null,
                'media' => $report->postComment->media->map(function ($m) {
                    return [
                        'url' => Storage::disk('r2')->url($m->media_url),
                        'type' => $m->media_type,
                    ];
                }),
            ];
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'target_id' => 'required|integer',
            'type' => 'required|in:product,user,review,post,post_comment',
            'reason' => 'required|string|max:1000',
        ]);

        $exists = Report::where([
            'reporter_id' => Auth::id(),
            'target_id' => $data['target_id'],
            'type' => $data['type'],
        ])->exists();

        if ($exists) {
            return response()->json(['message' => 'Bạn đã báo cáo mục này trước đó.'], 409);
        }

        Report::create([
            'reporter_id' => Auth::id(),
            'target_id' => $data['target_id'],
            'type' => $data['type'],
            'reason' => $data['reason'],
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Báo cáo đã được gửi'], 201);
    }

    public function updateStatus(Request $request, $id)
    {
        $report = Report::with(['review', 'postComment'])->findOrFail($id);

        $data = $request->validate([
            'status' => 'required|in:resolved,dismissed',
        ]);

        $report->status = $data['status'];
        $report->save();

        if ($data['status'] === 'resolved') {
            if ($report->type === 'review' && $report->review) {
                $report->review->update(['status' => 'hidden']);
            }
            if ($report->type === 'post_comment' && $report->postComment) {
                $report->postComment->update(['status' => 'hidden']);
            }
        } elseif ($data['status'] === 'dismissed') {
            if ($report->type === 'review' && $report->review) {
                $report->review->update(['status' => 'approved']);
            }
            if ($report->type === 'post_comment' && $report->postComment) {
                $report->postComment->update(['status' => 'approved']);
            }
        }

        return response()->json(['message' => 'Cập nhật trạng thái thành công.']);
    }

    public function destroy($id)
    {
        $report = Report::findOrFail($id);
        $report->delete();

        return response()->json([
            'message' => 'Xóa báo cáo thành công.',
            'success' => true
        ]);
    }

     public function adminIndex(Request $request)
    {
        $reports = Report::with([
            'review.product.seller',
            'review.user',
            'reporter'
        ])
            ->where('type', 'review')
            ->latest()
            ->get()
            ->filter(fn($report) => $report->review && $report->review->product && $report->review->user)
            ->map(function ($report) {
                return [
                    'report_id' => $report->id,
                    'reason' => $report->reason,
                    'status' => $report->status,
                    'reported_at' => $report->created_at,
                    'review' => [
                        'id' => $report->review->id,
                        'content' => $report->review->content,
                        'product_name' => $report->review->product->name,
                        'user_name' => $report->review->user->name,
                    ],
                    'reporter' => $report->reporter->name ?? 'Ẩn danh',
                ];
            })
            ->values();

        return response()->json(['success' => true, 'data' => $reports]);
    }

    // Phương thức cho admin: Xem chi tiết báo cáo
    public function adminShow($id)
    {
        $report = Report::with([
            'reporter',
            'review.product.seller',
            'review.user',
            'review.reply',
            'review.media',
        ])
            ->where('type', 'review')
            ->find($id);

        if (!$report || !$report->review || !$report->review->product) {
            return response()->json(['message' => 'Không tìm thấy báo cáo'], 404);
        }

        $report->review->loadCount('likes');

        return response()->json([
            'data' => [
                'report_id' => $report->id,
                'reason' => $report->reason,
                'status' => $report->status,
                'reported_at' => $report->created_at,
                'review' => [
                    'id' => $report->review->id,
                    'content' => $report->review->content,
                    'rating' => $report->review->rating,
                    'likes_count' => $report->review->likes_count ?? 0,
                    'status' => $report->review->status,
                    'created_at' => $report->review->created_at,
                    'product_name' => optional($report->review->product)->name,
                    'product_image' => optional($report->review->product)->image,
                    'user_name' => optional($report->review->user)->name,
                    'reply' => $report->review->reply,
                    'media' => $report->review->media->map(fn($m) => [
                        'id' => $m->id,
                        'url' => Storage::disk('r2')->url($m->media_url),
                        'type' => $m->media_type,
                    ])->values(),
                ],
                'reporter' => $report->reporter->name ?? 'Ẩn danh',
            ]
        ]);
    }

    // Phương thức cho admin: Cập nhật trạng thái báo cáo
    public function adminUpdateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:resolved,dismissed',
        ]);

        $report = Report::with('review.product.seller')->findOrFail($id);

        if (!$report->review || !$report->review->product) {
            return response()->json(['message' => 'Không tìm thấy báo cáo'], 404);
        }

        $newStatus = $request->input('status');

        $report->status = $newStatus;
        $report->save();

        if ($report->review) {
            if ($newStatus === 'resolved') {
                $report->review->status = 'rejected';
            } elseif ($newStatus === 'dismissed') {
                $report->review->status = 'approved';
            }
            $report->review->save();
        }

        return response()->json(['message' => 'Cập nhật trạng thái thành công']);
    }

    
    public function getReportProduct(Request $request)
    {
        try {
            // Validate query parameters
            $perPage = $request->query('per_page', 10); // Default to 10 items per page
            $page = $request->query('page', 1); // Default to page 1
            $search = $request->query('search'); // Search query for product name
            $sellerName = $request->query('seller_name'); // Filter by seller name

            // Build query
            $query = Report::with(['product.seller', 'reporter'])
                ->where('type', 'product')
                ->whereHas('product'); // Ensure product exists

            // Apply search filter
            if ($search) {
                $query->whereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            }

            // Apply seller name filter
            if ($sellerName) {
                $query->whereHas('product.seller', function ($q) use ($sellerName) {
                    $q->where('store_name', $sellerName);
                });
            }

            // Apply sorting (latest by default)
            $query->latest('created_at');

            // Paginate results
            $reports = $query->paginate($perPage, ['*'], 'page', $page);

            // Transform data
            $formattedReports = $reports->getCollection()->map(function ($report) {
                return [
                    'report_id' => $report->id,
                    'reason' => $report->reason,
                    'status' => $report->status,
                    'reported_at' => $report->created_at,
                    'product' => [
                        'id' => $report->product->id,
                        'name' => $report->product->name,
                        'image' => $report->product->image
                            ? Storage::disk('r2')->url($report->product->image)
                            : null,
                        'description' => $report->product->description ?? '',
                        'seller_name' => $report->product->seller->store_name ?? 'Không xác định',
                    ],
                    'reporter' => $report->reporter->name ?? 'Ẩn danh',
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedReports->values(),
                'last_page' => $reports->lastPage(),
                'current_page' => $reports->currentPage(),
                'total' => $reports->total(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải báo cáo sản phẩm',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getReportProductById($id)
    {
        try {
            $report = Report::with(['product.seller', 'reporter'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $report,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải báo cáo sản phẩm',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function sellerIndex(Request $request)
    {
        try {
            $userId = auth()->id();
            $perPage = $request->query('per_page', 10);
            $page = $request->query('page', 1);
            $status = $request->query('status');
            $sortOrder = $request->query('sort_order', 'desc');

            // Query chính
            $query = Report::with([
                'review' => function ($q) {
                    $q->select('id', 'product_id', 'content', 'user_id')
                        ->with(['user:id,name', 'product:id,name,seller_id']);
                }
            ])
                ->where('type', 'review')
                ->whereHas('review.product.seller', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                });

            // Áp dụng bộ lọc
            if ($status && in_array($status, ['pending', 'resolved', 'dismissed'])) {
                $query->where('status', $status);
            }

            // Sắp xếp
            $query->orderBy('reported_at', $sortOrder);

            // Phân trang
            $reports = $query->paginate($perPage, ['*'], 'page', $page);

            // Định dạng dữ liệu
            $reports->getCollection()->transform(function ($report) {
                if (!$report->review || !$report->review->product || !$report->review->user) {
                    return null;
                }
                return [
                    'report_id' => $report->id,
                    'reason' => $report->reason,
                    'status' => $report->status,
                    'reported_at' => $report->created_at,
                    'review' => [
                        'id' => $report->review->id,
                        'content' => $report->review->content,
                        'product_name' => $report->review->product->name,
                        'user_name' => $report->review->user->name,
                    ],
                    'reporter' => $report->reporter ? $report->reporter->name : 'Ẩn danh',
                ];
            })->filter();

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách báo cáo thành công.',
                'data' => $reports->items(),
                'last_page' => $reports->lastPage(),
                'total' => $reports->total(),
                'current_page' => $reports->currentPage(),
            ]);
        } catch (\Exception $e) {
            Log::error('Lấy danh sách báo cáo thất bại: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách báo cáo.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function sellerShow($id)
    {
        $userId = auth()->id();

        $report = Report::with([
                'reporter',
                'review.product.seller',
                'review.user',
                'review.reply',
                'review.media',
            ])
            ->where('type', 'review')
            ->find($id);

        if (
            !$report || !$report->review || !$report->review->product ||
            !$report->review->product->seller || $report->review->product->seller->user_id !== $userId
        ) {
            return response()->json(['message' => 'Không tìm thấy hoặc không có quyền truy cập'], 403);
        }

        $report->review->loadCount('likes');

        return response()->json([
            'data' => [
                'report_id' => $report->id,
                'reason' => $report->reason,
                'status' => $report->status,
                'reported_at' => $report->created_at,
                'review' => [
                    'id' => $report->review->id,
                    'content' => $report->review->content,
                    'rating' => $report->review->rating,
                    'likes_count' => $report->review->likes_count ?? 0,
                    'status' => $report->review->status,
                    'created_at' => $report->review->created_at,
                    'product_name' => optional($report->review->product)->name,
                    'product_image' => optional($report->review->product)->image,
                    'user_name' => optional($report->review->user)->name,
                    'reply' => $report->review->reply,
                    'media' => $report->review->media->map(fn($m) => [
                        'id' => $m->id,
                        'url' => Storage::disk('r2')->url($m->media_url),
                        'type' => $m->media_type,
                    ])->values(),
                ],
                'reporter' => $report->reporter->name ?? 'Ẩn danh',
            ]
        ]);
    }

}