<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostCommentLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostCommentController extends Controller
{
    public function getByPost($postId)
    {
        $comments = PostComment::with(['user', 'media', 'replies.user', 'replies.media', 'likes'])
            ->where('post_id', $postId)
            ->whereNull('parent_id')
            ->latest()
            ->get()
            ->map(function ($comment) {
                return $this->transformComment($comment);
            });

        return response()->json(['success' => true, 'data' => $comments], 200);
    }

public function store(Request $request, Post $post)
    {
        $data = $request->validate([
            'content' => 'required|string|max:2000',
            'rating'  => 'nullable|integer|min:1|max:5',
            'images.*' => 'nullable|image|max:2048',
            'videos.*' => 'nullable|mimetypes:video/mp4,video/quicktime|max:10000',
        ]);

        $data['post_id'] = $post->id;
        $data['user_id'] = Auth::id();

        $comment = PostComment::create($data);

        // Lưu media
        $this->storeMedia($request, $comment);

        return response()->json([
            'success' => true,
            'message' => 'Đã gửi bình luận',
            'data' => $this->transformComment($comment->fresh())
        ], 201);
    }

    public function update(Request $request, Post $post, $id)
    {
        $comment = PostComment::where('id', $id)->where('post_id', $post->id)->firstOrFail();

        if ($comment->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Không có quyền'], 403);
        }

        $data = $request->validate([
            'content' => 'required|string|max:2000',
            'kept_images' => 'nullable|array',
            'images.*' => 'nullable|image|max:2048',
            'videos.*' => 'nullable|mimetypes:video/mp4,video/quicktime|max:10000',
        ]);

        $comment->update(['content' => $data['content'], 'rating' => $request->input('rating', $comment->rating)]);

        // Xoá media không giữ lại
        $kept = $request->input('kept_images', []);
        $comment->media()->whereNotIn('id', $kept)->each(function ($m) {
            Storage::disk('r2')->delete($m->media_url);
            $m->delete();
        });

        // Lưu media mới
        $this->storeMedia($request, $comment);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thành công',
            'data' => $this->transformComment($comment->fresh())
        ], 200);
    }

    public function destroy($id)
    {
        $comment = PostComment::findOrFail($id);

        if ($comment->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            return response()->json(['success' => false, 'message' => 'Không có quyền'], 403);
        }

        foreach ($comment->media as $media) {
            Storage::disk('r2')->delete($media->media_url);
            $media->delete();
        }

        $comment->delete();

        return response()->json(['success' => true, 'message' => 'Xoá bình luận thành công'], 200);
    }

    public function like($id)
    {
        $comment = PostComment::findOrFail($id);

        if ($comment->likes()->where('user_id', Auth::id())->exists()) {
            return response()->json(['success' => false, 'message' => 'Đã thích trước đó'], 409);
        }

        $comment->likes()->create(['user_id' => Auth::id()]);
        $likesCount = $comment->likes()->count();

        return response()->json(['success' => true, 'liked' => true, 'likes' => $likesCount]);
    }

    public function unlike($id)
    {
        $like = PostCommentLike::where('post_comment_id', $id)->where('user_id', Auth::id())->first();
        if (!$like) return response()->json(['success' => false, 'message' => 'Chưa thích'], 400);

        $like->delete();
        $comment = PostComment::findOrFail($id);
        $likesCount = $comment->likes()->count();

        return response()->json(['success' => true, 'liked' => false, 'likes' => $likesCount]);
    }

    public function checkLiked($id)
    {
        $liked = PostCommentLike::where('post_comment_id', $id)->where('user_id', Auth::id())->exists();
        return response()->json(['success' => true, 'liked' => $liked]);
    }

    public function reply(Request $request, $id)
    {
        $parent = PostComment::findOrFail($id);
        if ($parent->user_id === Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Không thể tự trả lời bình luận mình'], 400);
        }

        $data = $request->validate(['content' => 'required|string|max:2000']);

        $reply = PostComment::create([
            'post_id' => $parent->post_id,
            'user_id' => Auth::id(),
            'parent_id' => $parent->id,
            'content' => $data['content'],
        ]);

        return response()->json(['success' => true, 'message' => 'Đã trả lời', 'data' => $this->transformComment($reply->fresh())], 201);
    }

    private function storeMedia(Request $request, PostComment $comment)
    {
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $filename = 'post_comments/' . uniqid() . '.' . $file->getClientOriginalExtension();
                Storage::disk('r2')->put($filename, file_get_contents($file));
                $comment->media()->create(['media_url' => $filename, 'media_type' => 'image']);
            }
        }

        if ($request->hasFile('videos')) {
            foreach ($request->file('videos') as $file) {
                $filename = 'post_comments/' . uniqid() . '.' . $file->getClientOriginalExtension();
                Storage::disk('r2')->put($filename, file_get_contents($file));
                $comment->media()->create(['media_url' => $filename, 'media_type' => 'video']);
            }
        }
    }

    private function transformComment(PostComment $comment)
    {
        return [
            'id' => $comment->id,
            'user' => [
                'id' => $comment->user->id,
                'name' => $comment->user->name,
                'avatar' => $comment->user->avatar_url ?? null,
            ],
            'content' => $comment->content,
            'rating' => $comment->rating,
            'created_at' => $comment->created_at,
            'likes_count' => $comment->likes->count(),
            'liked' => $comment->likes->contains('user_id', Auth::id()),
            'media' => $comment->media->map(fn ($m) => [
                'id' => $m->id,
                'url' => Storage::disk('r2')->url($m->media_url),
                'type' => $m->media_type,
            ]),
            'replies' => $comment->replies->map(fn ($r) => $this->transformComment($r)),
        ];
    }
}
