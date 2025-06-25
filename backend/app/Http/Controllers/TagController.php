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
public function index(Request $request)
{
    $perPage = $request->input('per_page', 10); // Mặc định 10, có thể truyền ?per_page=15 nếu muốn

    $tags = Tag::paginate($perPage);

    return response()->json([
        'success' => true,
        'message' => 'Lấy danh sách thẻ thành công.',
        'data' => [
            'tags' => $tags->items(),
            'current_page' => $tags->currentPage(),
            'last_page' => $tags->lastPage(),
            'total' => $tags->total(),
        ],
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
        // Xác thực dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:tags,slug|regex:/^[a-zA-Z0-9-]+$/',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ], [
            'name.required' => 'Tên thẻ là bắt buộc.',
            'name.max' => 'Tên thẻ không được vượt quá 255 ký tự.',
            'slug.unique' => 'Slug đã tồn tại.',
            'slug.regex' => 'Slug chỉ được chứa chữ cái, số và dấu gạch ngang, không được chứa khoảng trắng hoặc ký tự đặc biệt.',
            'slug.max' => 'Slug không được vượt quá 255 ký tự.',
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

        // Tạo slug: nếu không có slug trong request, tự động tạo từ name
        $slug = $request->filled('slug') ? $request->slug : Str::slug($request->name);

        // Lưu thẻ vào cơ sở dữ liệu
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
        // Tìm thẻ theo ID
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thẻ.',
            ], 404);
        }

        // Xác thực dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:tags,slug,' . $id . '|regex:/^[a-zA-Z0-9-]+$/',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ], [
            'name.required' => 'Tên thẻ là bắt buộc.',
            'name.max' => 'Tên thẻ không được vượt quá 255 ký tự.',
            'slug.unique' => 'Slug đã tồn tại.',
            'slug.regex' => 'Slug chỉ được chứa chữ cái, số và dấu gạch ngang, không được chứa khoảng trắng hoặc ký tự đặc biệt.',
            'slug.max' => 'Slug không được vượt quá 255 ký tự.',
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

        // Tạo slug: nếu không có slug trong request, tự động tạo từ name
        $slug = $request->filled('slug') ? $request->slug : Str::slug($request->name);

        // Xử lý hình ảnh
        $imagePath = $tag->image;
        if ($request->hasFile('image')) {
            // Xóa hình ảnh cũ nếu có
            if ($imagePath && Storage::disk('r2')->exists($imagePath)) {
                Storage::disk('r2')->delete($imagePath);
            }

            // Lưu hình ảnh mới
            $file = $request->file('image');
            $filename = 'tags/' . time() . '_' . $file->getClientOriginalName();
            Storage::disk('r2')->put($filename, file_get_contents($file));
            $imagePath = $filename;
        }

        // Cập nhật thẻ
        $tag->update([
            'name' => $request->name,
            'slug' => $slug,
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