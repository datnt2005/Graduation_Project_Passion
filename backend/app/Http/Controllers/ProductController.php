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
use App\Models\Review;
use App\Models\Seller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Http\Requests\ProductRequest;
use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $search = trim($request->get('search', ''));
            $perPage = $request->get('per_page', 10);

            $cacheKey = 'products_page_' . $page . '_perpage_' . $perPage . '_search_' . md5($search);
            $ttl = 3600;

            // Use tags for cache
            $products = Cache::store('redis')->tags(['products'])->remember($cacheKey, $ttl, function () use ($search, $perPage) {
                return Product::with([
                    'seller',
                    'categories',
                    'productVariants',
                    'productVariants.inventories',
                    'productVariants.attributes',
                    'productPic',
                    'tags'
                ])
                    ->whereIn('status', ['active', 'inactive'])
                    ->when($search, function ($query) use ($search) {
                        return $query->where('name', 'like', '%' . $search . '%');
                    })
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage)
                    ->toArray();
            });

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách sản phẩm thành công.',
                'data' => $products
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách sản phẩm.',
                'error' => env('APP_DEBUG', false) ? $e->getMessage() : null
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa đăng nhập. Vui lòng đăng nhập để tiếp tục.',
                'code' => 'AUTH_REQUIRED'
            ], 401);
        }

        // Kiểm tra vai trò admin
        $isAdmin = $user->role === 'admin';

        // Lấy seller_id mặc định cho Seller
        $defaultSellerId = null;
        if (!$isAdmin) {
            $seller = Seller::where('user_id', $user->id)->first();
            $defaultSellerId = $seller ? $seller->id : null;
            if (!$defaultSellerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có thông tin người bán. Vui lòng liên hệ admin.',
                ], 422);
            }
            // Kiểm tra thêm để đảm bảo seller_id chỉ thuộc về user hiện tại
            if ($request->has('sellers') && $request->sellers != $defaultSellerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn chỉ được tạo sản phẩm cho chính mình.',
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug|regex:/^[a-zA-Z0-9-]+$/',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive,draft,trash',
            'sellers' => $isAdmin ? 'required|exists:sellers,id' : 'nullable',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'variants' => 'nullable|array',
            'variants.*.price' => 'required_with:variants|numeric|min:0',
            'variants.*.sale_price' => 'nullable|numeric|min:0',
            'variants.*.cost_price' => 'required_with:variants|numeric|min:0',
            'variants.*.attributes' => 'nullable|array',
            'variants.*.attributes.*.attribute_id' => 'required_with:variants.*.attributes|exists:attributes,id',
            'variants.*.attributes.*.value_id' => 'required_with:variants.*.attributes|exists:attribute_values,id',
            'variants.*.inventory' => 'nullable|array',
            'variants.*.inventory.*.quantity' => 'required_with:variants.*.inventory|integer|min:0',
            'variants.*.inventory.*.location' => 'nullable|string|max:255',
            'variants.*.thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4048',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:4048',
        ], [
            'name.required' => 'Tên sản phẩm là bắt buộc.',
            'name.max' => 'Tên sản phẩm không được vượt quá 255 ký tự.',
            'slug.unique' => 'Slug đã tồn tại.',
            'slug.regex' => 'Slug chỉ được chứa chữ cái, số và dấu gạch ngang, không được chứa khoảng trắng hoặc ký tự đặc biệt.',
            'slug.max' => 'Slug không được vượt quá 255 ký tự.',
            'sellers.required' => 'Vui lòng chọn người bán.',
            'sellers.exists' => 'Người bán không hợp lệ.',
            'categories.array' => 'Danh mục phải là một mảng.',
            'categories.*.exists' => 'Danh mục không hợp lệ.',
            'tags.array' => 'Thẻ phải là một mảng.',
            'tags.*.exists' => 'Thẻ không hợp lệ.',
            'variants.*.price.required_with' => 'Giá sản phẩm là bắt buộc khi có biến thể.',
            'variants.*.price.numeric' => 'Giá phải là số.',
            'variants.*.price.min' => 'Giá không được nhỏ hơn 0.',
            'variants.*.sale_price.numeric' => 'Giá khuyến mãi phải là số.',
            'variants.*.sale_price.min' => 'Giá khuyến mãi không được nhỏ hơn 0.',
            'variants.*.cost_price.required_with' => 'Giá vốn là bắt buộc khi có biến thể.',
            'variants.*.cost_price.numeric' => 'Giá vốn phải là số.',
            'variants.*.cost_price.min' => 'Giá vốn không được nhỏ hơn 0.',
            'variants.*.attributes.*.attribute_id.required_with' => 'ID thuộc tính là bắt buộc khi có thuộc tính.',
            'variants.*.attributes.*.attribute_id.exists' => 'Thuộc tính không hợp lệ.',
            'variants.*.attributes.*.value_id.required_with' => 'ID giá trị thuộc tính là bắt buộc khi có thuộc tính.',
            'variants.*.attributes.*.value_id.exists' => 'Giá trị thuộc tính không hợp lệ.',
            'variants.*.inventory.*.quantity.required_with' => 'Số lượng kho là bắt buộc khi có kho.',
            'variants.*.inventory.*.quantity.integer' => 'Số lượng kho phải là số nguyên.',
            'variants.*.inventory.*.quantity.min' => 'Số lượng kho không được nhỏ hơn 0.',
            'variants.*.inventory.*.location.max' => 'Vị trí kho không được vượt quá 255 ký tự.',
            'variants.*.thumbnail.image' => 'Thumbnail phải là hình ảnh.',
            'variants.*.thumbnail.mimes' => 'Thumbnail phải có định dạng jpeg, png, jpg, gif, svg hoặc webp.',
            'variants.*.thumbnail.max' => 'Thumbnail không được vượt quá 4MB.',
            'images.*' => 'Hình ảnh sản phẩm không hợp lệ.',
            'images.*.image' => 'Tệp phải là hình ảnh.',
            'images.*.mimes' => 'Hình ảnh phải có định dạng jpeg, png, jpg, gif, svg hoặc webp.',
            'images.*.max' => 'Hình ảnh không được vượt quá 4MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->has('variants') && !empty($request->variants)) {
            foreach ($request->variants as $index => $variant) {
                if (!empty($variant['attributes'])) {
                    $attributeIds = collect($variant['attributes'])
                        ->pluck('attribute_id')
                        ->filter()
                        ->toArray();
                    if (count($attributeIds) !== count(array_unique($attributeIds))) {
                        return response()->json([
                            'success' => false,
                            'message' => "Biến thể tại vị trí " . ($index + 1) . " có thuộc tính bị lặp (ví dụ: chọn 2 lần màu sắc).",
                        ], 422);
                    }
                }
            }
        }

        // Kiểm tra thuộc tính trùng nhau nếu có biến thể và thuộc tính
        if ($request->has('variants') && !empty($request->variants)) {
            $attributeSets = collect($request->variants)->map(function ($variant, $index) {
                if (!empty($variant['attributes'])) {
                    $validAttributes = collect($variant['attributes'])->filter(function ($attr) {
                        return !empty($attr['attribute_id']) && !empty($attr['value_id']);
                    });
                    if ($validAttributes->isNotEmpty()) {
                        return [
                            'index' => $index,
                            'attributes' => $validAttributes->sortBy('attribute_id')->map(function ($attr) {
                                return $attr['attribute_id'] . ':' . $attr['value_id'];
                            })->implode(',')
                        ];
                    }
                }
                return ['index' => $index, 'attributes' => ''];
            });

            $duplicates = $attributeSets->filter(function ($set) {
                return !empty($set['attributes']);
            })->groupBy('attributes')->filter(function ($group) {
                return $group->count() > 1;
            });

            if ($duplicates->isNotEmpty()) {
                $duplicateIndices = $duplicates->flatten(1)->pluck('index')->implode(', ');
                return response()->json([
                    'success' => false,
                    'message' => "Các biến thể tại vị trí $duplicateIndices có thuộc tính trùng nhau.",
                ], 422);
            }
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

            $sellerId = $isAdmin ? $request->sellers : $defaultSellerId;
            if (!$sellerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin người bán.',
                ], 422);
            }
            $isAdminAdded = $isAdmin && $request->has('sellers') && $request->sellers != Seller::where('user_id', $user->id)->value('id');
            $product = Product::create([
                'seller_id' => $sellerId,
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'status' => $request->status ?? 'active',
                'is_admin_added' => $isAdminAdded,
            ]);

            // Sync categories if provided, otherwise sync empty array
            $product->categories()->sync($request->categories ?? []);

            if ($request->has('tags')) {
                $product->tags()->sync($request->tags ?? []);
            }

            // Xử lý hình ảnh sản phẩm
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('products/images', 'r2');
                    $product->productPic()->create(['imagePath' => $path]);
                }
            }

            // Xử lý biến thể nếu có
            if ($request->has('variants') && !empty($request->variants)) {
                foreach ($request->variants as $index => $variant) {
                    $sku = $this->generateSKU($request->name, $request->categories[0] ?? null, $variant['attributes'] ?? [], $product->id);

                    // Xử lý single thumbnail
                    $thumbnailPath = null;
                    if ($request->hasFile("variants.$index.thumbnail")) {
                        $thumbnailPath = $request->file("variants.$index.thumbnail")->store('products/variants/thumbnails', 'r2');
                    }

                    // Tính tổng số lượng từ tất cả kho, mặc định là 0 nếu không có kho
                    $totalQuantity = collect($variant['inventory'] ?? [])->sum('quantity');

                    $productVariant = $product->productVariants()->create([
                        'sku' => $sku,
                        'price' => $variant['price'],
                        'sale_price' => $variant['sale_price'] ?? null,
                        'cost_price' => $variant['cost_price'],
                        'quantity' => $totalQuantity,
                        'thumbnail' => $thumbnailPath,
                    ]);

                    // Xử lý inventory nếu có
                    if (!empty($variant['inventory'])) {
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
                    }

                    // Xử lý attributes nếu có
                    if (!empty($variant['attributes'])) {
                        $validAttributes = collect($variant['attributes'])->filter(function ($attr) {
                            return !empty($attr['attribute_id']) && !empty($attr['value_id']);
                        });
                        if ($validAttributes->isNotEmpty()) {
                            $attributes = $validAttributes->mapWithKeys(function ($attribute) {
                                if (!Attribute::find($attribute['attribute_id']) || !AttributeValue::find($attribute['value_id'])) {
                                    throw new \Exception('Invalid attribute_id or value_id');
                                }
                                return [$attribute['attribute_id'] => ['value_id' => $attribute['value_id']]];
                            })->toArray();
                            $productVariant->attributes()->attach($attributes);
                        }
                    }
                }
            }

            DB::commit();
            $this->clearProductCache();
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
            \Log::error('Error creating product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Tạo sản phẩm thất bại: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa đăng nhập. Vui lòng đăng nhập để tiếp tục sử dụng.',
            ], 401);
        }

        $user = Auth::user();
        $isAdmin = $user->role === 'admin';
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sản phẩm.',
            ], 404);
        }

        // Kiểm tra quyền sở hữu sản phẩm
        if (!$isAdmin && $product->seller_id != Seller::where('user_id', $user->id)->value('id')) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền chỉnh sửa sản phẩm này.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:products,name,' . $id,
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $id . '|regex:/^[a-zA-Z0-9-]+$/',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'sellers' => $isAdmin ? 'nullable|exists:sellers,id' : 'prohibited', // Admin có thể thay đổi seller_id, Seller bị cấm
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|exists:product_variants,id',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.sale_price' => 'nullable|numeric|min:0',
            'variants.*.cost_price' => 'nullable|numeric|min:0',
            'variants.*.attributes' => 'nullable|array',
            'variants.*.attributes.*.attribute_id' => 'nullable|exists:attributes,id',
            'variants.*.attributes.*.value_id' => 'nullable|exists:attribute_values,id',
            'variants.*.inventory' => 'nullable|array',
            'variants.*.inventory.*.quantity' => 'nullable|integer|min:0',
            'variants.*.inventory.*.location' => 'nullable|string|max:255',
            'variants.*.thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4048',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:4048',
            'removed_images' => 'nullable|array',
            'removed_images.*' => 'integer|exists:product_pics,id,product_id,' . $id,
        ], [
            'name.required' => 'Tên sản phẩm là bắt buộc.',
            'name.max' => 'Tên sản phẩm không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên sản phẩm đã tồn tại.',
            'sellers.exists' => 'Không tìm thấy người bán.',
            'slug.unique' => 'Slug đã tồn tại.',
            'slug.regex' => 'Slug chỉ được chứa các ký tự chữ số, chữ cái và dấu gạch ngang.',
            'slug.max' => 'Slug không được vượt quá 255 ký tự.',
            'categories.array' => 'Danh mục phải là một mảng.',
            'categories.*.exists' => 'Danh mục không hợp lệ.',
            'tags.array' => 'Thẻ phải là một mảng.',
            'tags.*.exists' => 'Thẻ không hợp lệ.',
            'variants.*.price.numeric' => 'Giá phải là số.',
            'variants.*.price.min' => 'Giá không được nhỏ hơn 0.',
            'variants.*.sale_price.numeric' => 'Giá khuyến mãi phải là số.',
            'variants.*.sale_price.min' => 'Giá khuyến mãi không được nhỏ hơn 0.',
            'variants.*.cost_price.numeric' => 'Giá vốn phải là số.',
            'variants.*.cost_price.min' => 'Giá vốn không được nhỏ hơn 0.',
            'variants.*.attributes.*.attribute_id.exists' => 'Thuộc tính không hợp lệ.',
            'variants.*.attributes.*.value_id.exists' => 'Giá trị thuộc tính không hợp lệ.',
            'variants.*.inventory.*.quantity.integer' => 'Số lượng kho phải là số nguyên.',
            'variants.*.inventory.*.quantity.min' => 'Số lượng kho không được nhỏ hơn 0.',
            'variants.*.inventory.*.location.max' => 'Vị trí kho không được vượt quá 255 ký tự.',
            'variants.*.thumbnail.image' => 'Thumbnail phải là một hình ảnh.',
            'variants.*.thumbnail.mimes' => 'Thumbnail phải có định dạng jpeg, png, jpg, gif, svg hoặc webp.',
            'variants.*.thumbnail.max' => 'Thumbnail không được vượt quá 4MB.',
            'images.*' => 'Hình ảnh sản phẩm không hợp lệ.',
            'images.*.image' => 'Tệp phải là hình ảnh.',
            'images.*.mimes' => 'Hình ảnh sản phẩm phải có định dạng jpeg, png, jpg, gif, svg hoặc webp.',
            'images.*.max' => 'Hình ảnh sản phẩm không được vượt quá 4MB.',
            'removed_images' => 'Hình ảnh cần xóa không hợp lệ.',
            'removed_images.*.exists' => 'Hình ảnh cần xóa không tồn tại.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->has('variants') && !empty($request->variants)) {
            foreach ($request->variants as $index => $variant) {
                if (!empty($variant['attributes'])) {
                    $attributeIds = collect($variant['attributes'])
                        ->pluck('attribute_id')
                        ->filter()
                        ->toArray();
                    if (count($attributeIds) !== count(array_unique($attributeIds))) {
                        return response()->json([
                            'success' => false,
                            'message' => "Biến thể tại vị trí " . ($index + 1) . " có thuộc tính bị lặp (ví dụ: chọn 2 lần màu sắc).",
                        ], 422);
                    }
                }
            }
        }

        // Check for duplicate attributes only for variants with attributes
        if ($request->has('variants')) {
            $attributeSets = collect($request->variants)
                ->filter(function ($variant) {
                    return !empty($variant['attributes']);
                })
                ->map(function ($variant, $index) {
                    return [
                        'index' => $index,
                        'attributes' => collect($variant['attributes'])
                            ->filter(function ($attr) {
                                return !empty($attr['attribute_id']) && !empty($attr['value_id']);
                            })
                            ->sortBy('attribute_id')
                            ->map(function ($attr) {
                                return $attr['attribute_id'] . ':' . $attr['value_id'];
                            })->implode(',')
                    ];
                });

            $duplicates = $attributeSets->groupBy('attributes')->filter(function ($group) {
                return $group->count() > 1;
            });

            if ($duplicates->isNotEmpty()) {
                $duplicateIndices = $duplicates->flatten(1)->pluck('index')->map(function ($index) {
                    return $index + 1; // Adjust for 1-based indexing in error message
                })->implode(', ');
                return response()->json([
                    'success' => false,
                    'message' => "Các biến thể tại vị trí $duplicateIndices có thuộc tính trùng nhau.",
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            $slug = $request->slug ?? Str::slug($request->name);

            // Cập nhật seller_id chỉ khi là Admin và có seller_id mới
            $sellerId = $product->seller_id;
            if ($isAdmin && $request->has('sellers')) {
                $sellerId = $request->sellers;
            }

            $product->update([
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'status' => $request->status ?? 'active',
                'seller_id' => $sellerId,
            ]);

            // Sync categories
            $product->categories()->sync($request->categories ?? []);

            // Sync tags
            $product->tags()->sync($request->tags ?? []);

            // Remove old images
            if ($request->has('removed_images')) {
                foreach ($request->removed_images as $imageId) {
                    $image = ProductPic::where('id', $imageId)->where('product_id', $id)->first();
                    if ($image) {
                        Storage::disk('r2')->delete($image->imagePath);
                        $image->delete();
                    }
                }
            }

            // Add new product images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('products/images', 'r2');
                    $product->productPic()->create(['imagePath' => $path]);
                }
            }

            // Handle variants
            if ($request->has('variants')) {
                $variantIdsFromRequest = collect($request->variants)->pluck('id')->filter()->all();
                $product->productVariants()->whereNotIn('id', $variantIdsFromRequest)->delete();

                foreach ($request->variants as $index => $variant) {
                    // Skip invalid variants
                    if (!isset($variant['price']) || !isset($variant['cost_price'])) {
                        continue;
                    }

                    $sku = $this->generateSKU(
                        $request->name,
                        $request->categories[0] ?? null,
                        $variant['attributes'] ?? [],
                        $product->id
                    );

                    // Handle thumbnail
                    $thumbnailPath = null;
                    if ($request->hasFile("variants.$index.thumbnail")) {
                        if (isset($variant['id'])) {
                            $existingVariant = $product->productVariants()->find($variant['id']);
                            if ($existingVariant && $existingVariant->thumbnail) {
                                Storage::disk('r2')->delete($existingVariant->thumbnail);
                            }
                        }
                        $thumbnailPath = $request->file("variants.$index.thumbnail")->store('products/variants/thumbnails', 'r2');
                    } else {
                        if (isset($variant['id'])) {
                            $existingVariant = $product->productVariants()->find($variant['id']);
                            $thumbnailPath = $existingVariant ? $existingVariant->thumbnail : null;
                        }
                    }

                    // Calculate total quantity
                    $totalQuantity = collect($variant['inventory'] ?? [])->sum('quantity');

                    // Update or create variant
                    $variantData = [
                        'sku' => $sku,
                        'price' => $variant['price'],
                        'sale_price' => $variant['sale_price'] ?? null,
                        'cost_price' => $variant['cost_price'],
                        'quantity' => $totalQuantity,
                    ];
                    if ($thumbnailPath) {
                        $variantData['thumbnail'] = $thumbnailPath;
                    }

                    if (isset($variant['id']) && $existingVariant = $product->productVariants()->find($variant['id'])) {
                        $existingVariant->update($variantData);
                        $productVariant = $existingVariant;
                    } else {
                        $productVariant = $product->productVariants()->create($variantData);
                    }

                    // Handle inventory
                    if (!empty($variant['inventory'])) {
                        $productVariant->inventories()->delete();
                        $inventoryData = collect($variant['inventory'])
                            ->filter(function ($inventory) {
                                return isset($inventory['quantity']) && $inventory['quantity'] >= 0;
                            })
                            ->map(function ($inventory) use ($productVariant) {
                                return [
                                    'product_variant_id' => $productVariant->id,
                                    'quantity' => $inventory['quantity'],
                                    'location' => $inventory['location'] ?? null,
                                    'last_updated' => now(),
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            })->toArray();

                        if (!empty($inventoryData)) {
                            Inventory::insert($inventoryData);
                        }
                    }

                    // Handle attributes
                    $productVariant->attributes()->detach();
                    if (!empty($variant['attributes'])) {
                        $attributes = collect($variant['attributes'])
                            ->filter(function ($attribute) {
                                return !empty($attribute['attribute_id']) && !empty($attribute['value_id']);
                            })
                            ->mapWithKeys(function ($attribute) {
                                if (!Attribute::find($attribute['attribute_id']) || !AttributeValue::find($attribute['value_id'])) {
                                    throw new \Exception('Invalid attribute_id or value_id');
                                }
                                return [$attribute['attribute_id'] => ['value_id' => $attribute['value_id']]];
                            })->toArray();
                        if (!empty($attributes)) {
                            $productVariant->attributes()->attach($attributes);
                        }
                    }
                }
            } else {
                // If no variants provided, delete all existing variants
                $product->productVariants()->delete();
            }

            DB::commit();
            $this->clearProductCache();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật sản phẩm thành công.',
                'data' => $product->load('categories', 'productVariants.attributes', 'productVariants.inventories', 'productPic', 'tags')
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật sản phẩm thất bại: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa đăng nhập. Vui lòng đăng nhập để tiếp tục.',
            ], 401);
        }

        $user = Auth::user();
        $isAdmin = $user->role === 'admin';
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sản phẩm.',
            ], 404);
        }

        // Kiểm tra quyền sở hữu sản phẩm
        if (!$isAdmin) {
            $sellerId = Seller::where('user_id', $user->id)->value('id');
            if ($product->seller_id != $sellerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền xóa sản phẩm này.',
                ], 403);
            }
        }

        // Kiểm tra xem sản phẩm có liên kết với đơn hàng hay không
        $hasOrders = $product->productVariants()
            ->whereHas('orderItems')
            ->exists();

        if ($hasOrders) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa sản phẩm đang có đơn hàng liên kết.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Xóa hình ảnh sản phẩm
            foreach ($product->productPic as $pic) {
                Storage::disk('r2')->delete($pic->imagePath);
                $pic->delete();
            }

            // Xóa ảnh biến thể và các liên kết
            foreach ($product->productVariants as $variant) {
                if ($variant->thumbnail) {
                    Storage::disk('r2')->delete($variant->thumbnail);
                }
                $variant->inventories()->delete();
                $variant->attributes()->detach();
                $variant->delete();
            }

            $product->delete();

            DB::commit();
            $this->clearProductCache();
            return response()->json([
                'success' => true,
                'message' => 'Xóa sản phẩm thành công.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Xóa sản phẩm thất bại: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    protected function clearProductCache()
    {
        try {
            // Clear tagged caches
            Cache::store('redis')->tags(['products'])->flush();

            // Clear non-tagged cache keys with Laravel prefix
            $redis = Cache::store('redis')->getRedis();
            $prefix = config('cache.prefix', 'laravel_cache');
            $cursor = 0;
            do {
                $scan = $redis->scan($cursor, ['MATCH' => $prefix . 'products_page_*', 'COUNT' => 100]);
                $cursor = $scan[0];
                foreach ($scan[1] as $key) {
                    // Remove the prefix from the key for Cache::forget
                    $cacheKey = str_replace($prefix, '', $key);
                    Cache::store('redis')->forget($cacheKey);
                }
            } while ($cursor != 0);
        } catch (\Exception $e) {
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
    public function show($id)
    {
        $product = Product::with(['categories', 'productVariants', 'productVariants.inventories', 'productVariants.attributes',  'productPic', 'tags'])->find($id);

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
    }

    public function showBySlug($slug)
    {
        try {
            // Validate slug
            if (empty($slug) || !is_string($slug)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Slug không hợp lệ.',
                ], 400);
            }

            // Fetch product with relationships
            $product = Product::where('slug', $slug)
                ->where('status', 'active')
                ->with([
                    'seller.user',
                    'categories:id,name,slug',
                    'productVariants.inventories:id,product_variant_id,quantity',
                    'productVariants.attributes.values:id,attribute_id,value',
                    'productVariants.orderItems:id,product_variant_id,quantity',
                    'productPic:id,product_id,imagePath',
                    'tags:id,name,slug',
                    'reviews:id,product_id,rating',
                ])
                ->select('id', 'name', 'slug', 'description', 'seller_id', 'status')
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm không tồn tại hoặc không khả dụng.',
                ], 404);
            }

            // Calculate rating and sold count
            $rating = round($product->reviews->avg('rating') ?: 0, 2);
            $sold = $product->productVariants->flatMap(function ($variant) {
                return $variant->orderItems;
            })->sum('quantity');

            // Seller information
            $seller = $product->seller;
            $sellerProductsCount = $seller ? Product::where('seller_id', $seller->id)->where('status', 'active')->count() : 0;
            $sellerRating = $seller ? round(
                DB::table('reviews')
                    ->join('products', 'reviews.product_id', '=', 'products.id')
                    ->where('products.seller_id', $seller->id)
                    ->avg('reviews.rating') ?: 0,
                2
            ) : 0;

            // Mask phone number (e.g., 0901234567 -> 0901******)
            $phone = $seller && $seller->user->phone
                ? substr($seller->user->phone, 0, 4) . str_repeat('*', max(0, strlen($seller->user->phone) - 6))
                : 'N/A';

            // Format last active time (Vietnamese localization)
            $lastActive = $seller && $seller->last_active_at
                ? $seller->last_active_at->locale('vi')->diffForHumans()
                : 'Chưa hoạt động';

            // Product images
            $productImages = $product->productPic->map(fn($pic) => [
                'src' => $pic->imagePath,
                'alt' => $pic->alt_text ?? 'Hình ảnh sản phẩm',
            ])->values()->toArray();

            // Format variants
            $variants = $product->productVariants->map(function ($variant) {
                $price = $variant->price ?? 0.0;
                $salePrice = $variant->sale_price ?? null;
                $inventories = $variant->inventories ?? collect();
                return [
                    'id' => $variant->id,
                    'sku' => $variant->sku ?? 'N/A',
                    'price' => number_format($price, 2, '.', ''),
                    'sale_price' => $salePrice ? number_format($salePrice, 2, '.', '') : null,
                    'original_price' => number_format($price, 2, '.', ''),
                    'discount_percent' => ($salePrice && $price > 0)
                        ? round((($price - $salePrice) / $price) * 100)
                        : 0,
                    'thumbnail' => $variant->thumbnail ?? null,
                    'stock' => $inventories->sum('quantity') ?? 0,
                    'attributes' => $variant->attributes->map(function ($attr) {
                        $value = $attr->values->where('id', $attr->pivot->value_id)->first();
                        return [
                            'attribute_id' => $attr->pivot->attribute_id,
                            'attribute_name' => $attr->name ?? 'Không xác định',
                            'value_id' => $attr->pivot->value_id,
                            'value' => $value ? $value->value : 'N/A',
                        ];
                    })->values()->toArray(),
                ];
            })->values()->toArray();

            // Default variant for product-level pricing
            $defaultVariant = $product->productVariants->first();
            $defaultPrice = $defaultVariant?->price ?? 0.0;
            $defaultSalePrice = $defaultVariant?->sale_price ?? null;
            $defaultPercent = ($defaultSalePrice && $defaultPrice > 0)
                ? round((($defaultPrice - $defaultSalePrice) / $defaultPrice) * 100)
                : 0;

            // Related products
            $relatedProducts = Product::where('id', '!=', $product->id)
                ->where('status', 'active')
                ->whereHas('categories', function ($query) use ($product) {
                    $query->whereIn('id', $product->categories->pluck('id'));
                })
                ->with([
                    'productPic:id,product_id,imagePath',
                    'productVariants:id,product_id,price,sale_price,thumbnail',
                ])
                ->select('id', 'name', 'slug')
                ->take(8)
                ->get()
                ->map(function ($item) {
                    $variant = $item->productVariants->first();
                    $image = $item->productPic->first()->imagePath ?? ($variant->thumbnail ?? config('app.default_image'));
                    $price = $variant ? ($variant->sale_price ?? $variant->price ?? 0) : 0;
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'slug' => $item->slug,
                        'price' => number_format($price, 0, ',', '.'),
                        'image' => $image,
                    ];
                })
                ->values()
                ->toArray();

            // Sanitize description to prevent XSS
            $description = $product->description ?? 'Không có mô tả.';

            // Format response
            $formattedProduct = [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'phone' => $phone,
                'rating' => $rating,
                'stars' => (int) round($rating),
                'originalPrice' => number_format($defaultPrice, 2, '.', ''),
                'discountPercent' => $defaultPercent,
                'fullDescription' => $description,
                'sold' => (string) ($sold ?? 0),
                'stock' => collect($variants)->sum('stock'),
                'images' => $productImages,
                'variants' => $variants,
                'seller' => [
                    'id' => $seller->id,
                    'store_name' => $seller->store_name ?? 'N/A',
                    'store_slug' => $seller->store_slug ?? 'N/A',
                    'avatar' => $seller->user->avatar ?? "avatars/default.jpg",
                    'products_count' => $sellerProductsCount,
                    'rating' => $sellerRating,
                    'last_active' => $lastActive,
                ],
            ];

            return response()->json([
                'success' => true,
                'message' => 'Lấy thông tin sản phẩm thành công.',
                'data' => [
                    'product' => $formattedProduct,
                    'related_products' => $relatedProducts,
                    'tags' => $product->tags->toArray(),
                    'categories' => $product->categories->pluck('name')->toArray(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy thông tin sản phẩm. Vui lòng thử lại sau.',
            ], 500);
        }
    }
    public function changeStatus($id, Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chưa đăng nhập. Vui lòng đăng nhập để tiếp tục.',
                ], 401);
            }

            $user = Auth::user();
            $isAdmin = $user->role === 'admin';
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:active,inactive,draft,trash,restore',
            ], [
                'status.required' => 'Trạng thái là bắt buộc.',
                'status.in' => 'Trạng thái không hợp lệ.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy sản phẩm.',
                ], 404);
            }

            // Kiểm tra quyền sở hữu sản phẩm
            if (!$isAdmin) {
                $sellerId = Seller::where('user_id', $user->id)->value('id');
                if ($product->seller_id != $sellerId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bạn không có quyền thay đổi trạng thái sản phẩm này.',
                    ], 403);
                }
            }

            // Xử lý trạng thái đặc biệt
            if ($request->status === 'restore' && $product->status !== 'trash') {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ có thể khôi phục sản phẩm có trạng thái trash.',
                ], 422);
            }

            // Cập nhật trạng thái sản phẩm
            $product->status = $request->status;
            $product->save();

            $product->load([
                'categories',
                'productVariants',
                'productPic',
                'tags'
            ]);

            // Xóa cache sản phẩm
            $this->clearProductCache();
            return response()->json([
                'success' => true,
                'message' => 'Thay đổi trạng thái sản phẩm thành công!',
                'data' => $product
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Thay đổi trạng thái sản phẩm thất bại: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getTrash(Request $request)
    {
        $products = Product::with(['seller', 'categories', 'productVariants', 'productPic', 'tags', 'productVariants.inventories', 'productVariants.attributes'])
            ->when($request->has('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            })
            ->where('status', 'trash')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        $this->clearProductCache();
        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách sản phẩm thành công.',
            'data' => $products
        ], 200);
    }

    //hiện sản phẩm ra trang chủ, (có rating, sold, price, sale_price, thumbnail, categories, tags)
    public function getAllProducts(Request $request)
    {
        try {
            // Validate request parameters
            $validator = Validator::make($request->all(), [
                'page' => 'integer|min:1',
                'per_page' => 'integer|min:1|max:100',
                'search' => 'nullable|string|max:255',
                'price_min' => 'nullable|numeric|min:0',
                'price_max' => 'nullable|numeric|min:0',
                'brands' => 'nullable|string', // Chấp nhận chuỗi dạng "Samsung,Apple"
                'category_id' => 'nullable|integer|exists:categories,id',
            ], [
                'brands.string' => 'Danh sách thương hiệu không hợp lệ.',
                'price_min.numeric' => 'Giá tối thiểu phải là số.',
                'price_max.numeric' => 'Giá tối đa phải là số.',
                'category_id.exists' => 'Danh mục không hợp lệ.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu đầu vào không hợp lệ.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $page = (int) $request->get('page', 1);
            $search = trim($request->get('search', ''));
            $perPage = (int) $request->get('per_page', 24);
            $priceMin = $request->get('price_min', 0);
            $priceMax = $request->get('price_max', 100000000);
            $brands = $request->get('brands');
            $categoryId = $request->get('category_id', null);

            // Xử lý brands thành mảng
            $brandArray = $brands ? explode(',', $brands) : [];
            if ($brandArray) {
                // Validate brands tồn tại trong sellers.store_name
                $existingBrands = Seller::whereIn('store_name', $brandArray)->pluck('store_name')->toArray();
                $invalidBrands = array_diff($brandArray, $existingBrands);
                if ($invalidBrands) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Một số thương hiệu không hợp lệ: ' . implode(', ', $invalidBrands),
                    ], 422);
                }
            }

            // Create cache key
            $brandKey = implode(',', $brandArray);
            $cacheKey = 'shop_page_' . $page . '_perpage_' . $perPage . '_search_' . md5($search) . '_price_' . $priceMin . '_' . $priceMax . '_brands_' . md5($brandKey) . '_category_' . ($categoryId ?? 'none');
            $ttl = 3600; // Cache for 1 hour

            $products = Cache::store('redis')->tags(['products'])->remember($cacheKey, $ttl, function () use ($search, $perPage, $priceMin, $priceMax, $brandArray, $categoryId) {
                $query = Product::with([
                    'categories',
                    'productPic',
                    'productVariants.inventories',
                    'productVariants.orderItems',
                    'reviews',
                    'seller',
                    'tags',
                ])
                    ->where('status', 'active');

                // Search by name or description
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%')
                            ->orWhere('description', 'like', '%' . $search . '%');
                    });
                }

                // Filter by category
                if ($categoryId) {
                    $query->whereHas('categories', function ($q) use ($categoryId) {
                        $q->where('categories.id', $categoryId);
                    });
                }

                // Filter by price
                if ($priceMin !== null && $priceMax !== null) {
                    $query->whereHas('productVariants', function ($q) use ($priceMin, $priceMax) {
                        $q->where(function ($q2) use ($priceMin, $priceMax) {
                            $q2->whereBetween('price', [$priceMin, $priceMax])
                                ->orWhereBetween('sale_price', [$priceMin, $priceMax]);
                        });
                    });
                }

                // Filter by brands
                if ($brandArray) {
                    $query->whereHas('seller', function ($q) use ($brandArray) {
                        $q->whereIn('store_name', $brandArray);
                    });
                }

                // Sort by created_at
                $query->orderBy('created_at', 'desc');

                return $query->paginate($perPage);
            });

            // Format response
            $formatted = collect($products->items())->map(function ($product) {
                $variant = $product->productVariants->first();
                $price = $variant?->price ?? 0;
                $discount = $variant?->sale_price ?? null;
                $finalPrice = $discount ?? $price;

                // Average rating
                $rating = round($product->reviews->avg('rating') ?? 0);
                $stars = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);

                // Total sold
                $sold = $variant?->orderItems->sum('quantity') ?? 0;
                $percent = ($discount && $price > 0)
                    ? round((($price - $discount) / $price) * 100)
                    : 0;

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'image' => $product->productPic?->first()?->imagePath ?? $variant?->thumbnail ?? '/default.jpg',
                    'price' => number_format($finalPrice, 0, ',', '.'),
                    'discount' => $discount ? number_format($price, 0, ',', '.') : null,
                    'rating' => $stars,
                    'sold' => number_format($sold),
                    'brand' => $product->seller?->store_name ?? 'N/A',
                    'percent' => $percent,
                    'categories' => $product->categories->pluck('name')->implode(', '),
                    'tags' => $product->tags->pluck('name')->implode(', '),
                ];
            });

            // Get unique brands
            $brandSet = Seller::whereNotNull('store_name')
                ->where('verification_status', 'verified')
                ->pluck('store_name')
                ->unique()
                ->values();
            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách sản phẩm thành công.',
                'data' => [
                    'products' => $formatted,
                    'brands' => $brandSet,
                    'total' => $products->total(),
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu đầu vào không hợp lệ.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách sản phẩm.',
                'error' => env('APP_DEBUG', false) ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function getProducts(Request $request, $slug = null)
    {
        try {
            // Validate request parameters
            $validated = $request->validate([
                'page' => 'integer|min:1',
                'per_page' => 'integer|min:1|max:100',
                'search' => 'nullable|string|max:255',
                'price_min' => 'nullable|numeric|min:0',
                'price_max' => 'nullable|numeric|min:0',
                'brands' => 'nullable|string',
                'ratings' => 'nullable|array',
                'ratings.*' => 'integer|min:0|max:5',
                'on_sale' => 'nullable|in:0,1,true,false', // Sửa để chấp nhận rõ ràng true/false
                'sort' => 'nullable|in:default,popular,newest,bestseller',
                'price_order' => 'nullable|in:asc,desc',
                'category_id' => 'nullable|integer|exists:categories,id',
            ]);

            // Extract parameters
            $page = (int) $request->get('page', 1);
            $perPage = (int) $request->get('per_page', 24);
            $search = trim($request->get('search', ''));
            $priceMin = (float) $request->get('price_min', 0);
            $priceMax = (float) $request->get('price_max', 100000000);
            $brands = $request->get('brands') ? array_filter(explode(',', $request->get('brands'))) : [];
            $ratings = $request->get('ratings', []);
            $onSale = in_array($request->get('on_sale'), ['true', '1']); // Chuyển đổi rõ ràng
            $sort = $request->get('sort', 'default');
            $priceOrder = $request->get('price_order', '');
            $isSearchMode = $slug === 'search' || empty($slug);

            // Handle category
            $categoryIds = [];
            if (!$isSearchMode) {
                $category = Category::where('slug', $slug)->first();
                if (!$category) {
                } else {
                    $categoryIds = $this->getAllCategoryChildrenIds($category);
                    $categoryIds[] = $category->id;
                }
            }

            // Create cache key
            $brandKey = is_array($brands) ? implode(',', $brands) : ($brands ?? '');
            $ratingsKey = is_array($ratings) ? implode(',', $ratings) : ($ratings ?? '');
            $keyHash = md5(json_encode([
                $slug,
                $search,
                $page,
                $perPage,
                $priceMin,
                $priceMax,
                $brandKey,
                $ratingsKey,
                $onSale,
                $sort,
                $priceOrder,
                $categoryIds
            ]));
            $cacheKey = "products_{$slug}_page_{$page}_per_{$perPage}_{$keyHash}";
            $ttl = 3600; // Cache for 1 hour

            // Fetch products
            $products = Cache::store('redis')->tags(['products'])->remember($cacheKey, $ttl, function () use (
                $isSearchMode,
                $categoryIds,
                $search,
                $perPage,
                $priceMin,
                $priceMax,
                $brands,
                $ratings,
                $onSale,
                $sort,
                $priceOrder
            ) {
                $query = Product::with([
                    'categories',
                    'productPic',
                    'productVariants.inventories',
                    'productVariants.orderItems',
                    'reviews',
                    'seller',
                    'tags',
                ])->where('status', 'active');

                // Category filter
                if (!$isSearchMode && !empty($categoryIds)) {
                    $query->whereHas('categories', fn($q) => $q->whereIn('categories.id', $categoryIds));
                }

                // Search filter
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%')
                            ->orWhere('description', 'like', '%' . $search . '%')
                            ->orWhereHas('tags', fn($q) => $q->where('name', 'like', '%' . $search . '%'));
                    });
                }

                // Price filter
                $query->whereHas('productVariants', fn($q) => $q->where(function ($q2) use ($priceMin, $priceMax) {
                    $q2->whereBetween('price', [$priceMin, $priceMax])
                        ->orWhereBetween('sale_price', [$priceMin, $priceMax]);
                }));

                // Brand filter
                if (!empty($brands)) {
                    $query->whereHas('seller', fn($q) => $q->whereIn('store_name', $brands));
                }

                // Rating filter
                if (!empty($ratings)) {
                    $query->where(function ($q) use ($ratings) {
                        if (in_array(0, $ratings)) {
                            $q->whereDoesntHave('reviews')
                                ->orWhereHas('reviews', fn($q2) => $q2->whereIn('rating', array_filter($ratings, fn($r) => $r > 0)));
                        } else {
                            $q->whereHas('reviews', fn($q2) => $q2->whereIn('rating', $ratings));
                        }
                    });
                }

                // On sale filter
                if ($onSale) {
                    $query->whereHas('productVariants', fn($q) => $q->whereNotNull('sale_price')->where('sale_price', '<', DB::raw('price')));
                }

                // Sorting
                if ($priceOrder === 'asc' || $priceOrder === 'desc') {
                    $query->orderByRaw('(SELECT MIN(COALESCE(sale_price, price)) FROM product_variants WHERE product_variants.product_id = products.id) ' . $priceOrder);
                } else {
                    switch ($sort) {
                        case 'newest':
                            $query->orderBy('created_at', 'desc');
                            break;
                        case 'popular':
                            $query->withCount('reviews')->orderBy('reviews_count', 'desc');
                            break;
                        case 'bestseller':
                            $query->orderByRaw('(SELECT SUM(quantity) FROM order_items JOIN product_variants ON order_items.product_variant_id = product_variants.id WHERE product_variants.product_id = products.id) DESC');
                            break;
                        default:
                            $query->orderBy('created_at', 'desc');
                            break;
                    }
                }

                return $query->paginate($perPage);
            });

            // Format response
            $formatted = collect($products->items())->map(function ($product) {
                $variant = $product->productVariants->first();
                $price = $variant?->price ?? 0;
                $discount = $variant?->sale_price ?? null;
                $finalPrice = $discount ?? $price;

                $sold = $variant?->orderItems->sum('quantity') ?? 0;
                $rating = round($product->reviews->avg('rating') ?? 0);
                $percent = ($discount && $price > 0) ? round((($price - $discount) / $price) * 100) : 0;

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'image' => $product->productPic->first()->imagePath ?? $variant?->thumbnail ?? '/default-image.jpg',
                    'price' => number_format($finalPrice, 0, '.', ''),
                    'discount' => $discount ? number_format($price, 0, '.', '') : null,
                    'rating' => str_repeat('★', $rating) . str_repeat('☆', 5 - $rating),
                    'sold' => $sold,
                    'brand' => $product->seller?->store_name ?? 'N/A',
                    'percent' => $percent,
                    'categories' => $product->categories->pluck('name')->implode(', '),
                    'tags' => $product->tags->pluck('name')->implode(', '),
                ];
            });

            // Get unique brands
            $brandsList = collect($formatted)->pluck('brand')->filter()->unique()->values();

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách sản phẩm thành công.',
                'data' => [
                    'products' => $formatted,
                    'brands' => $brandsList,
                    'total' => $products->total(),
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu đầu vào không hợp lệ.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách sản phẩm.',
                'error' => env('APP_DEBUG', false) ? $e->getMessage() : null,
            ], 500);
        }
    }
    protected function getAllCategoryChildrenIds($category)
    {
        $ids = [];
        foreach ($category->children() as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getAllCategoryChildrenIds($child));
        }
        return $ids;
    }

    public function getAllProductBySellers(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chưa đăng nhập. Vui lý đăng nhập.',
                ], 401);
            }

            $seller = Seller::where('user_id', $user->id)->first();

            if (!$seller) {
                return response()->json([
                    'success' => false,
                    'message' => 'Người dùng không phải là seller.',
                ], 403);
            }

            $page = $request->get('page', 1);
            $search = trim($request->get('search', ''));
            $perPage = $request->get('per_page', 10);

            $cacheKey = 'products_seller_' . $seller->id . '_page_' . $page . '_perpage_' . $perPage . '_search_' . md5($search);
            $ttl = 3600;

            $products = Cache::store('redis')->tags(['products'])->remember($cacheKey, $ttl, function () use ($search, $perPage, $seller) {
                return Product::with([
                    'seller',
                    'categories',
                    'productVariants',
                    'productVariants.inventories',
                    'productVariants.attributes',
                    'productPic',
                    'tags'
                ])
                    ->where('seller_id', $seller->id)
                    ->whereIn('status', ['active', 'inactive'])
                    ->when($search, function ($query) use ($search) {
                        return $query->where('name', 'like', '%' . $search . '%');
                    })
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage)
                    ->toArray();
            });

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách sản phẩm của seller thành công.',
                'data' => $products
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách sản phẩm.',
                'error' => env('APP_DEBUG', false) ? $e->getMessage() : null
            ], 500);
        }
    }
    public function getTrashBySeller(Request $request)
    {
        try {
            $seller = Seller::where('user_id', Auth::id())->first();
            if (!$seller) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy seller tương ứng với user hiện tại.',
                ], 404);
            }

            $products = Product::with([
                'seller',
                'categories',
                'productVariants',
                'productPic',
                'tags',
                'productVariants.inventories',
                'productVariants.attributes'
            ])
                ->where('status', 'trash')
                ->where('seller_id', $seller->id)
                ->when($request->has('search'), function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->search . '%');
                })
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách sản phẩm (trash) của seller thành công.',
                'data' => $products
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy sản phẩm.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null,
            ], 500);
        }
    }
}