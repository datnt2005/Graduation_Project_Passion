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
    try {
        $perPage = $request->query('per_page', 10); // Mặc định 10 bản ghi mỗi trang
        $page = $request->query('page', 1); // Mặc định trang 1
        $sortOrder = $request->query('sort_order', 'desc'); // Mặc định sắp xếp mới nhất
        $status = $request->query('status'); // Lọc theo trạng thái
        $search = $request->query('search'); // Tìm kiếm theo nội dung, sản phẩm, hoặc người báo cáo

        // Xây dựng query
        $query = Report::with([
            'review.product.seller',
            'review.user',
            'reporter'
        ])
            ->where('type', 'review')
            ->whereHas('review', function ($q) {
                $q->whereHas('product')->whereHas('user');
            });

        // Lọc theo trạng thái
        if ($status) {
            $query->where('status', $status);
        }

        // Lọc theo tìm kiếm
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('review', function ($sub) use ($search) {
                    $sub->where('content', 'like', '%' . $search . '%')
                        ->orWhereHas('product', function ($p) use ($search) {
                            $p->where('name', 'like', '%' . $search . '%');
                        });
                })->orWhereHas('reporter', function ($r) use ($search) {
                    $r->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        // Sắp xếp
        $query->orderBy('created_at', $sortOrder);

        // Phân trang
        $reports = $query->paginate($perPage, ['*'], 'page', $page);

        // Chuyển đổi dữ liệu
        $formattedReports = $reports->getCollection()->map(function ($report) {
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
        });

        // Cập nhật collection trong kết quả phân trang
        $reports->setCollection($formattedReports);

        return response()->json([
            'success' => true,
            'data' => $reports->items(),
            'current_page' => $reports->currentPage(),
            'last_page' => $reports->lastPage(),
            'total' => $reports->total(),
            'per_page' => $reports->perPage(),
        ], 200);
    } catch (\Exception $e) {
        \Log::error('Lỗi khi tải báo cáo admin: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi tải báo cáo: ' . $e->getMessage(),
        ], 500);
    }
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

    public function adminReportCounts(Request $request)
{
    try {
        $baseQuery = Report::where('type', 'review');

        $allReports = $baseQuery->get();
        $countByStatus = [
            'all' => $allReports->count(),
            'pending' => $allReports->where('status', 'pending')->count(),
            'resolved' => $allReports->where('status', 'resolved')->count(),
            'dismissed' => $allReports->where('status', 'dismissed')->count(),
        ];

        Log::info('adminReportCounts data', [
            'reports' => $allReports->toArray(),
            'count_by_status' => $countByStatus,
        ]);

        return response()->json([
            'success' => true,
            'count_by_status' => $countByStatus,
        ], 200);
    } catch (\Exception $e) {
        Log::error('Lỗi khi lấy số lượng Durand báo cáo admin: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi lấy số lượng báo cáo: ' . $e->getMessage(),
        ], 500);
    }
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
    $userId = auth()->id();

    // Lấy các tham số từ request
    $perPage = $request->input('per_page', 10); // Mặc định 10 bản ghi mỗi trang
    $sortOrder = $request->input('sort_order', 'desc'); // Mặc định sắp xếp mới nhất
    $status = $request->input('status'); // Lọc theo trạng thái (nếu có)

    // Xây dựng query
    $query = Report::with([
            'review.product.seller',
            'review.user',
            'reporter'
        ])
        ->where('type', 'review')
        ->whereHas('review.product.seller', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });

    // Lọc theo trạng thái nếu có
    if ($status) {
        $query->where('status', $status);
    }

    // Sắp xếp theo created_at
    $query->orderBy('created_at', $sortOrder);

    // Phân trang
    $reports = $query->paginate($perPage);

    // Chuyển đổi dữ liệu
    $formattedReports = $reports->getCollection()->map(function ($report) {
        return [
            'report_id' => $report->id,
            'reason' => $report->reason,
            'status' => $report->status,
            'reported_at' => $report->created_at,
            'review' => [
                'id' => optional($report->review)->id,
                'content' => optional($report->review)->content,
                'product_name' => optional($report->review->product)->name,
                'user_name' => optional($report->review->user)->name,
            ],
            'reporter' => optional($report->reporter)->name ?? 'Ẩn danh',
        ];
    })->filter(fn($report) => $report['review']['id']); // Lọc các báo cáo có review hợp lệ

    // Cập nhật collection trong kết quả phân trang
    $reports->setCollection($formattedReports);

    // Trả về dữ liệu phân trang
    return response()->json([
        'success' => true,
        'data' => $reports->items(),
        'current_page' => $reports->currentPage(),
        'last_page' => $reports->lastPage(),
        'total' => $reports->total(),
        'per_page' => $reports->perPage(),
    ]);
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

    public function sellerReportCounts(Request $request)
{
    try {
        $userId = auth('sanctum')->user()->id;
        $baseQuery = Report::where('type', 'review')
            ->whereHas('review', function ($q) use ($userId) {
                $q->whereHas('product', function ($sub) use ($userId) {
                    $sub->whereHas('seller', function ($subSub) use ($userId) {
                        $subSub->where('user_id', $userId);
                    });
                })->whereNull('parent_id');
            });

        $allReports = $baseQuery->get();
        $countByStatus = [
            'all' => $allReports->count(),
            'pending' => $allReports->where('status', 'pending')->count(),
            'resolved' => $allReports->where('status', 'resolved')->count(),
            'dismissed' => $allReports->where('status', 'dismissed')->count(),
        ];

        \Log::info('sellerReportCounts data', [
            'seller_id' => $userId,
            'reports' => $allReports->toArray(),
            'count_by_status' => $countByStatus,
        ]);

        return response()->json([
            'success' => true,
            'count_by_status' => $countByStatus,
        ], 200);
    } catch (\Exception $e) {
        \Log::error('Lỗi khi lấy số lượng báo cáo: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi lấy số lượng báo cáo: ' . $e->getMessage(),
        ], 500);
    }
}

}