<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostCommentLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PostCommentController extends Controller
{
    /**
     * Constructor to apply middleware for admin routes.
     */

    /**
     * Get paginated comments for admin management.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
public function indexAdmin(Request $request)
{
    try {
        // Validate pagination parameters
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);

        if (!is_numeric($perPage) || $perPage < 1 || $perPage > 100) {
            return response()->json([
                'success' => false,
                'message' => 'Số lượng bản ghi mỗi trang không hợp lệ (1-100)',
            ], 400);
        }

        if (!is_numeric($page) || $page < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Số trang không hợp lệ',
            ], 400);
        }

        // Build query with eager loading
        $query = PostComment::with([
            'user',
            'post',
            'media',
            'replies' => function ($q) {
                $q->with(['user', 'media']);
            },
            'likes'
        ])->latest();

        // Apply status filter
        if ($request->has('status') && in_array($request->input('status'), ['approved', 'pending'])) {
            $query->where('status', $request->input('status'));
        }

        // Apply search filter
        if ($request->has('search') && $search = trim($request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                  ->orWhere('admin_reply', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('post', function ($q) use ($search) {
                      $q->where('title', 'like', "%{$search}%");
                  });
            });
        }

        // Paginate results
        $comments = $query->paginate((int) $perPage, ['*'], 'page', (int) $page);

        // Transform and return response
        return response()->json([
            'success' => true,
            'data' => [
                'data' => $comments->map(function ($comment) {
                    return $this->transformComment($comment);
                })->toArray(),
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage(),
                'total' => $comments->total(),
            ],
        ], 200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy dữ liệu',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi server khi lấy danh sách bình luận: ' . $e->getMessage(),
        ], 500);
    }
}

    /**
     * Get comments by post for public view.
     *
     * @param int $postId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByPost($postId)
    {
        try {
            $comments = PostComment::with(['user', 'media', 'replies.user', 'replies.media', 'likes'])
                ->where('post_id', $postId)
                ->whereNull('parent_id')
                ->where('status', 'approved') // Only show approved comments
                ->latest()
                ->get()
                ->map(function ($comment) {
                    return $this->transformComment($comment);
                });

            return response()->json(['success' => true, 'data' => $comments], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bài viết không tồn tại',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server khi lấy bình luận',
            ], 500);
        }
    }

    /**
     * Store a new comment.
     *
     * @param Request $request
     * @param Post $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Post $post)
    {
        try {
            $data = $request->validate([
                'content' => 'required|string|max:2000',
                'rating' => 'nullable|integer|min:1|max:5',
                'images.*' => 'nullable|image|max:2048',
                'videos.*' => 'nullable|mimetypes:video/mp4,video/quicktime|max:10000',
            ]);

            $data['post_id'] = $post->id;
            $data['user_id'] = Auth::id();
            $data['status'] = Auth::user()->hasRole('admin') ? 'approved' : 'pending'; // Auto-approve for admins

            $comment = PostComment::create($data);

            // Store media
            $this->storeMedia($request, $comment);

            return response()->json([
                'success' => true,
                'message' => 'Đã gửi bình luận',
                'data' => $this->transformComment($comment->fresh())
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi gửi bình luận: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a comment (user-facing).
     *
     * @param Request $request
     * @param Post $post
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Post $post, $id)
    {
        try {
            $comment = PostComment::where('id', $id)->where('post_id', $post->id)->firstOrFail();

            if ($comment->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
                return response()->json(['success' => false, 'message' => 'Không có quyền'], 403);
            }

            $data = $request->validate([
                'content' => 'required|string|max:2000',
                'kept_images' => 'nullable|array',
                'images.*' => 'nullable|image|max:2048',
                'videos.*' => 'nullable|mimetypes:video/mp4,video/quicktime|max:10000',
            ]);

            $comment->update([
                'content' => $data['content'],
                'rating' => $request->input('rating', $comment->rating),
                'status' => Auth::user()->hasRole('admin') ? 'approved' : $comment->status,
            ]);

            // Delete media not kept
            $kept = $request->input('kept_images', []);
            $comment->media()->whereNotIn('id', $kept)->each(function ($m) {
                Storage::disk('r2')->delete($m->media_url);
                $m->delete();
            });

            // Store new media
            $this->storeMedia($request, $comment);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thành công',
                'data' => $this->transformComment($comment->fresh())
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bình luận không tồn tại',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật bình luận: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a comment (admin-specific, for status or admin_reply).
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
   public function updateAdmin(Request $request, $id)
{
    try {
        $comment = PostComment::findOrFail($id);

        $data = $request->validate([
            'status' => 'nullable|string|in:approved,rejected,pending',
            'admin_reply' => 'nullable|string|max:500',
        ]);

        $updateData = [];

        if (array_key_exists('status', $data)) {
            $updateData['status'] = $data['status'];
        }

        if (array_key_exists('admin_reply', $data)) {
            $updateData['admin_reply'] = $data['admin_reply'];
        }

        if (!empty($updateData)) {
            $comment->update($updateData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật bình luận thành công',
            'data' => $this->transformComment($comment->fresh()),
        ], 200);

    } catch (ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Bình luận không tồn tại',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi cập nhật bình luận: ' . $e->getMessage(),
        ], 500);
    }
}

    /**
     * Delete a comment (user-facing).
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $comment = PostComment::findOrFail($id);

            if ($comment->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
                return response()->json(['success' => false, 'message' => 'Không có quyền'], 403);
            }

            DB::beginTransaction();
            foreach ($comment->media as $media) {
                Storage::disk('r2')->delete($media->media_url);
                $media->delete();
            }
            $comment->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Xoá bình luận thành công'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bình luận không tồn tại',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa bình luận: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a comment (admin-specific).
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyAdmin($id)
    {
        try {
            $comment = PostComment::findOrFail($id);

            DB::beginTransaction();
            foreach ($comment->media as $media) {
                Storage::disk('r2')->delete($media->media_url);
                $media->delete();
            }
            $comment->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Xoá bình luận thành công'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bình luận không tồn tại',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa bình luận: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Like a comment.
     *
     * @param Request $request
     * @param int $postId
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function like(Request $request, $postId, $id)
    {
        try {
            $post = Post::findOrFail($postId);
            $comment = PostComment::where('post_id', $postId)->findOrFail($id);

            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng đăng nhập để thích bình luận',
                ], 401);
            }

            $userId = Auth::id();
            if ($comment->likes()->where('user_id', $userId)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã thích bình luận này trước đó',
                ], 409);
            }

            DB::beginTransaction();
            $comment->likes()->create(['user_id' => $userId]);
            $likesCount = $comment->likes()->count();
            DB::commit();

            return response()->json([
                'success' => true,
                'liked' => true,
                'likes' => $likesCount,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bình luận hoặc bài viết không tồn tại',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server khi thích bình luận: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unlike a comment.
     *
     * @param Request $request
     * @param int $postId
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function unlike(Request $request, $postId, $id)
    {
        try {
            $post = Post::findOrFail($postId);
            $comment = PostComment::where('post_id', $postId)->findOrFail($id);

            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng đăng nhập để bỏ thích bình luận',
                ], 401);
            }

            $userId = Auth::id();
            $like = PostCommentLike::where('post_comment_id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$like) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn chưa thích bình luận này',
                ], 400);
            }

            DB::beginTransaction();
            $like->delete();
            $likesCount = $comment->likes()->count();
            DB::commit();

            return response()->json([
                'success' => true,
                'liked' => false,
                'likes' => $likesCount,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bình luận hoặc bài viết không tồn tại',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server khi bỏ thích bình luận: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if a comment is liked by the authenticated user.
     *
     * @param Request $request
     * @param int $postId
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function liked(Request $request, $postId, $id)
    {
        try {
            $post = Post::findOrFail($postId);
            $comment = PostComment::where('post_id', $postId)->findOrFail($id);

            $liked = Auth::check() && $comment->likes()->where('user_id', Auth::id())->exists();

            return response()->json([
                'success' => true,
                'liked' => $liked,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bình luận hoặc bài viết không tồn tại',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server khi kiểm tra trạng thái thích: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reply to a comment.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function reply(Request $request, $id)
    {
        try {
            $parent = PostComment::findOrFail($id);
            if ($parent->user_id === Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Không thể tự trả lời bình luận mình'], 400);
            }

            $data = $request->validate([
                'content' => 'required|string|max:2000',
                'images.*' => 'nullable|image|max:2048',
                'videos.*' => 'nullable|mimetypes:video/mp4,video/quicktime|max:10000',
            ]);

            $reply = PostComment::create([
                'post_id' => $parent->post_id,
                'user_id' => Auth::id(),
                'parent_id' => $parent->id,
                'content' => $data['content'],
                'status' => Auth::user()->hasRole('admin') ? 'approved' : 'pending',
            ]);

            // Store media
            $this->storeMedia($request, $reply);

            return response()->json([
                'success' => true,
                'message' => 'Đã trả lời',
                'data' => $this->transformComment($reply->fresh())
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bình luận không tồn tại',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi trả lời bình luận: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store media for a comment.
     *
     * @param Request $request
     * @param PostComment $comment
     * @return void
     */
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

    /**
     * Transform a comment for API response.
     *
     * @param PostComment $comment
     * @return array
     */
    private function transformComment(PostComment $comment)
    {
        return [
            'id' => $comment->id,
            'user' => [
                'id' => $comment->user->id,
                'name' => $comment->user->name,
                'avatar' => $comment->user->avatar ?? null,
            ],
            'post' => [
                'id' => $comment->post->id,
                'title' => $comment->post->title,
                'slug' => $comment->post->slug,
            ],
            'content' => $comment->content,
            'rating' => $comment->rating,
            'status' => $comment->status ?? 'pending',
            'admin_reply' => $comment->admin_reply,
            'created_at' => $comment->created_at,
            'likes_count' => $comment->likes->count(),
            'liked' => Auth::check() && $comment->likes->contains('user_id', Auth::id()),
            'media' => $comment->media->map(fn ($m) => [
                'id' => $m->id,
                'url' => Storage::disk('r2')->url($m->media_url),
                'type' => $m->media_type,
            ]),
            'replies' => $comment->replies->map(fn ($r) => $this->transformComment($r)),
        ];
    }
}