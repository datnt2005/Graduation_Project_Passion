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
use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(Request $request)
{
    try {
        $search = $request->input('search');
        $sortDirection = in_array($request->input('sort_direction', 'asc'), ['asc', 'desc']) ? $request->input('sort_direction') : 'asc';

        $products = Product::when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%");
            })
            ->with(['categories', 'productVariants', 'productPic'])
            ->orderBy('created_at', $sortDirection)
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách sản phẩm thành công.',
            'data' => $products
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lấy danh sách sản phẩm thất bại: ' . $e->getMessage(),
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:products,name',
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
            'variants.*.quantity' => 'required|integer|min:0',
            'variants.*.attributes' => 'required|array',
            'variants.*.attributes.*.attribute_id' => 'required|exists:attributes,id',
            'variants.*.attributes.*.value_id' => 'required|exists:attribute_values,id',
            'variants.*.thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4048',
            'image.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4048',
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
            'variants.*.price.required' => 'Giá sản phẩm là bắt buộc.',
            'variants.*.price.numeric' => 'Giá phải là số.',
            'variants.*.price.min' => 'Giá không được nhỏ hơn 0.',
            'variants.*.sale_price.numeric' => 'Giá khuyến mãi phải là số.',
            'variants.*.sale_price.min' => 'Giá khuyến mãi không được nhỏ hơn 0.',
            'variants.*.cost_price.required' => 'Giá vốn là bắt buộc.',
            'variants.*.cost_price.numeric' => 'Giá vốn phải là số.',
            'variants.*.cost_price.min' => 'Giá vốn không được nhỏ hơn 0.',
            'variants.*.quantity.required' => 'Số lượng là bắt buộc.',
            'variants.*.quantity.integer' => 'Số lượng phải là số nguyên.',
            'variants.*.quantity.min' => 'Số lượng không được nhỏ hơn 0.',
            'variants.*.attributes.required' => 'Thuộc tính biến thể là bắt buộc.',
            'variants.*.attributes.*.attribute_id.required' => 'ID thuộc tính là bắt buộc.',
            'variants.*.attributes.*.attribute_id.exists' => 'Thuộc tính không hợp lệ.',
            'variants.*.attributes.*.value_id.required' => 'ID giá trị thuộc tính là bắt buộc.',
            'variants.*.attributes.*.value_id.exists' => 'Giá trị thuộc tính không hợp lệ.',
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

        DB::beginTransaction();
        try {
            // Tạo slug nếu không được cung cấp
            $slug = $request->slug ?? Str::slug($request->name);

            // Tạo sản phẩm
            $product = Product::create([
                "seller_id" => 1,
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'status' => $request->status ?? 'active',
            ]);

            // Gán danh mục
            $product->categories()->sync($request->categories);

            // Gán tags nếu có
            if ($request->has('tags')) {
                $product->tags()->sync($request->tags);
            }

            // Tạo biến thể
            foreach ($request->variants as $index => $variant) {
                $sku = $this->generateSKU($request->name, $request->categories[0], $variant['attributes'], $product->id);

                $variantImagePath = null;
                if ($request->hasFile("variants.$index.thumbnail")) {
                    $variantImagePath = $request->file("variants.$index.thumbnail")->store('products/variants', 'minio');
                }

                $productVariant = $product->productVariants()->create([
                    'sku' => $sku,
                    'price' => $variant['price'],
                    'sale_price' => $variant['sale_price'] ?? null,
                    'cost_price' => $variant['cost_price'],
                    'quantity' => $variant['quantity'],
                    'thumbnail' => $variantImagePath,
                ]);

                foreach ($variant['attributes'] as $attribute) {
                    $productVariant->attributes()->create([
                        'attribute_id' => $attribute['attribute_id'],
                        'value_id' => $attribute['value_id'],
                    ]);
                }
            }

            // Upload ảnh sản phẩm
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
                'data' => $product->load('categories', 'productVariants', 'productPic')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Tạo sản phẩm thất bại: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv',
        ], [
            'file.required' => 'Tệp là bắt buộc.',
            'file.mimes' => 'Tệp phải có định dạng xlsx, xls hoặc csv.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            Excel::import(new ProductsImport, $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'Nhập sản phẩm từ tệp thành công.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Nhập sản phẩm thất bại: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $product = Product::with(['categories', 'productVariants.attributes', 'productPic'])->find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy sản phẩm.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lấy thông tin sản phẩm thành công.',
                'data' => $product
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lấy thông tin sản phẩm thất bại: ' . $e->getMessage(),
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
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.sale_price' => 'nullable|numeric|min:0',
            'variants.*.cost_price' => 'required|numeric|min:0',
            'variants.*.quantity' => 'required|integer|min:0',
            'variants.*.attributes' => 'required|array',
            'variants.*.attributes.*.attribute_id' => 'required|exists:attributes,id',
            'variants.*.attributes.*.value_id' => 'required|exists:attribute_values,id',
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
            'variants.*.price.required' => 'Giá sản phẩm là bắt buộc.',
            'variants.*.price.numeric' => 'Giá phải là số.',
            'variants.*.price.min' => 'Giá không được nhỏ hơn 0.',
            'variants.*.sale_price.numeric' => 'Giá khuyến mãi phải là số.',
            'variants.*.sale_price.min' => 'Giá khuyến mãi không được nhỏ hơn 0.',
            'variants.*.cost_price.required' => 'Giá vốn là bắt buộc.',
            'variants.*.cost_price.numeric' => 'Giá vốn phải là số.',
            'variants.*.cost_price.min' => 'Giá vốn không được nhỏ hơn 0.',
            'variants.*.quantity.required' => 'Số lượng là bắt buộc.',
            'variants.*.quantity.integer' => 'Số lượng phải là số nguyên.',
            'variants.*.quantity.min' => 'Số lượng không được nhỏ hơn 0.',
            'variants.*.attributes.required' => 'Thuộc tính biến thể là bắt buộc.',
            'variants.*.attributes.*.attribute_id.required' => 'ID thuộc tính là bắt buộc.',
            'variants.*.attributes.*.attribute_id.exists' => 'Thuộc tính không hợp lệ.',
            'variants.*.attributes.*.value_id.required' => 'ID giá trị thuộc tính là bắt buộc.',
            'variants.*.attributes.*.value_id.exists' => 'Giá trị thuộc tính không hợp lệ.',
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

            if ($request->has('removed_images')) {
                foreach ($request->removed_images as $imageId) {
                    $image = ProductPic::find($imageId);
                    if ($image) {
                        Storage::disk('minio')->delete($image->imagePath);
                        $image->delete();
                    }
                }
            }

            if ($request->hasFile('image')) {
                foreach ($request->file('image') as $image) {
                    $path = $image->store('products', 'minio');
                    $product->productPic()->create(['imagePath' => $path]);
                }
            }

            $variantIdsFromRequest = collect($request->variants)->pluck('id')->filter()->all();
            $product->productVariants()->whereNotIn('id', $variantIdsFromRequest)->delete();

            foreach ($request->variants as $index => $variant) {
                $sku = $variant['sku'] ?? $this->generateSKU($request->name, $request->categories[0], $variant['attributes'], $product->id);

                $variantData = [
                    'price' => $variant['price'],
                    'sale_price' => $variant['sale_price'] ?? null,
                    'cost_price' => $variant['cost_price'],
                    'quantity' => $variant['quantity'],
                ];

                $variantImagePath = null;
                if ($request->hasFile("variants.$index.thumbnail")) {
                    $variantImagePath = $request->file("variants.$index.thumbnail")->store('products/variants', 'minio');
                    $variantData['thumbnail'] = $variantImagePath;
                }

                if (isset($variant['id']) && $variant['id']) {
                    $productVariant = ProductVariant::find($variant['id']);
                    if ($productVariant) {
                        if (ProductVariant::where('sku', $sku)->where('id', '!=', $productVariant->id)->exists()) {
                            $sku = $this->makeUniqueSKU($sku);
                        }
                        $variantData['sku'] = $sku;

                        if (!$variantImagePath && $productVariant->thumbnail) {
                            $variantData['thumbnail'] = $productVariant->thumbnail;
                        }

                        $productVariant->update($variantData);
                        $productVariant->attributes()->delete();
                        foreach ($variant['attributes'] as $attribute) {
                            $productVariant->attributes()->create([
                                'attribute_id' => $attribute['attribute_id'],
                                'value_id' => $attribute['value_id'],
                            ]);
                        }
                    }
                } else {
                    if (ProductVariant::where('sku', $sku)->exists()) {
                        $sku = $this->makeUniqueSKU($sku);
                    }
                    $variantData['sku'] = $sku;

                    $productVariant = $product->productVariants()->create($variantData);
                    foreach ($variant['attributes'] as $attribute) {
                        $productVariant->attributes()->create([
                            'attribute_id' => $attribute['attribute_id'],
                            'value_id' => $attribute['value_id'],
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật sản phẩm thành công.',
                'data' => $product->load('categories', 'productVariants.attributes', 'productPic')
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
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

    private function generateSKU($productName, $categoryId, $attributes, $productId)
    {
        $category = Category::find($categoryId);
        $categorySlug = $category ? Str::slug($category->name) : 'uncategorized';

        $attributeNames = collect($attributes)->pluck('value_id')->implode('-');

        $sku = Str::slug($productName . '-' . $categorySlug . '-' . $attributeNames . '-' . $productId);

        $counter = 1;
        while (ProductVariant::where('sku', $sku)->exists()) {
            $sku = Str::slug($productName . '-' . $categorySlug . '-' . $attributeNames . '-' . $productId . '-' . $counter);
            $counter++;
        }

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