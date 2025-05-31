<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách danh mục thành công.',
            'categories' => Category::with('parent')->get(),
        ], 200);
    }

    public function show($id)
    {
        $category = Category::with('parent')->find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy danh mục.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy thông tin danh mục thành công.',
            'data' => $category,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'slug' => 'nullable|unique:categories,slug',
        ], [
            'name.required' => 'Tên danh mục là bắt buộc.',
            'name.max' => 'Tên danh mục không được vượt quá 255 ký tự.',
            'parent_id.exists' => 'Danh mục cha không tồn tại.',
            'slug.unique' => 'Slug đã tồn tại.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $slug = $request->slug ?? Str::slug($request->name);
        if (Category::where('slug', $slug)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Slug đã tồn tại.',
            ], 422);
        }
        if ($request->hasFile(key: 'image')) {
                $path = $request->file('image')->store('images', options: 's3');
                $data['image'] = "/passion/{$path}";
            }
        
        $category = Category::create([
            'name' => $request->name,
            'slug' => $slug,
            'parent_id' => $request->parent_id,
            'image' => $data['image'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tạo danh mục thành công.',
            'data' => $category,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy danh mục.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'slug' => 'nullable|unique:categories,slug,' . $id,
        ], [
            'name.required' => 'Tên danh mục là bắt buộc.',
            'name.max' => 'Tên danh mục không được vượt quá 255 ký tự.',
            'parent_id.exists' => 'Danh mục cha không tồn tại.',
            'slug.unique' => 'Slug đã tồn tại.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->hasFile(key: 'image')) {
            $path = $request->file('image')->store('images', options: 's3');
            $data['image'] = "/passion/{$path}";
        }
        if (isset($data['image'])) {
            $category->image = $data['image'];
        }
        $category->update([
            'name' => $request->name,
            'slug' => $request->slug ?? Str::slug($request->name),
            'parent_id' => $request->parent_id,
            'image' => $data['image'] ?? $category->image,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật danh mục thành công.',
            'data' => $category,
        ], 200);
    }

    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy danh mục.',
            ], 404);
        }

        if ($category->children()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa danh mục vì có danh mục con.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa danh mục thành công.',
        ], 200);
    }

    public function children($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy danh mục.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách danh mục con thành công.',
            'categories' => $category->children()->get(),
        ], 200);
    }

    public function parents($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy danh mục.',
            ], 404);
        }

        $parents = [];
        while ($category->parent) {
            $parents[] = $category->parent;
            $category = $category->parent;
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách danh mục cha thành công.',
            'categories' => array_reverse($parents),
        ], 200);
    }
}