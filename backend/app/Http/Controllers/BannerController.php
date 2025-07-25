<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;


class BannerController extends Controller
{
    public function index(): JsonResponse
    {
       try {
        $page = request()->get('page', 1); // Default page is 1
        $perPage = request()->get('per_page', 10); // Default per_page is 10

        // Query with pagination and optional filtering by status
        $query = Banner::orderByDesc('id');
        if (request()->has('status')) {
            $query->where('status', request('status'));
        }
        if (request()->has('type')) {
            $query->where('type', request('type'));
         }
        $banners = $query->paginate($perPage, ['*'], 'page', $page);

            foreach ($banners as $banner) {
                $banner->image_url = Storage::disk('r2')->url($banner->image);
                // Đảm bảo trả về trường link
                $banner->link = $banner->link;
            }

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách banner thành công',
                'data' => $banners
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách banner',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4048',
                'status' => 'required|in:active,inactive',
                'type' => 'required|in:banner,popup', // validate type
                'link' => 'nullable|url', // validate link
            ], [
                'title.required' => 'Tiêu đề là bắt buộc.',
                'title.string' => 'Tiêu đề phải là chuỗi.',
                'title.max' => 'Tiêu đề tối đa 255 ký tự.',
                'image.required' => 'Ảnh là bắt buộc.',
                'image.image' => 'File phải là ảnh.',
                'image.mimes' => 'Ảnh phải có định dạng: jpeg, png, jpg, gif, svg, webp.',
                'image.max' => 'Ảnh tối đa 4MB.',
                'status.required' => 'Trạng thái là bắt buộc.',
                'status.in' => 'Trạng thái chỉ nhận giá trị active hoặc inactive.',
                'type.required' => 'Loại banner là bắt buộc.',
                'type.in' => 'Loại banner chỉ nhận giá trị banner hoặc popup.',
            ]);

            $imagePath = null;

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                [$width, $height] = getimagesize($file);
                // Chỉ kiểm tra kích thước nếu là banner thường
                // if ((isset($validated['type']) && $validated['type'] === 'banner' && ($width !== 1200 || $height !== 400))) {
                //     return response()->json([
                //         'success' => false,
                //         'message' => 'Ảnh banner phải có kích thước 1200x400 pixel.',
                //         'errors' => [
                //             'image' => ['Ảnh banner phải có kích thước 1200x400 pixel.']
                //         ]
                //     ], 422);
                // }
                $filename = 'banners/' . time() . '_' . $file->getClientOriginalName();
                Storage::disk('r2')->put($filename, file_get_contents($file));
                $imagePath = $filename;
            }

            $banner = Banner::create([
                'title' => $validated['title'],
                'image' => $imagePath,
                'status' => $validated['status'],
                'type' => $validated['type'], // lưu type
                'link' => $validated['link'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tạo banner thành công',
                'data' => $banner
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
                'message' => 'Lỗi khi tạo banner',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $banner = Banner::findOrFail($id);
            $banner->image_url = Storage::disk('r2')->url($banner->image);
            $banner->link = $banner->link;

            return response()->json([
                'success' => true,
                'message' => 'Lấy banner thành công',
                'data' => $banner
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy banner',
                'error' => $e->getMessage()
            ], 404);
        }
    }


    public function update(Request $request, $id): JsonResponse
    {
        try {
            $banner = Banner::findOrFail($id);

            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4048',
                'status' => 'sometimes|required|in:active,inactive',
                'type' => 'sometimes|required|in:banner,popup', // validate type
                'link' => 'nullable|url', // validate link
            ], [
                'title.required' => 'Tiêu đề là bắt buộc.',
                'title.string' => 'Tiêu đề phải là chuỗi.',
                'title.max' => 'Tiêu đề tối đa 255 ký tự.',
                'image.image' => 'File phải là ảnh.',
                'image.mimes' => 'Ảnh phải có định dạng: jpeg, png, jpg, gif, svg, webp.',
                'image.max' => 'Ảnh tối đa 4MB.',
                'status.required' => 'Trạng thái là bắt buộc.',
                'status.in' => 'Trạng thái chỉ nhận giá trị active hoặc inactive.',
                'type.required' => 'Loại banner là bắt buộc.',
                'type.in' => 'Loại banner chỉ nhận giá trị banner hoặc popup.',
            ]);

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                [$width, $height] = getimagesize($file);
                // Chỉ kiểm tra kích thước nếu là banner thường
                if ((isset($validated['type']) && $validated['type'] === 'banner' && ($width !== 1200 || $height !== 400))
                    || (!isset($validated['type']) && $banner->type === 'banner' && ($width !== 1200 || $height !== 400))) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ảnh banner phải có kích thước 1200x400 pixel.',
                        'errors' => [
                            'image' => ['Ảnh banner phải có kích thước 1200x400 pixel.']
                        ]
                    ], 422);
                }
                $filename = 'banners/' . time() . '_' . $file->getClientOriginalName();
                Storage::disk('r2')->put($filename, file_get_contents($file));
                $validated['image'] = $filename;
            }

            $banner->update($validated);
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật banner thành công',
                'data' => tap($banner->fresh(), function ($b) {
                    $b->image_url = Storage::disk('r2')->url($b->image);
                    $b->link = $b->link;
                })
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
                'message' => 'Lỗi khi cập nhật banner',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $banner = Banner::findOrFail($id);
            $banner->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa banner thành công',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lôi khi xóa banner',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getActiveBanners(): JsonResponse
    {
        $banners = Banner::where('status', 'active')->orderByDesc('id')->get();
        foreach ($banners as $banner) {
            $banner->image_url = Storage::disk('r2')->url($banner->image);
        }
        return response()->json([
            'success' => true,
            'data' => $banners
        ]);
    }
    // Thêm API lấy popup active
    public function getActivePopups(): JsonResponse
    {
        $popups = Banner::where('status', 'active')->where('type', 'popup')->orderByDesc('id')->get();
        foreach ($popups as $popup) {
            $popup->image_url = Storage::disk('r2')->url($popup->image);
        }
        return response()->json([
            'success' => true,
            'data' => $popups
        ]);
    }
}