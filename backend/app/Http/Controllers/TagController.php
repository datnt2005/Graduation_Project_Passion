<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

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
        ], [
            'name.required' => 'Tên thẻ là bắt buộc.',
            'name.max' => 'Tên thẻ không được vượt quá 255 ký tự.',
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

        $tag = Tag::create([
            'name' => $request->name,
            'slug' => $slug,
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
        ], [
            'name.required' => 'Tên thẻ là bắt buộc.',
            'name.max' => 'Tên thẻ không được vượt quá 255 ký tự.',
            'slug.unique' => 'Slug đã tồn tại.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $tag->update([
            'name' => $request->name,
            'slug' => $request->slug ?? Str::slug($request->name),
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