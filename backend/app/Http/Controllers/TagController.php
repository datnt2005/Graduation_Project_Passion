<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class TagController extends Controller
{
    // Lấy danh sách tag (API)
    public function index(Request $request)
    {
        try {
            // Use cache to store all tags
            $cacheKey = 'all_tags';

            $tags = Cache::store('redis')->tags(['tags'])->remember($cacheKey, 3600, function () {
                return Tag::all();
            });

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách thẻ thành công.',
                'data' => $tags
            ], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách thẻ: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách thẻ.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
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


    public function productsBySlug($slug)
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        $productIds = DB::table('product_tags')
            ->where('tag_id', $tag->id)
            ->pluck('product_id');

        $products = Product::whereIn('id', $productIds)
            ->where('status', 'active')
            ->where('admin_status', 'approved')
            ->whereHas('seller', function ($q) {
                $q->where('verification_status', 'verified');
            })
            ->with([
                'variants' => function ($q) {
                    $q->select('id', 'product_id', 'price', 'sale_price', 'quantity', 'thumbnail')
                        ->orderByRaw('CASE WHEN sale_price IS NOT NULL THEN 0 ELSE 1 END');
                },
                'categories:id,name',
                'tags:id,name',
            ])
            ->select('id', 'name', 'slug', 'created_at', 'status', 'admin_status')
            ->paginate(20);

        // Chuẩn hoá output
        $mapped = $products->map(function ($p) {
            $variant = $p->variants->first();
            return [
                'id'           => $p->id,
                'name'         => $p->name,
                'slug'         => $p->slug,
                'price'        => $variant->price ?? null,
                'sale_price'   => $variant->sale_price ?? null,
                'thumbnail'    => $p->productPic->first()->imagePath ?? $variant->thumbnail ?? 'products/default.png',
                'quantity'     => $variant->quantity ?? null,
                'created_at'   => $p->created_at,
                'status'       => $p->status,
                'admin_status' => $p->admin_status,
                'variants_count' => $p->variants->count(),
                'categories'   => $p->categories,
                'tags'         => $p->tags,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Lấy sản phẩm theo tag thành công.',
            'data' => [
                'tag' => $tag,
                'products' => $mapped,
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ]
        ]);
    }
}
