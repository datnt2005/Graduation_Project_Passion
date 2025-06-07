<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class TagController extends Controller
{
    // Lấy danh sách tag (API)
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách thẻ thành công.',
            'tags' => Tag::all(),
        ], 200);
    }

    // Lấy thông tin tag theo ID (API)
    public function show($id)
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thẻ.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy thông tin thẻ thành công.',
            'data' => $tag,
        ], 200);
    }

    // Tạo mới tag (API)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|unique:tags,slug',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ], [
            'name.required' => 'Tên thẻ là bắt buộc.',
            'name.max' => 'Tên thẻ không được vượt quá 255 ký tự.',
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
        if (Tag::where('slug', $slug)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Slug đã tồn tại.',
            ], 422);
        }

        $imagePath = null;

    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $filename = 'tags/' . time() . '_' . $file->getClientOriginalName();
        Storage::disk('r2')->put($filename, file_get_contents($file));
        $imagePath = $filename;
    }
        $tag = Tag::create([
            'name' => $request->name,
            'slug' => $slug,
            'image' => $imagePath,
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Tạo thẻ thành công.',
            'data' => $tag,
        ], 201);
    }

    // Cập nhật tag (API)
    public function update(Request $request, $id)
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thẻ.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|unique:tags,slug,' . $id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ], [
            'name.required' => 'Tên thẻ là bắt buộc.',
            'name.max' => 'Tên thẻ không được vượt quá 255 ký tự.',
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
        if (Tag::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Slug đã tồn tại.',
            ], 422);
        }
        $imagePath = $tag->image;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'tags/' . time() . '_' . $file->getClientOriginalName();
            Storage::disk('r2')->put($filename, file_get_contents($file));
            $imagePath = $filename;
        }
        $tag->update([
            'name' => $request->name,
            'slug' => $request->slug ?? Str::slug($request->name),
            'image' => $imagePath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thẻ thành công.',
            'data' => $tag,
        ], 200);
    }

    // Xóa tag (API)
    public function destroy($id)
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thẻ.',
            ], 404);
        }

        $tag->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa thẻ thành công.',
        ], 200);
    }
}