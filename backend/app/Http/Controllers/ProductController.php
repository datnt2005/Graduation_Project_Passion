<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductPic;
use App\Models\VariantAttribute;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Tag;
use App\Models\Inventory;
use App\Http\Requests\ProductRequest;
use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Log;

class ProductController extends Controller
{
    //     public function index(Request $request)
    // {
    //     try {
    //         $search = $request->input('search');
    //         $sortDirection = in_array($request->input('sort_direction', 'asc'), ['asc', 'desc']) ? $request->input('sort_direction') : 'asc';

    //         $products = Product::when($search, function ($query, $search) {
    //                 return $query->where('name', 'like', "%{$search}%");
    //             })
    //             ->with(['categories', 'productVariants', 'productPic'])
    //             ->orderBy('created_at', $sortDirection)
    //             ->paginate(10);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Lấy danh sách sản phẩm thành công.',
    //             'data' => $products
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Lấy danh sách sản phẩm thất bại: ' . $e->getMessage(),
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function index(Request $request)
    {
        $products = Product::with(['categories', 'productVariants', 'productPic', 'tags', 'productVariants.inventories', 'productVariants.attributes'])
            ->when($request->has('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách sản phẩm thành công.',
            'data' => $products
        ], 200);
    }

public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'slug' => 'nullable|string|max:255|unique:products,slug',
        'description' => 'required|string',
        'status' => 'nullable|in:active,inactive',
        'categories' => 'required|array',
        'categories.*' => 'exists:categories,id',
        'tags' => 'nullable|array',
        'tags.*' => 'exists:tags,id',
        'variants' => 'required|array',
        'variants.*.price' => 'required|numeric|min:0',
        'variants.*.sale_price' => 'nullable|numeric|min:0',
        'variants.*.cost_price' => 'required|numeric|min:0',
        'variants.*.attributes' => 'required|array|min:1',
        'variants.*.attributes.*.attribute_id' => 'required|exists:attributes,id',
        'variants.*.attributes.*.value_id' => 'required|exists:attribute_values,id',
        'variants.*.inventory' => 'required|array|min:1',
        'variants.*.inventory.*.quantity' => 'required|integer|min:0',
        'variants.*.inventory.*.location' => 'nullable|string|max:255',
        'variants.*.thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4048',
        'image.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4048',
    ], [
        'name.required' => 'Tên sản phẩm là bắt buộc.',
        'name.max' => 'Tên sản phẩm không được vượt quá 255 ký tự.',
        'slug.unique' => 'Slug đã tồn tại.',
        'description.required' => 'Mô tả sản phẩm là bắt buộc.',
        'categories.required' => 'Danh mục là bắt buộc.',
        'categories.*.exists' => 'Danh mục không hợp lệ.',
        'tags.*.exists' => 'Thẻ không hợp lệ.',
        'variants.required' => 'Phải có ít nhất một biến thể.',
        'variants.*.price.required' => 'Giá sản phẩm là bắt buộc.',
        'variants.*.price.numeric' => 'Giá phải là số.',
        'variants.*.price.min' => 'Giá không được nhỏ hơn 0.',
        'variants.*.sale_price.numeric' => 'Giá khuyến mãi phải là số.',
        'variants.*.sale_price.min' => 'Giá khuyến mãi không được nhỏ hơn 0.',
        'variants.*.cost_price.required' => 'Giá vốn là bắt buộc.',
        'variants.*.cost_price.numeric' => 'Giá vốn phải là số.',
        'variants.*.cost_price.min' => 'Giá vốn không được nhỏ hơn 0.',
        'variants.*.attributes.required' => 'Thuộc tính biến thể là bắt buộc.',
        'variants.*.attributes.*.attribute_id.required' => 'ID thuộc tính là bắt buộc.',
        'variants.*.attributes.*.attribute_id.exists' => 'Thuộc tính không hợp lệ.',
        'variants.*.attributes.*.value_id.required' => 'ID giá trị thuộc tính là bắt buộc.',
        'variants.*.attributes.*.value_id.exists' => 'Giá trị thuộc tính không hợp lệ.',
        'variants.*.inventory.required' => 'Phải có ít nhất một bản ghi kho.',
        'variants.*.inventory.*.quantity.required' => 'Số lượng kho là bắt buộc.',
        'variants.*.inventory.*.quantity.integer' => 'Số lượng kho phải là số nguyên.',
        'variants.*.inventory.*.quantity.min' => 'Số lượng kho không được nhỏ hơn 0.',
        'variants.*.inventory.*.location.max' => 'Vị trí kho không được vượt quá 255 ký tự.',
        'variants.*.thumbnail' => 'Hình ảnh thumbnail không hợp lệ.',
        'image.*' => 'Hình ảnh sản phẩm không hợp lệ.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ.',
            'errors' => $validator->errors()
        ], 422);
    }

    // Kiểm tra thuộc tính trùng nhau
    $attributeSets = collect($request->variants)->map(function ($variant, $index) {
        return [
            'index' => $index,
            'attributes' => collect($variant['attributes'])->sortBy('attribute_id')->map(function ($attr) {
                return $attr['attribute_id'] . ':' . $attr['value_id'];
            })->implode(',')
        ];
    });

    $duplicates = $attributeSets->groupBy('attributes')->filter(function ($group) {
        return $group->count() > 1;
    });

    if ($duplicates->isNotEmpty()) {
        $duplicateIndices = $duplicates->flatten(1)->pluck('index')->implode(', ');
        return response()->json([
            'success' => false,
            'message' => "Các biến thể tại vị trí $duplicateIndices có thuộc tính trùng nhau.",
        ], 422);
    }

    DB::beginTransaction();

    try {
        $slug = $request->slug ?? Str::slug($request->name);
        if (Product::where('slug', $slug)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Slug đã tồn tại.',
            ], 422);
        }

        $product = Product::create([
            'seller_id' => auth()->id() ?? 1,
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'status' => $request->status ?? 'active',
        ]);

        $product->categories()->sync($request->categories);

        if ($request->has('tags')) {
            $product->tags()->sync($request->tags);
        }

        foreach ($request->variants as $index => $variant) {
            $sku = $this->generateSKU($request->name, $request->categories[0], $variant['attributes'], $product->id);

            $thumbnailPath = null;
            if ($request->hasFile("variants.$index.thumbnail")) {
                $thumbnailPath = $request->file("variants.$index.thumbnail")->store('products/variants', 'minio');
            }

            // Tính tổng số lượng từ tất cả kho
            $totalQuantity = collect($variant['inventory'])->sum('quantity');

            $productVariant = $product->productVariants()->create([
                'sku' => $sku,
                'price' => $variant['price'],
                'sale_price' => $variant['sale_price'] ?? null,
                'cost_price' => $variant['cost_price'],
                'quantity' => $totalQuantity,
                'thumbnail' => $thumbnailPath,
            ]);

            // Tạo inventory hàng loạt
            $inventoryData = collect($variant['inventory'])->map(function ($inventory) use ($productVariant) {
                return [
                    'product_variant_id' => $productVariant->id,
                    'quantity' => $inventory['quantity'],
                    'location' => $inventory['location'] ?? null,
                    'last_updated' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            Inventory::insert($inventoryData);

            // Gán attributes
            $attributes = collect($variant['attributes'])->mapWithKeys(function ($attribute) {
                if (!Attribute::find($attribute['attribute_id']) || !AttributeValue::find($attribute['value_id'])) {
                    throw new \Exception('Invalid attribute_id or value_id');
                }
                return [$attribute['attribute_id'] => ['value_id' => $attribute['value_id']]];
            })->toArray();
            $productVariant->attributes()->attach($attributes);
        }

        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $image) {
                $path = $image->store('products', 'minio');
                $product->productPic()->create([
                    'imagePath' => $path,
                ]);
            }
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Tạo sản phẩm thành công.',
            'data' => $product->load(
                'categories',
                'productVariants.attributes',
                'productVariants.inventories',
                'productPic',
                'tags'
            )
        ], 201);
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Tạo sản phẩm thất bại: ' . $e->getMessage(), ['exception' => $e]);
        return response()->json([
            'success' => false,
            'message' => 'Tạo sản phẩm thất bại: ' . $e->getMessage(),
            'error' => $e->getMessage()
        ], 500);
    }
}
public function update(Request $request, $id)
{
    $product = Product::find($id);
    if (!$product) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy sản phẩm.',
        ], 404);
    }

    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255|unique:products,name,' . $id,
        'slug' => 'nullable|string|max:255|unique:products,slug,' . $id,
        'description' => 'required|string',
        'status' => 'nullable|in:active,inactive',
        'categories' => 'required|array',
        'categories.*' => 'exists:categories,id',
        'tags' => 'nullable|array',
        'tags.*' => 'exists:tags,id',
        'variants' => 'required|array',
        'variants.*.id' => 'nullable|exists:product_variants,id',
        'variants.*.price' => 'required|numeric|min:0',
        'variants.*.sale_price' => 'nullable|numeric|min:0',
        'variants.*.cost_price' => 'required|numeric|min:0',
        'variants.*.attributes' => 'required|array|min:1',
        'variants.*.attributes.*.attribute_id' => 'required|exists:attributes,id',
        'variants.*.attributes.*.value_id' => 'required|exists:attribute_values,id',
        'variants.*.inventory' => 'required|array|min:1',
        'variants.*.inventory.*.quantity' => 'required|integer|min:0',
        'variants.*.inventory.*.location' => 'nullable|string|max:255',
        'variants.*.thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4048',
        'image.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4048',
        'removed_images' => 'nullable|array',
        'removed_images.*' => 'exists:product_pics,id',
    ], [
        'name.required' => 'Tên sản phẩm là bắt buộc.',
        'name.max' => 'Tên sản phẩm không được vượt quá 255 ký tự.',
        'name.unique' => 'Tên sản phẩm đã tồn tại.',
        'slug.unique' => 'Slug đã tồn tại.',
        'description.required' => 'Mô tả sản phẩm là bắt buộc.',
        'categories.required' => 'Danh mục là bắt buộc.',
        'categories.*.exists' => 'Danh mục không hợp lệ.',
        'tags.*.exists' => 'Thẻ không hợp lệ.',
        'variants.required' => 'Phải có ít nhất một biến thể.',
        'variants.*.id.exists' => 'ID biến thể không hợp lệ.',
        'variants.*.price.required' => 'Giá sản phẩm là bắt buộc.',
        'variants.*.price.numeric' => 'Giá phải là số.',
        'variants.*.price.min' => 'Giá không được nhỏ hơn 0.',
        'variants.*.sale_price.numeric' => 'Giá khuyến mãi phải là số.',
        'variants.*.sale_price.min' => 'Giá khuyến mãi không được nhỏ hơn 0.',
        'variants.*.cost_price.required' => 'Giá vốn là bắt buộc.',
        'variants.*.cost_price.numeric' => 'Giá vốn phải là số.',
        'variants.*.cost_price.min' => 'Giá vốn không được nhỏ hơn 0.',
        'variants.*.attributes.required' => 'Thuộc tính biến thể là bắt buộc.',
        'variants.*.attributes.*.attribute_id.required' => 'ID thuộc tính là bắt buộc.',
        'variants.*.attributes.*.attribute_id.exists' => 'Thuộc tính không hợp lệ.',
        'variants.*.attributes.*.value_id.required' => 'ID giá trị thuộc tính là bắt buộc.',
        'variants.*.attributes.*.value_id.exists' => 'Giá trị thuộc tính không hợp lệ.',
        'variants.*.inventory.required' => 'Phải có ít nhất một bản ghi kho.',
        'variants.*.inventory.*.quantity.required' => 'Số lượng kho là bắt buộc.',
        'variants.*.inventory.*.quantity.integer' => 'Số lượng kho phải là số nguyên.',
        'variants.*.inventory.*.quantity.min' => 'Số lượng kho không được nhỏ hơn 0.',
        'variants.*.inventory.*.location.max' => 'Vị trí kho không được vượt quá 255 ký tự.',
        'variants.*.thumbnail' => 'Hình ảnh thumbnail không hợp lệ.',
        'image.*' => 'Hình ảnh sản phẩm không hợp lệ.',
        'removed_images.*.exists' => 'Hình ảnh cần xóa không hợp lệ.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ.',
            'errors' => $validator->errors()
        ], 422);
    }

    // Kiểm tra thuộc tính trùng nhau
    $attributeSets = collect($request->variants)->map(function ($variant, $index) {
        return [
            'index' => $index,
            'attributes' => collect($variant['attributes'])->sortBy('attribute_id')->map(function ($attr) {
                return $attr['attribute_id'] . ':' . $attr['value_id'];
            })->implode(',')
        ];
    });

    $duplicates = $attributeSets->groupBy('attributes')->filter(function ($group) {
        return $group->count() > 1;
    });

    if ($duplicates->isNotEmpty()) {
        $duplicateIndices = $duplicates->flatten(1)->pluck('index')->implode(', ');
        return response()->json([
            'success' => false,
            'message' => "Các biến thể tại vị trí $duplicateIndices có thuộc tính trùng nhau.",
        ], 422);
    }

    DB::beginTransaction();
    try {
        $slug = $request->slug ?? Str::slug($request->name);

        $product->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'status' => $request->status ?? 'active',
        ]);

        $product->categories()->sync($request->categories);

        if ($request->has('tags')) {
            $product->tags()->sync($request->tags);
        }

        // Xóa hình ảnh cũ nếu có
        if ($request->has('removed_images')) {
            foreach ($request->removed_images as $imageId) {
                $image = ProductPic::find($imageId);
                if ($image) {
                    Storage::disk('minio')->delete($image->imagePath);
                    $image->delete();
                }
            }
        }

        // Thêm hình ảnh mới
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $image) {
                $path = $image->store('products', 'minio');
                $product->productPic()->create(['imagePath' => $path]);
            }
        }

        // Xử lý variants
        $variantIdsFromRequest = collect($request->variants)->pluck('id')->filter()->all();
        $product->productVariants()->whereNotIn('id', $variantIdsFromRequest)->delete();

        foreach ($request->variants as $index => $variant) {
            $sku = $this->generateSKU($request->name, $request->categories[0], $variant['attributes'], $product->id);

            $thumbnailPath = null;
            if ($request->hasFile("variants.$index.thumbnail")) {
                $thumbnailPath = $request->file("variants.$index.thumbnail")->store('products/variants', 'minio');
            }

            // Tính tổng số lượng từ inventory
            $totalQuantity = collect($variant['inventory'])->sum('quantity');

            // Cập nhật hoặc tạo biến thể
            $productVariant = isset($variant['id']) && $product->productVariants()->find($variant['id'])
                ? $product->productVariants()->find($variant['id'])
                : $product->productVariants()->create([
                    'sku' => $sku,
                    'price' => $variant['price'],
                    'sale_price' => $variant['sale_price'] ?? null,
                    'cost_price' => $variant['cost_price'],
                    'quantity' => $totalQuantity,
                    'thumbnail' => $thumbnailPath,
                ]);

            if (isset($variant['id'])) {
                $productVariant->update([
                    'sku' => $sku,
                    'price' => $variant['price'],
                    'sale_price' => $variant['sale_price'] ?? null,
                    'cost_price' => $variant['cost_price'],
                    'quantity' => $totalQuantity,
                    'thumbnail' => $thumbnailPath ?: $productVariant->thumbnail,
                ]);
            }

            // Xóa inventory cũ và thêm inventory mới
            $productVariant->inventories()->delete();
            $inventoryData = collect($variant['inventory'])->map(function ($inventory) use ($productVariant) {
                return [
                    'product_variant_id' => $productVariant->id,
                    'quantity' => $inventory['quantity'],
                    'location' => $inventory['location'] ?? null,
                    'last_updated' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            Inventory::insert($inventoryData);

            // Xóa attributes cũ và gán attributes mới
            $productVariant->attributes()->detach();
            $attributes = collect($variant['attributes'])->mapWithKeys(function ($attribute) {
                if (!Attribute::find($attribute['attribute_id']) || !AttributeValue::find($attribute['value_id'])) {
                    throw new \Exception('Invalid attribute_id or value_id');
                }
                return [$attribute['attribute_id'] => ['value_id' => $attribute['value_id']]];
            })->toArray();
            $productVariant->attributes()->attach($attributes);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật sản phẩm thành công.',
            'data' => $product->load('categories', 'productVariants.attributes', 'productVariants.inventories', 'productPic', 'tags')
        ], 200);
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Cập nhật sản phẩm thất bại: ' . $e->getMessage(), ['exception' => $e]);
        return response()->json([
            'success' => false,
            'message' => 'Cập nhật sản phẩm thất bại: ' . $e->getMessage(),
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sản phẩm.',
            ], 404);
        }

        DB::beginTransaction();
        try {
            // Xóa ảnh sản phẩm
            foreach ($product->productPic as $pic) {
                Storage::disk('minio')->delete($pic->imagePath);
                $pic->delete();
            }

            // Xóa ảnh biến thể
            foreach ($product->productVariants as $variant) {
                if ($variant->thumbnail) {
                    Storage::disk('minio')->delete($variant->thumbnail);
                }
            }

            $product->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Xóa sản phẩm thành công.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Xóa sản phẩm thất bại: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function generateSKU($productName, $categories, $attributes, $productId = null)
    {
        // Lấy category đầu tiên
        $categoryId = is_array($categories) && isset($categories[0]['category_id']) ? $categories[0]['category_id'] : null;
        $category = $categoryId ? Category::find($categoryId) : null;
        $categorySlug = $category ? Str::slug($category->name) : null;

        // Lấy value_id từ attributes
        $attributePart = collect($attributes)->pluck('value_id')->implode('-');

        // Rút gọn tên sản phẩm: lấy 3 từ đầu tiên
        $nameWords = explode(' ', trim($productName));
        $shortName = implode('-', array_slice($nameWords, 0, 3));
        $shortName = Str::slug($shortName); // Ví dụ: "giay-da-bong"

        // Gộp thành SKU: name + category (nếu có) + attribute
        $parts = [$shortName];

        if ($categorySlug) {
            $parts[] = $categorySlug;
        }

        if (!empty($attributePart)) {
            $parts[] = $attributePart;
        }

        $sku = implode('-', $parts);

        // Nếu trùng thì thêm hậu tố đếm
        $sku = $this->makeUniqueSKU($sku);

        return $sku;
    }

    private function makeUniqueSKU($sku)
    {
        $originalSKU = $sku;
        $counter = 1;
        while (ProductVariant::where('sku', $sku)->exists()) {
            $sku = $originalSKU . '-' . $counter;
            $counter++;
        }
        return $sku;
    }
}