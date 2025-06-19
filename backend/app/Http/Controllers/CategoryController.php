<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        try {
            $categories = Cache::store('redis')->tags(['categories'])->remember('categories', 3600, function () {
                return Category::with('parent')->get()->toArray();
            });

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách danh mục thành công.',
                'categories' => $categories,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách danh mục: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách danh mục.',
                'error' => env('APP_DEBUG', false) ? $e->getMessage() : null
            ], 500);
        }
    }

    public function show($id)
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh mục ID ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy thông tin danh mục.',
                'error' => env('APP_DEBUG', false) ? $e->getMessage() : null
            ], 500);
        }
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

        try {
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

            $this->clearCategoryCache();

            return response()->json([
                'success' => true,
                'message' => 'Tạo danh mục thành công.',
                'data' => $category,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo danh mục: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Tạo danh mục thất bại.',
                'error' => env('APP_DEBUG', false) ? $e->getMessage() : null
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
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

                // Delete old image if exists
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

            $this->clearCategoryCache();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật danh mục thành công.',
                'data' => $category,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật danh mục ID ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật danh mục thất bại.',
                'error' => env('APP_DEBUG', false) ? $e->getMessage() : null
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
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

            $imagePath = $category->image;
            if ($imagePath && Storage::disk('r2')->exists($imagePath)) {
                Storage::disk('r2')->delete($imagePath);
            }

            $category->delete();

            $this->clearCategoryCache();

            return response()->json([
                'success' => true,
                'message' => 'Xóa danh mục thành công.',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa danh mục ID ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Xóa danh mục thất bại.',
                'error' => env('APP_DEBUG', false) ? $e->getMessage() : null
            ], 500);
        }
    }

    public function children($id)
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh mục con ID ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách danh mục con.',
                'error' => env('APP_DEBUG', false) ? $e->getMessage() : null
            ], 500);
        }
    }

    public function parents($id)
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh mục cha ID ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách danh mục cha.',
                'error' => env('APP_DEBUG', false) ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Clear all category-related cache keys (tagged and non-tagged)
     */
    protected function clearCategoryCache()
    {
        try {
            // Clear tagged caches
            Cache::store('redis')->tags(['categories'])->flush();

            // Clear non-tagged cache keys with Laravel prefix
            $redis = Cache::store('redis')->getRedis();
            $prefix = config('cache.prefix', 'laravel_cache');
            $cursor = 0;
            do {
                $scan = $redis->scan($cursor, ['MATCH' => $prefix . 'categories', 'COUNT' => 100]);
                $cursor = $scan[0];
                foreach ($scan[1] as $key) {
                    // Remove the prefix from the key for Cache::forget
                    $cacheKey = str_replace($prefix, '', $key);
                    Cache::store('redis')->forget($cacheKey);
                }
            } while ($cursor != 0);

            Log::info('Đã xóa cache danh mục thành công.');
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa cache danh mục: ' . $e->getMessage());
        }
    }

    public function showAllCategoryParent()
    {
        try {
            $categories = Cache::store('redis')->tags(['categories'])->remember('categories:only_parents', 3600, function () {
                return Category::whereNull('parent_id')->get()->toArray();
            });

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách danh mục cha thành công.',
                'categories' => $categories,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách danh mục cha: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách danh mục cha.',
                'error' => env('APP_DEBUG', false) ? $e->getMessage() : null
            ], 500);
        }
    }

   public function getCategoryChildrenByParent($slug)
{
    try {
        $cacheKey = "category:tree_children_of:$slug";

        $tree = Cache::store('redis')->tags(['categories'])->remember($cacheKey, 3600, function () use ($slug) {
            $parent = Category::where('slug', $slug)->first();

            if (!$parent) {
                return null;
            }

            // Gọi đệ quy lấy toàn bộ cây con
            return $this->buildCategoryTree($parent->id);
        });

        if ($tree === null) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy danh mục cha với slug: ' . $slug,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách danh mục con thành công.',
            'categories' => $tree,
        ]);
    } catch (\Exception $e) {
        Log::error('Lỗi khi lấy cây danh mục: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Đã xảy ra lỗi khi lấy cây danh mục.',
            'error' => env('APP_DEBUG', false) ? $e->getMessage() : null,
        ], 500);
    }
}

/**
 * Đệ quy xây dựng cây danh mục con.
 */
private function buildCategoryTree($parentId)
{
    $children = Category::where('parent_id', $parentId)->get();

    return $children->map(function ($child) {
        return [
            'id' => $child->id,
            'name' => $child->name,
            'slug' => $child->slug,
            'image' => $child->image,
            'children' => $this->buildCategoryTree($child->id),
        ];
    });
}

}
