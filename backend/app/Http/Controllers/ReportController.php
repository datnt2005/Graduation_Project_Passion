<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\PostComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        return response()->json(['message' => 'Xóa báo cáo thành công.']);
    }

    public function sellerIndex(Request $request)
    {
        $userId = auth()->id();

        $reports = Report::with([
                'review.product.seller',
                'review.user',
                'reporter'
            ])
            ->where('type', 'review')
            ->whereHas('review.product.seller', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
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

    public function sellerUpdateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:resolved,dismissed',
        ]);

        $userId = auth()->id();

        $report = Report::with('review.product.seller')->findOrFail($id);

        if (
            !$report->review || !$report->review->product ||
            !$report->review->product->seller || $report->review->product->seller->user_id !== $userId
        ) {
            return response()->json(['message' => 'Bạn không có quyền xử lý báo cáo này'], 403);
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
}
