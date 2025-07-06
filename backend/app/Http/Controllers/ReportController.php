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
        $report = Report::with(['reporter', 'review.product', 'review.user', 'postComment.user', 'postComment.media'])
            ->findOrFail($id);

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
            $data['rating'] = $report->review->rating;
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
        $report = Report::findOrFail($id);

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
        }

        return response()->json(['message' => 'Cập nhật trạng thái thành công.']);
    }

    public function destroy($id)
    {
        $report = Report::findOrFail($id);
        $report->delete();

        return response()->json(['message' => 'Xóa báo cáo thành công.']);
    }
}
