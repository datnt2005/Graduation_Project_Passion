<?php

namespace App\Http\Controllers;

use App\Models\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class PostCategoryController extends Controller
{
    public function index()
    {
        try {
            $page = request()->get('page', 1);
            $perPage = request()->get('per_page', 10);

            $categories = PostCategory::orderByDesc('id')->paginate($perPage);

            foreach ($categories as $cat) {
                $cat->image_url = $cat->image ? Storage::disk('r2')->url($cat->image) : null;
            }

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách danh mục thành công',
                'data' => $categories
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách danh mục',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:post_categories',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4048'
            ], [
                'name.required' => 'Tên danh mục là bắt buộc.',
                'name.string' => 'Tên danh mục phải là chuỗi.',
                'name.max' => 'Tên danh mục tối đa 255 ký tự.',
                'slug.required' => 'Slug là bắt buộc.',
                'slug.string' => 'Slug phải là chuỗi.',
                'slug.max' => 'Slug tối đa 255 ký tự.',
                'slug.unique' => 'Slug đã tồn tại.',
                'image.image' => 'File phải là ảnh.',
                'image.mimes' => 'Ảnh phải có định dạng: jpeg, png, jpg, gif, svg, webp.',
                'image.max' => 'Ảnh tối đa 4MB.',
            ]);

            $imagePath = null;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = 'post_categories/' . time() . '_' . $file->getClientOriginalName();
                Storage::disk('r2')->put($filename, file_get_contents($file));
                $imagePath = $filename;
            }

            $category = PostCategory::create([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'image' => $imagePath,
            ]);

            // Gán image_url cho response
            $category->image_url = $imagePath ? Storage::disk('r2')->url($imagePath) : null;

            return response()->json([
                'success' => true,
                'message' => 'Tạo danh mục thành công',
                'data' => $category
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
                'message' => 'Lỗi khi tạo danh mục',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $category = PostCategory::findOrFail($id);
            $category->image_url = $category->image ? Storage::disk('r2')->url($category->image) : null;

            return response()->json([
                'success' => true,
                'data' => $category
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy danh mục',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $category = PostCategory::findOrFail($id);
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:post_categories,slug,' . $id,
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4048'
            ], [
                'name.required' => 'Tên danh mục là bắt buộc.',
                'name.string' => 'Tên danh mục phải là chuỗi.',
                'name.max' => 'Tên danh mục tối đa 255 ký tự.',
                'slug.required' => 'Slug là bắt buộc.',
                'slug.string' => 'Slug phải là chuỗi.',
                'slug.max' => 'Slug tối đa 255 ký tự.',
                'slug.unique' => 'Slug đã tồn tại.',
                'image.image' => 'File phải là ảnh.',
                'image.mimes' => 'Ảnh phải có định dạng: jpeg, png, jpg, gif, svg, webp.',
                'image.max' => 'Ảnh tối đa 4MB.',
            ]);

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = 'post_categories/' . time() . '_' . $file->getClientOriginalName();
                Storage::disk('r2')->put($filename, file_get_contents($file));
                $validated['image'] = $filename;
            }

            $category->update($validated);

            // Gán image_url cho response
            $category = $category->fresh();
            $category->image_url = $category->image ? Storage::disk('r2')->url($category->image) : null;

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật danh mục thành công',
                'data' => $category
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
                'message' => 'Lỗi khi cập nhật danh mục',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $deleted = PostCategory::destroy($id);
            return response()->json([
                'success' => (bool)$deleted,
                'message' => $deleted ? 'Xoá danh mục thành công' : 'Không tìm thấy danh mục'
            ], $deleted ? 200 : 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xoá danh mục',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}