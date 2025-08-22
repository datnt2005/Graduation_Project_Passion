<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Notification;
use App\Models\User;


class PostController extends Controller
{
 public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 10);
        $categoryId = $request->get('category_id');
        $search = $request->get('search');
        $sort = $request->get('sort', 'created_at:desc');
        $limit = $request->get('limit', $perPage); // Hỗ trợ limit cho danh sách đặc biệt

        $query = Post::with('user', 'category')->where('status', 'published');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($sort) {
            $sortParts = explode(':', $sort);
            $column = $sortParts[0];
            $direction = $sortParts[1] ?? 'desc';
            // Đảm bảo cột views được xử lý
            if ($column === 'views') {
                $query->orderByRaw("COALESCE(views, 0) $direction");
            } else {
                $query->orderBy($column, $direction);
            }
        }

        $posts = $limit === $perPage ? $query->paginate($perPage) : $query->limit($limit)->get();

        foreach ($posts as $post) {
            $post->thumbnail_url = $post->thumbnail
                ? Storage::disk('r2')->url($post->thumbnail)
                : null;
        }

        return response()->json([
            'success' => true,
            'message' => 'Fetched posts successfully',
            'data' => $posts instanceof \Illuminate\Pagination\LengthAwarePaginator
                ? $posts
                : ['data' => $posts, 'current_page' => 1, 'last_page' => 1]
        ], 200);
    }

    public function store(Request $request)
{
    try {
        // Validate dữ liệu đầu vào
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:posts,slug|regex:/^[a-z0-9\-]+$/',
            'content' => 'required|string',
            'category_id' => 'required|exists:post_categories,id',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4048',
            'excerpt' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published,pending',
            'published_at' => 'nullable|date',
        ], [
            'title.required' => 'Tiêu đề là bắt buộc.',
            'title.string' => 'Tiêu đề phải là chuỗi.',
            'title.max' => 'Tiêu đề tối đa 255 ký tự.',
            'slug.regex' => 'Slug chỉ bao gồm chữ thường, số và dấu gạch ngang.',
            'slug.unique' => 'Slug đã tồn tại.',
            'content.required' => 'Nội dung là bắt buộc.',
            'content.string' => 'Nội dung phải là chuỗi.',
            'category_id.required' => 'Danh mục là bắt buộc.',
            'category_id.exists' => 'Danh mục không tồn tại.',
            'thumbnail.image' => 'File phải là ảnh.',
            'thumbnail.mimes' => 'Ảnh phải có định dạng: jpeg, png, jpg, gif, svg, webp.',
            'thumbnail.max' => 'Ảnh tối đa 4MB.',
            'excerpt.max' => 'Tóm tắt tối đa 500 ký tự.',
            'status.required' => 'Trạng thái là bắt buộc.',
            'status.in' => 'Trạng thái phải là draft, published hoặc pending.',
            'published_at.date' => 'Ngày xuất bản phải là định dạng ngày hợp lệ.',
        ]);

        // Xử lý ảnh thumbnail nếu có
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $filename = 'posts/' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            Storage::disk('r2')->put($filename, file_get_contents($file));
            $thumbnailPath = $filename;
        }

        // Tạo slug nếu chưa có
        $slug = $request->input('slug');
        if (!$slug) {
            $slug = Str::slug($request->input('title'));
        }
        $originalSlug = $slug;
        $count = 1;
        while (Post::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        // Gán thêm các giá trị còn lại
        $validated['user_id'] = Auth::id();
        $validated['thumbnail'] = $thumbnailPath;
        $validated['slug'] = $slug;

        $post = Post::create($validated);

        // Gắn URL ảnh (nếu cần hiển thị)
        $post->thumbnail_url = $thumbnailPath ? Storage::disk('r2')->url($thumbnailPath) : null;

        // Gửi thông báo cho admin
        $user = Auth::user();
        Notification::create([
            'title' => 'Bài viết mới được tạo',
            'content' => "Bài viết '{$post->title}' đã được {$user->name} tạo thành công vào " . now()->format('d/m/Y H:i'),
            'type' => 'system',
            'to_roles' => json_encode(['admin']),
            'link' => "/admin/posts/list-post",
            'user_id' => $user->id,
            'from_role' => 'system',
            'status' => 'sent',
            'channels' => json_encode(['dashboard']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tạo bài viết thành công',
            'data' => $post
        ], 201);
    } catch (ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi xác thực',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi tạo bài viết',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function show($id)
    {
        try {
            $post = Post::with('user', 'category', 'comments.user')->findOrFail($id);
            // Tăng lượt xem
            $post->increment('views');
            $post->thumbnail_url = $post->thumbnail
                ? Storage::disk('r2')->url($post->thumbnail)
                : null;

            return response()->json([
                'success' => true,
                'message' => 'Lấy bài viết thành công',
                'data' => $post
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy bài viết',
                'error' => $e->getMessage()
            ], 404);
        }
    }

public function update(Request $request, $id)
{
    try {
        $post = Post::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|unique:posts,slug,' . $id . '|regex:/^[a-z0-9\-]+$/',
            'content' => 'sometimes|required|string',
            'category_id' => 'sometimes|required|exists:post_categories,id',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4048',
            'excerpt' => 'nullable|string|max:500',
            'status' => 'sometimes|required|in:draft,published,pending',
            'published_at' => 'nullable|date',
        ], [
            'title.required' => 'Tiêu đề là bắt buộc.',
            'title.string' => 'Tiêu đề phải là chuỗi.',
            'title.max' => 'Tiêu đề tối đa 255 ký tự.',
            'slug.regex' => 'Slug chỉ bao gồm chữ thường, số và dấu gạch ngang.',
            'slug.unique' => 'Slug đã tồn tại.',
            'content.required' => 'Nội dung là bắt buộc.',
            'content.string' => 'Nội dung phải là chuỗi.',
            'category_id.required' => 'Danh mục là bắt buộc.',
            'category_id.exists' => 'Danh mục không tồn tại.',
            'thumbnail.image' => 'File phải là ảnh.',
            'thumbnail.mimes' => 'Ảnh phải có định dạng: jpeg, png, jpg, gif, svg, webp.',
            'thumbnail.max' => 'Ảnh tối đa 4MB.',
            'excerpt.max' => 'Tóm tắt tối đa 500 ký tự.',
            'status.required' => 'Trạng thái là bắt buộc.',
            'status.in' => 'Trạng thái phải là draft, published hoặc pending.',
            'published_at.date' => 'Ngày xuất bản phải là định dạng ngày hợp lệ.',
        ]);

        // Xử lý ảnh nếu có
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $filename = 'posts/' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            Storage::disk('r2')->put($filename, file_get_contents($file));
            $validated['thumbnail'] = $filename;
        }

        // Slug logic
        $slug = $request->input('slug', $post->slug);
        if (!$slug || $slug === $post->slug) {
            $slug = Str::slug($request->input('title', $post->title));
        }
        $originalSlug = $slug;
        $count = 1;
        while (Post::where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }
        $validated['slug'] = $slug;

        // Cập nhật
        $post->update($validated);

        $post = $post->fresh();
        $post->thumbnail_url = $post->thumbnail
            ? Storage::disk('r2')->url($post->thumbnail)
            : null;

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật bài viết thành công',
            'data' => $post
        ], 200);
    } catch (ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi xác thực',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi cập nhật bài viết',
            'error' => $e->getMessage()
        ], 500);
    }
}


    public function destroy($id)
    {
        try {
            $post = Post::findOrFail($id);
            $post->delete();
            return response()->json([
                'success' => true,
                'message' => 'Xoá bài viết thành công'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xoá bài viết',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function showBySlug($slug)
    {
        try {
            $post = Post::with(['user', 'category', 'comments.user'])->where('slug', $slug)->firstOrFail();
            // Tăng lượt xem
            $post->increment('views');
            $post->thumbnail_url = $post->thumbnail ? Storage::disk('r2')->url($post->thumbnail) : null;

            return response()->json([
                'success' => true,
                'data' => $post
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy bài viết',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function getAllPost(Request $request)
    {
        try {
            $posts = Post::with(['user', 'category'])->get();
            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách bài viết',
                'data' => $posts
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy bài viết',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
