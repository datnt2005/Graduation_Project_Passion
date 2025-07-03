<?php

namespace App\Http\Controllers;

use App\Models\PostComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PostCommentController extends Controller
{
    public function index()
    {
        $comments = \App\Models\PostComment::with(['user', 'post'])
            ->latest()
            ->get();

        return response()->json($comments);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'post_id' => 'required|exists:posts,id',
            'content' => 'required|string|max:2000',
            'rating'  => 'nullable|integer|min:1|max:5',
            'image'   => 'nullable|image|max:2048',
        ]);

        // Lấy user_id từ user đăng nhập
        $data['user_id'] = Auth::id();

        if ($file = $request->file('image')) {
            $filename = 'comments/' . time() . '_' . $file->getClientOriginalName();
            Storage::disk('r2')->put($filename, file_get_contents($file));
            $data['image'] = $filename;
        }

        $comment = PostComment::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Thêm bình luận thành công',
            'data'    => $comment
        ], 201);
    }

    public function show($id)
    {
        $comment = PostComment::with(['user', 'post'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $comment], 200);
    }

    public function update(Request $request, $id)
    {
        $comment = PostComment::findOrFail($id);

        // Nếu là admin, chỉ cần có is_admin flag
        if (Auth::user()?->is_admin && $request->has('admin_reply')) {
            $comment->update(['admin_reply' => $request->input('admin_reply')]);
            return response()->json([
                'success' => true,
                'message' => 'Phản hồi comment thành công',
                'data'    => $comment->load('user', 'post'),
            ], 200);
        }

        // User thường chỉ sửa comment của mình
        if ($comment->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'content' => 'required|string|max:2000',
        ], [
            'content.required' => 'Nội dung là bắt buộc.',
            'content.max'      => 'Nội dung tối đa 2000 ký tự.',
        ]);

        $comment->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật bình luận thành công',
            'data'    => $comment->load('user', 'post'),
        ], 200);
    }

    public function destroy($id)
    {
        $comment = PostComment::findOrFail($id);

        if ($comment->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa bình luận thành công',
        ], 200);
    }

    // Lấy comment theo post
    public function getByPost($postId)
    {
        $comments = PostComment::where('post_id', $postId)
            ->with('user')
            ->latest()
            ->get();

        return response()->json(['data' => $comments], 200);
    }
}
