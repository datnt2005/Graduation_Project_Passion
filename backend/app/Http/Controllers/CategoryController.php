<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
    ], [
        'name.required' => 'Tên danh mục là bắt buộc.',
        'name.max' => 'Tên danh mục không được vượt quá 255 ký tự.',
        'parent_id.exists' => 'Danh mục cha không tồn tại.',
        'slug.unique' => 'Slug đã tồn tại.',
        'image.image' => 'Tệp phải là hình ảnh.',
        'image.mimes' => 'Hình ảnh phải có định dạng jpeg, png, jpg, gif, svg hoặc webp.',
        'image.max' => 'Hình ảnh không được vượt quá 2MB.',
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

    $imagePath = null;

    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $filename = 'categories/' . time() . '_' . $file->getClientOriginalName();
        Storage::disk('r2')->put($filename, file_get_contents($file));
        $imagePath = $filename;
    }

    $category = Category::create([
        'name' => $request->name,
        'slug' => $slug,
        'parent_id' => $request->parent_id,
        'image' => $imagePath,
    ]);

    $category->image_url = $imagePath ? Storage::disk('r2')->url($imagePath) : null;

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
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
    ], [
        'name.required' => 'Tên danh mục là bắt buộc.',
        'name.max' => 'Tên danh mục không được vượt quá 255 ký tự.',
        'parent_id.exists' => 'Danh mục cha không tồn tại.',
        'slug.unique' => 'Slug đã tồn tại.',
        'image.image' => 'Tệp phải là hình ảnh.',
        'image.mimes' => 'Hình ảnh phải có định dạng jpeg, png, jpg, gif, svg hoặc webp.',
        'image.max' => 'Hình ảnh không được vượt quá 2MB.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ.',
            'errors' => $validator->errors(),
        ], 422);
    }

    $slug = $request->slug ?? Str::slug($request->name);
    if (Category::where('slug', $slug)->where('id', '!=', $id)->exists()) {
        return response()->json([
            'success' => false,
            'message' => 'Slug đã tồn tại.',
        ], 422);
    }

    $imagePath = $category->image;

    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $filename = 'categories/' . time() . '_' . $file->getClientOriginalName();

        Storage::disk('r2')->put($filename, file_get_contents($file));

        // Xóa ảnh cũ nếu có
        if ($imagePath && Storage::disk('r2')->exists($imagePath)) {
            Storage::disk('r2')->delete($imagePath);
        }

        $imagePath = $filename;
    }

    $category->update([
        'name' => $request->name,
        'slug' => $slug,
        'parent_id' => $request->parent_id,
        'image' => $imagePath,
    ]);

    $category->image_url = $imagePath ? Storage::disk('r2')->url($imagePath) : null;

    return response()->json([
        'success' => true,
        'message' => 'Cập nhật danh mục thành công.',
        'data' => $category,
    ]);
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