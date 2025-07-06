<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('user', 'category')->latest()->get();

        // Nếu có ảnh, trả về url đầy đủ
        foreach ($posts as $post) {
            $post->thumbnail_url = $post->thumbnail
                ? Storage::disk('r2')->url($post->thumbnail)
                : null;
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách bài viết thành công',
            'data' => $posts
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'category_id' => 'required|exists:post_categories,id',
                'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4048'
            ], [
                'title.required' => 'Tiêu đề là bắt buộc.',
                'title.string' => 'Tiêu đề phải là chuỗi.',
                'title.max' => 'Tiêu đề tối đa 255 ký tự.',
                'content.required' => 'Nội dung là bắt buộc.',
                'content.string' => 'Nội dung phải là chuỗi.',
                'category_id.required' => 'Danh mục là bắt buộc.',
                'category_id.exists' => 'Danh mục không tồn tại.',
                'thumbnail.image' => 'File phải là ảnh.',
                'thumbnail.mimes' => 'Ảnh phải có định dạng: jpeg, png, jpg, gif, svg, webp.',
                'thumbnail.max' => 'Ảnh tối đa 4MB.',
            ]);

            $thumbnailPath = null;
            if ($request->hasFile('thumbnail')) {
                $file = $request->file('thumbnail');
                $filename = 'posts/' . time() . '_' . $file->getClientOriginalName();
                Storage::disk('r2')->put($filename, file_get_contents($file));
                $thumbnailPath = $filename;
            }

            $validated['user_id'] = Auth::id(); 
        

            $validated['thumbnail'] = $thumbnailPath;

            $post = Post::create($validated);

            $post->thumbnail_url = $thumbnailPath ? Storage::disk('r2')->url($thumbnailPath) : null;

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
                'content' => 'sometimes|required|string',
                'category_id' => 'sometimes|required|exists:post_categories,id',
                'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4048'
            ], [
                'title.required' => 'Tiêu đề là bắt buộc.',
                'title.string' => 'Tiêu đề phải là chuỗi.',
                'title.max' => 'Tiêu đề tối đa 255 ký tự.',
                'content.required' => 'Nội dung là bắt buộc.',
                'content.string' => 'Nội dung phải là chuỗi.',
                'category_id.required' => 'Danh mục là bắt buộc.',
                'category_id.exists' => 'Danh mục không tồn tại.',
                'thumbnail.image' => 'File phải là ảnh.',
                'thumbnail.mimes' => 'Ảnh phải có định dạng: jpeg, png, jpg, gif, svg, webp.',
                'thumbnail.max' => 'Ảnh tối đa 4MB.',
            ]);

            if ($request->hasFile('thumbnail')) {
                $file = $request->file('thumbnail');
                $filename = 'posts/' . time() . '_' . $file->getClientOriginalName();
                Storage::disk('r2')->put($filename, file_get_contents($file));
                $validated['thumbnail'] = $filename;
            }

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
}
