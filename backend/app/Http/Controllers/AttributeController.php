<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::with('values')->get();

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách thuộc tính thành công.',
            'data' => $attributes,
        ], 200);
    }

    public function show($id)
    {
        $attribute = Attribute::with('values')->find($id);

        if (!$attribute) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thuộc tính.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy thông tin thuộc tính thành công.',
            'data' => $attribute,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:attributes,name',
            'slug' => 'nullable|string|max:255|unique:attributes,slug',
            'values' => 'nullable|array',
            'values.*.value' => 'required|string|max:255',
        ], [
            'name.required' => 'Tên thuộc tính là bắt buộc.',
            'name.max' => 'Tên thuộc tính không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên thuộc tính đã tồn tại.',
            'slug.unique' => 'Slug đã tồn tại.',
            'values.*.value.required' => 'Giá trị thuộc tính là bắt buộc.',
            'values.*.value.max' => 'Giá trị thuộc tính không được vượt quá 255 ký tự.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $slug = $request->slug ?? Str::slug($request->name);

        DB::beginTransaction();
        try {
            $attribute = Attribute::create([
                'name' => $request->name,
                'slug' => $slug,
            ]);
            if (!empty($request->values) && is_array($request->values)) {
                foreach ($request->values as $valueData) {
                    $attribute->values()->create([
                        'value' => $valueData['value'],
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tạo thuộc tính thành công.',
                'data' => $attribute->load('values'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Tạo thuộc tính thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $attribute = Attribute::find($id);
        if (!$attribute) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thuộc tính.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:attributes,name,' . $id,
            'slug' => 'nullable|string|max:255|unique:attributes,slug,' . $id,
            'values' => 'nullable|array',
            'values.*.value' => 'required|string|max:255',
        ], [
            'name.required' => 'Tên thuộc tính là bắt buộc.',
            'name.max' => 'Tên thuộc tính không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên thuộc tính đã tồn tại.',
            'slug.unique' => 'Slug đã tồn tại.',
            'values.*.value.required' => 'Giá trị thuộc tính là bắt buộc.',
            'values.*.value.max' => 'Giá trị thuộc tính không được vượt quá 255 ký tự.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $slug = $request->slug ?? Str::slug($request->name);

        DB::beginTransaction();
        try {
            $attribute->update([
                'name' => $request->name,
                'slug' => $slug,
            ]);

            $attribute->values()->delete();

            if (!empty($request->values) && is_array($request->values)) {
                foreach ($request->values as $valueData) {
                    $attribute->values()->create([
                        'value' => $valueData['value'],
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thuộc tính thành công.',
                'data' => $attribute->load('values'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật thuộc tính thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $attribute = Attribute::find($id);
        if (!$attribute) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thuộc tính.',
            ], 404);
        }

        DB::beginTransaction();
        try {
            $attribute->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Xóa thuộc tính thành công.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Xóa thuộc tính thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function showBySlug($slug)
    {
        $attribute = Attribute::with('values')->where('slug', $slug)->first();

        if (!$attribute) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thuộc tính.',                
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy thông tin thuộc tính thành công.',
            'data' => $attribute,
        ], 200);
    }
}