<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Review;

use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function index()
    {
        $reports = Report::with(['review.product', 'review.user', 'reporter'])
            ->where('type', 'review')
            ->latest()
            ->get()
            // Lọc những report không có đầy đủ thông tin
            ->filter(function ($report) {
                return $report->review && $report->review->product && $report->review->user;
            })
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
            ->values(); // Reset lại chỉ mục sau khi filter

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
        ])
            ->where('type', 'review')
            ->where('id', $id)
            ->first();

        // 👉 Bổ sung withCount thủ công nếu cần (nếu không eager loaded từ Report)
        if ($report && $report->review) {
            $report->review->loadCount('likes');
        }

        if (!$report) {
            return response()->json(['message' => 'Không tìm thấy'], 404);
        }

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
                    'likes_count' => $report->review->likes_count ?? 0, // ✅ Lượt thích
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
                            'type' => $m->media_type, // 'image' hoặc 'video'
                        ];
                    })->values(),
                ],
                'reporter' => $report->reporter->name ?? 'Ẩn danh',
            ]
        ]);
    }


    public function updateStatus(Request $request, $id)
    {
        $report = Report::with('review')->findOrFail($id);
        $newStatus = $request->input('status');

        $report->status = $newStatus;
        $report->save();

        // Ẩn đánh giá
        if ($newStatus === 'resolved' && $report->review) {
            $report->review->status = 'hidden';
            $report->review->save();
        }

        return response()->json(['message' => 'Cập nhật trạng thái báo cáo thành công']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'target_id' => 'required|integer',
            'type' => 'required|in:product,user,review,post',
            'reason' => 'required|string|max:1000',
        ]);

        $existing = Report::where('reporter_id', auth()->id())
            ->where('target_id', $request->target_id)
            ->where('type', $request->type)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Bạn đã báo cáo đánh giá này trước đó rồi.'
            ], 409);
        }

        Report::create([
            'reporter_id' => auth()->id(),
            'target_id' => $request->target_id,
            'type' => $request->type,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Báo cáo đã được gửi']);
    }
}
