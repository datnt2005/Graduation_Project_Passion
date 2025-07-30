<?php

namespace App\Services;

use App\Models\{Category, Product, SearchHistory, Trend, Seller};
use App\Repositories\{TrendRepository, SearchHistoryRepository};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, Redis, DB, Log};
use Carbon\Carbon;

class SearchService
{
    protected $trendRepository;
    protected $searchHistoryRepository;

    public function __construct(TrendRepository $trendRepository, SearchHistoryRepository $searchHistoryRepository)
    {
        $this->trendRepository = $trendRepository;
        $this->searchHistoryRepository = $searchHistoryRepository;
    }

    public function addSearch($userId, $sessionId, $keyword)
    {
        $keyword = trim($keyword);
        if (mb_strlen($keyword) < 2 || is_numeric($keyword)) return;

        $date = Carbon::now()->format('Ymd');
        $keySuffix = $userId ? "user_{$userId}:{$date}" : "sess_{$sessionId}:{$date}";
        $redisKey = "search_history:$keySuffix";
        $ttl = $userId ? 60 * 60 * 24 * 7 : 60 * 60 * 24; // 7 days for user, 1 hour for session

        $lastKeyword = Redis::lindex($redisKey, 0);
        if ($lastKeyword === $keyword) return;

        Redis::lrem($redisKey, 0, $keyword);
        Redis::lpush($redisKey, $keyword);
        Redis::ltrim($redisKey, 0, 9);
        Redis::expire($redisKey, $ttl);

        // Thống kê keyword toàn hệ thống
        Redis::zincrby('global:search_keywords', 1, $keyword);

        $this->searchHistoryRepository->addSearch($userId, $sessionId, $keyword);
        $this->trendRepository->incrementKeyword($keyword);
    }

    public function trackProductClick($productId)
    {
        DB::table('trends')->updateOrInsert(
            ['entity_type' => 'product', 'entity_id' => $productId],
            [
                'click_count' => DB::raw('click_count + 1'),
                'last_updated' => now(),
            ]
        );
    }

    public function trackProductSearchViews(array $productIds)
    {
        foreach ($productIds as $id) {
            DB::table('trends')->updateOrInsert(
                ['entity_type' => 'product', 'entity_id' => $id],
                [
                    'search_count' => DB::raw('search_count + 1'),
                    'last_updated' => now(),
                ]
            );
        }
    }

public function getProducts(Request $request, $slug = null)
{
    try {
        // Validate input
        $validated = $request->validate([
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'search' => 'nullable|string|max:255',
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0',
            'brands' => 'nullable|string',
            'ratings' => 'nullable|array',
            'ratings.*' => 'integer|min:0|max:5',
            'on_sale' => 'nullable|in:0,1,true,false',
            'sort' => 'nullable|in:default,popular,newest,bestseller',
            'price_order' => 'nullable|in:asc,desc',
            'category_id' => 'nullable|integer|exists:categories,id',
        ]);

        // Extract parameters
        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 24);
        $search = htmlspecialchars(trim($request->get('search', '')));
        $priceMin = (float) $request->get('price_min', 0);
        $priceMax = (float) $request->get('price_max', 100000000);
        $brands = $request->get('brands') ? array_filter(explode(',', $request->get('brands'))) : [];
        $ratings = $request->get('ratings', []);
        $onSale = in_array($request->get('on_sale'), ['true', '1']);
        $sort = $request->get('sort', 'default');
        $priceOrder = $request->get('price_order', '');
        $isSearchMode = $slug === 'search' || empty($slug);

        // Get category IDs
        $categoryIds = [];
        $isCategoryMode = false;
        $currentCategory = null;
        if (!$isSearchMode) {
            $currentCategory = Category::where('slug', $slug)->first();
            if ($currentCategory) {
                $isCategoryMode = true;
                $categoryIds = $this->getAllCategoryChildrenIds($currentCategory) ?? [];
                $categoryIds[] = $currentCategory->id;
            }
        }

        // Generate cache key
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

        // Fetch products from cache or database
        $productsData = Cache::store('redis')->tags(['products'])->remember($cacheKey, $ttl, function () use (
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
            // Build main product query
            $query = $this->buildQuery(
                Product::query()->select(['products.id', 'products.name', 'products.slug', 'products.seller_id', 'products.status', 'products.admin_status']),
                $isSearchMode,
                $categoryIds,
                $search,
                $priceMin,
                $priceMax,
                $brands,
                $ratings,
                $onSale,
                $sort,
                $priceOrder
            );
            return $query->with(['categories' => fn($q) => $q->select('categories.id', 'categories.name', 'categories.slug', 'categories.parent_id')])
                        ->paginate($perPage);
        });

        // Format products
        $formattedProducts = collect($productsData->items())->map(function ($product) {
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
                'image' => $product->productPic->first()->imagePath ?? $variant?->thumbnail ?? 'products/default.png',
                'price' => number_format($finalPrice, 0, '.', ''),
                'discount' => $discount ? number_format($price, 0, '.', '') : null,
                'rating' => str_repeat('★', $rating) . str_repeat('☆', 5 - $rating),
                'sold' => $sold,
                'brand' => $product->seller?->store_name ?? 'N/A',
                'percent' => $percent,
                'categories' => $product->categories->map(fn($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'parent_id' => $cat->parent_id,
                ])->toArray(),
                'tags' => $product->tags->pluck('name')->implode(', '),
                'seller_id' => $product->seller->id ?? null,
            ];
        })->toArray();

        // Track product search views
        $productIds = array_column($formattedProducts, 'id');
        if (!empty($productIds)) {
            $this->trackProductSearchViews($productIds);
        }

        // Fetch categories for sidebar
        $categories = [];
        if ($isCategoryMode && $currentCategory) {
            // In category mode, include the current category, its parent, and its children
            $categories = $this->getCategoryTreeForCategory($currentCategory);
        } elseif ($isSearchMode) {
            if (!empty($formattedProducts)) {
                // In search mode with products, get categories from products
                $categoryIds = collect($formattedProducts)
                    ->pluck('categories')
                    ->flatten(1)
                    ->pluck('id')
                    ->unique()
                    ->values()
                    ->toArray();
                $categories = $this->getCategoryTreeForIds($categoryIds);
            } elseif (!empty($relatedShops)) {
                // In search mode with shops but no products, get categories from shop products
                $shopIds = array_column($relatedShops, 'id');
                $categoryIds = Product::whereHas('seller', fn($q) => $q->whereIn('sellers.id', $shopIds))
                    ->where('status', 'active')
                    ->where('admin_status', 'approved')
                    ->with(['categories' => fn($q) => $q->select('categories.id', 'categories.name', 'categories.slug', 'categories.parent_id')])
                    ->get()
                    ->pluck('categories')
                    ->flatten(1)
                    ->pluck('id')
                    ->unique()
                    ->values()
                    ->toArray();
                $categories = $this->getCategoryTreeForIds($categoryIds);
            }
        }

        // Get related shops if in search mode
        $relatedShops = [];
        if ($isSearchMode && $search) {
            $searchVariants = $this->normalizeVietnamese($search);
            $shopQuery = Seller::with([
                'user' => fn($q) => $q->select('users.id', 'users.avatar'),
                'followers' => fn($q) => $q->select('seller_followers.id', 'seller_followers.seller_id'),
                'products' => fn($q) => $q->select('products.id', 'products.seller_id', 'products.status', 'products.admin_status')
                    ->where('status', 'active')->where('admin_status', 'approved'),
                'products.reviews' => fn($q) => $q->select('reviews.id', 'reviews.product_id', 'reviews.rating'),
            ])->where(function ($q) use ($searchVariants) {
                foreach ($searchVariants as $variant) {
                    $patterns = ["% $variant %", "$variant %", "% $variant", $variant];
                    foreach ($patterns as $pattern) {
                        $q->orWhere('store_name', 'LIKE', $pattern);
                    }
                }
            })->select('sellers.id', 'sellers.store_name', 'sellers.store_slug', 'sellers.user_id')->get();

            $relatedShops = $shopQuery->map(function ($seller) {
                $rating = round($seller->products->flatMap(fn($p) => $p->reviews)->avg('rating') ?? 0, 2);
                $totalProducts = $seller->products->where('status', 'active')->where('admin_status', 'approved')->count();
                return [
                    'id' => $seller->id,
                    'store_name' => $seller->store_name,
                    'store_slug' => $seller->store_slug,
                    'avatar' => $seller->user?->avatar ?? 'avatars/default.jpg',
                    'followers' => $seller->followers->count(),
                    'rating' => $rating,
                    'total_products' => $totalProducts,
                ];
            })->toArray();

            // If no products match, fetch products from related shops
            if (empty($formattedProducts) && !empty($relatedShops)) {
                $shopIds = array_column($relatedShops, 'id');
                $shopQuery = $this->buildQuery(
                    Product::whereHas('seller', fn($q) => $q->whereIn('sellers.id', $shopIds))
                        ->select(['products.id', 'products.name', 'products.slug', 'products.seller_id', 'products.status', 'products.admin_status']),
                    $isSearchMode,
                    $categoryIds,
                    '',
                    $priceMin,
                    $priceMax,
                    $brands,
                    $ratings,
                    $onSale,
                    $sort,
                    $priceOrder
                );
                $productsData = $shopQuery->with(['categories' => fn($q) => $q->select('categories.id', 'categories.name', 'categories.slug', 'categories.parent_id')])
                    ->paginate($perPage);

                $formattedProducts = collect($productsData->items())->map(function ($product) {
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
                        'image' => $product->productPic->first()->imagePath ?? $variant?->thumbnail ?? 'products/default.png',
                        'price' => number_format($finalPrice, 0, '.', ''),
                        'discount' => $discount ? number_format($price, 0, '.', '') : null,
                        'rating' => str_repeat('★', $rating) . str_repeat('☆', 5 - $rating),
                        'sold' => $sold,
                        'brand' => $product->seller?->store_name ?? 'N/A',
                        'percent' => $percent,
                        'categories' => $product->categories->map(fn($cat) => [
                            'id' => $cat->id,
                            'name' => $cat->name,
                            'slug' => $cat->slug,
                            'parent_id' => $cat->parent_id,
                        ])->toArray(),
                        'tags' => $product->tags->pluck('name')->implode(', '),
                        'seller_id' => $product->seller->id ?? null,
                    ];
                })->toArray();

                $productIds = array_column($formattedProducts, 'id');
                if (!empty($productIds)) {
                    $this->trackProductSearchViews($productIds);
                }
            }
        }

        // Get shop information only if not in category mode
        $shopInfo = null;
        if (!$isSearchMode && !$isCategoryMode && $slug) {
            $seller = Seller::where('store_slug', $slug)
                ->with([
                    'user' => fn($q) => $q->select('users.id', 'users.avatar'),
                    'followers' => fn($q) => $q->select('seller_followers.id', 'seller_followers.seller_id'),
                    'products' => fn($q) => $q->select('products.id', 'products.seller_id', 'products.status', 'products.admin_status')
                        ->where('status', 'active')->where('admin_status', 'approved'),
                    'products.reviews' => fn($q) => $q->select('reviews.id', 'reviews.product_id', 'reviews.rating'),
                ])
                ->select('sellers.id', 'sellers.store_name', 'sellers.store_slug', 'sellers.user_id', 'sellers.verification_status')
                ->first();

            if ($seller) {
                $rating = round($seller->products->flatMap(fn($p) => $p->reviews)->avg('rating') ?? 0, 1);
                $shopInfo = [
                    'avatar' => $seller->user?->avatar ?? 'avatars/default.jpg',
                    'store_name' => $seller->store_name,
                    'store_slug' => $seller->store_slug,
                    'likes' => $seller->followers->count(),
                    'followers' => $seller->followers->count(),
                    'total_products' => $seller->products->count(),
                    'rating_stars' => str_repeat('★', round($rating)) . str_repeat('☆', 5 - round($rating)),
                    'rating_value' => sprintf('%.1f/5 (%d%%)', $rating, round($rating / 5 * 100)),
                    'status' => $seller->verification_status ?? 'verified',
                ];
            } else {
                $shopInfo = [
                    'avatar' => 'avatars/default.jpg',
                    'store_name' => 'N/A',
                    'store_slug' => $slug,
                    'likes' => 0,
                    'followers' => 0,
                    'total_products' => 0,
                    'rating_stars' => str_repeat('☆', 5),
                    'rating_value' => '0/5 (0%)',
                    'status' => 'verified',
                ];
            }
        }

        // Sort brands alphabetically
        $brandsList = array_values(array_filter(array_unique(array_column($formattedProducts, 'brand'))));

        // Format categories for frontend
        $formattedCategories = $this->formatCategories($categories);

        // Return response
        if (empty($formattedProducts) && empty($relatedShops) && $isSearchMode) {
            return [
                'success' => true,
                'message' => 'Không tìm thấy sản phẩm hoặc cửa hàng phù hợp với từ khóa tìm kiếm.',
                'data' => [
                    'shops' => [],
                    'products' => [],
                    'brands' => [],
                    'categories' => [],
                    'total' => 0,
                    'current_page' => 1,
                    'last_page' => 1,
                    'shop' => $shopInfo,
                ],
            ];
        }

        return [
            'success' => true,
            'message' => 'Lấy danh sách sản phẩm thành công.',
            'data' => [
                'shops' => $relatedShops,
                'products' => $formattedProducts,
                'brands' => $brandsList,
                'categories' => $formattedCategories,
                'total' => $productsData->total(),
                'current_page' => $productsData->currentPage(),
                'last_page' => $productsData->lastPage(),
                'shop' => $shopInfo,
            ],
        ];
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::warning('Validation error in getProducts: ', ['errors' => $e->errors()]);
        return [
            'success' => false,
            'message' => 'Dữ liệu đầu vào không hợp lệ.',
            'errors' => $e->errors(),
        ];
    } catch (\Exception $e) {
        Log::error('Exception in getProducts: ', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request' => $request->all()
        ]);
        return [
            'success' => false,
            'message' => 'Đã xảy ra lỗi khi lấy danh sách sản phẩm.',
            'error' => env('APP_DEBUG', false) ? $e->getMessage() : null,
        ];
    }
}

/**
 * Helper function to get category tree for a specific category
 */
protected function getCategoryTreeForCategory($category)
{
    $categories = [];

    // Include the current category
    $categories[] = [
        'id' => $category->id,
        'name' => $category->name,
        'slug' => $category->slug,
        'parent_id' => $category->parent_id,
    ];

    // Include parent category if exists
    if ($category->parent_id) {
        $parent = Category::where('id', $category->parent_id)
            ->select('id', 'name', 'slug', 'parent_id')
            ->first();
        if ($parent) {
            $categories[] = [
                'id' => $parent->id,
                'name' => $parent->name,
                'slug' => $parent->slug,
                'parent_id' => $parent->parent_id,
            ];
        }
    }

    // Include child categories
    $children = Category::where('parent_id', $category->id)
        ->select('id', 'name', 'slug', 'parent_id')
        ->get()
        ->map(fn($child) => [
            'id' => $child->id,
            'name' => $child->name,
            'slug' => $child->slug,
            'parent_id' => $child->parent_id,
        ])
        ->toArray();

    return array_merge($categories, $children);
}

/**
 * Helper function to get category tree for a list of category IDs
 */
protected function getCategoryTreeForIds($categoryIds)
{
    if (empty($categoryIds)) {
        return [];
    }

    // Fetch categories and their parents
    $categories = Category::whereIn('id', $categoryIds)
        ->orWhereIn('id', function ($query) use ($categoryIds) {
            $query->select('parent_id')
                ->from('categories')
                ->whereIn('id', $categoryIds)
                ->whereNotNull('parent_id');
        })
        ->select('id', 'name', 'slug', 'parent_id')
        ->get()
        ->map(fn($cat) => [
            'id' => $cat->id,
            'name' => $cat->name,
            'slug' => $cat->slug,
            'parent_id' => $cat->parent_id,
        ])
        ->toArray();

    return $categories;
}

/**
 * Helper function to format categories into a tree structure
 */
protected function formatCategories($categories)
{
    $tree = [];
    $categoryMap = [];

    // Create a map for quick lookup
    foreach ($categories as $cat) {
        $categoryMap[$cat['id']] = [
            'id' => $cat['id'],
            'name' => $cat['name'],
            'slug' => $cat['slug'],
            'parent_id' => $cat['parent_id'],
            'children' => [],
        ];
    }

    // Build the tree
    foreach ($categoryMap as $id => $cat) {
        if ($cat['parent_id'] && isset($categoryMap[$cat['parent_id']])) {
            $categoryMap[$cat['parent_id']]['children'][] = &$categoryMap[$id];
        } else {
            $tree[] = &$categoryMap[$id];
        }
    }

    return array_values($tree);
}

/**
 * Helper function to get all child category IDs
 */
protected function getAllCategoryChildrenIds($category)
{
    $ids = [];
    $children = Category::where('parent_id', $category->id)->pluck('id');
    foreach ($children as $childId) {
        $ids[] = $childId;
        $ids = array_merge($ids, $this->getAllCategoryChildrenIds(Category::find($childId)));
    }
    return $ids;
}
    private function buildQuery($baseQuery, $isSearchMode, $categoryIds, $search, $priceMin, $priceMax, $brands, $ratings, $onSale, $sort, $priceOrder)
    {
        $query = $baseQuery->with([
            'categories' => fn($q) => $q->select('categories.id', 'categories.name'),
            'productPic' => fn($q) => $q->select('product_id', 'imagePath')->latest(),
            'productVariants.inventories' => fn($q) => $q->select('id', 'product_variant_id', 'quantity'),
            'productVariants.orderItems' => fn($q) => $q->select('id', 'product_variant_id', 'quantity'),
            'reviews' => fn($q) => $q->select('id', 'product_id', 'rating'),
            'seller' => fn($q) => $q->select('id', 'store_name'),
            'tags' => fn($q) => $q->select('tags.id', 'tags.name'),
        ])->where('status', 'active')
            ->where('admin_status', 'approved');

        if (!$isSearchMode && !empty($categoryIds)) {
            $query->whereHas('categories', fn($q) => $q->whereIn('categories.id', $categoryIds));
        }

        if ($search) {
            $searchVariants = $this->normalizeVietnamese($search);
            $query->where(function ($q) use ($searchVariants) {
                foreach ($searchVariants as $variant) {
                    $patterns = ["% $variant %", "$variant %", "% $variant", $variant];
                    foreach ($patterns as $pattern) {
                        $q->orWhere('name', 'LIKE', $pattern);
                    }
                }
            });
        }

        $query->whereHas('productVariants', fn($q) => $q->where(function ($q2) use ($priceMin, $priceMax) {
            $q2->whereBetween('price', [$priceMin, $priceMax])
                ->orWhereBetween('sale_price', [$priceMin, $priceMax]);
        }));

        if (!empty($brands)) {
            $query->whereHas('seller', fn($q) => $q->whereIn('store_name', $brands));
        }

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

        if ($onSale) {
            $query->whereHas('productVariants', fn($q) => $q->whereNotNull('sale_price')->where('sale_price', '<', DB::raw('price')));
        }

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
                    $query->orderByRaw('(SELECT SUM(quantity) FROM order_items WHERE order_items.product_variant_id IN (SELECT id FROM product_variants WHERE product_variants.product_id = products.id)) DESC');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        }

        return $query;
    }

    /**
     * Normalize Vietnamese text for search
     *
     * @param string $text
     * @return array
     */
    private function normalizeVietnamese($text)
    {
        $text = mb_strtolower($text, 'UTF-8');
        $unaccented = [
            'a' => ['à', 'á', 'ả', 'ã', 'ạ', 'ă', 'ằ', 'ắ', 'ẳ', 'ẵ', 'ặ', 'â', 'ầ', 'ấ', 'ẩ', 'ẫ', 'ậ'],
            'e' => ['è', 'é', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ề', 'ế', 'ể', 'ễ', 'ệ'],
            'i' => ['ì', 'í', 'ỉ', 'ĩ', 'ị'],
            'o' => ['ò', 'ó', 'ỏ', 'õ', 'ọ', 'ô', 'ồ', 'ố', 'ổ', 'ỗ', 'ộ', 'ơ', 'ờ', 'ớ', 'ở', 'ỡ', 'ợ'],
            'u' => ['ù', 'ú', 'ủ', 'ũ', 'ụ', 'ư', 'ừ', 'ứ', 'ử', 'ữ', 'ự'],
            'y' => ['ỳ', 'ý', 'ỷ', 'ỹ', 'ỵ'],
            'd' => ['đ'],
        ];

        foreach ($unaccented as $base => $chars) {
            $text = str_replace($chars, $base, $text);
        }

        return [$text];
    }

    public function getSearchSuggestions($userId, $sessionId)
    {
        $date = Carbon::now()->format('Ymd');
        $keySuffix = $userId ? "user_{$userId}:{$date}" : "sess_{$sessionId}:{$date}";
        $redisKey = "search_history:$keySuffix";
        $history = Redis::lrange($redisKey, 0, 9);

        $topKeywords = Redis::zrevrange('global:search_keywords', 0, 9);
        $topProducts = $this->trendRepository->getTopProducts(10);

        $products = Product::whereIn('id', array_keys($topProducts))
            ->select('id', 'name', 'slug')
            ->with(['productPic' => function ($query) {
                $query->select('product_id', 'imagePath')->orderBy('created_at', 'desc');
            }])
            ->get()
            ->mapWithKeys(function ($product) {
                return [$product->id => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->productPic->first()->imagePath ?? 'products/default.png',
                ]];
            })->toArray();

        return [
            'history' => $history,
            'top_keywords' => $topKeywords,
            'top_products' => array_values($products),
        ];
    }

    public function syncSessionToUser($sessionId, $userId)
    {
        $date = Carbon::now()->format('Ymd');
        $sessionKey = "search_history:sess_{$sessionId}:{$date}";
        $userKey = "search_history:user_{$userId}:{$date}";

        $keywords = Redis::lrange($sessionKey, 0, 9);
        foreach (array_reverse($keywords) as $keyword) {
            Redis::lrem($userKey, 0, $keyword);
            Redis::lpush($userKey, $keyword);
        }
        Redis::ltrim($userKey, 0, 9);
        Redis::expire($userKey, 60 * 60 * 24 * 7);
        Redis::del($sessionKey);
    }

    public function deleteSearchHistory($userId, $sessionId, $keyword = null)
    {
        $date = Carbon::now()->format('Ymd');
        $redisKey = $userId ? "search_history:user_{$userId}:{$date}" : "search_history:sess_{$sessionId}:{$date}";

        if ($keyword) {
            Redis::lrem($redisKey, 0, $keyword);
            $this->searchHistoryRepository->deleteSearch($userId, $sessionId, $keyword);
        } else {
            Redis::del($redisKey);
            $this->searchHistoryRepository->deleteSearch($userId, $sessionId);
        }
    }

    public function getTrendingProducts($limit = 20)
    {
        // Lấy top N sản phẩm theo tổng search_count + click_count
        $topProducts = DB::table('trends')
            ->where('entity_type', 'product')
            ->orderByDesc(DB::raw('search_count + click_count'))
            ->limit($limit)
            ->pluck('entity_id') // lấy danh sách id sản phẩm
            ->toArray();

        // Nếu không có sản phẩm thì trả mảng rỗng
        if (empty($topProducts)) return [];

        // Ép kiểu entity_id về int nếu cần
        $productIds = array_map('intval', $topProducts);

        // Lấy dữ liệu sản phẩm tương ứng
        return Product::whereIn('id', $productIds)
            ->select('id', 'name', 'slug')
            ->where('status', 'active')
            ->where('admin_status', 'approved')
            ->with(['productPic' => function ($query) {
                $query->select('product_id', 'imagePath')->latest();
            }])
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->productPic->first()->imagePath ?? 'products/default.png',
                    'slug' => $product->slug,
                ];
            })
            ->values();
    }

    public function trackCategoryClick($categoryId)
    {
        DB::table('trends')->updateOrInsert(
            ['entity_type' => 'category', 'entity_id' => $categoryId],
            [
                'click_count' => DB::raw('click_count + 1'),
                'last_updated' => now(),
            ]
        );
    }
    public function getTrendingCategories($limit = 20)
    {
        $topCategories = DB::table('trends')
            ->where('entity_type', 'category')
            ->orderByDesc(DB::raw('search_count + click_count'))
            ->limit($limit)
            ->pluck('entity_id')
            ->toArray();

        if (empty($topCategories)) return [];

        return Category::whereIn('id', $topCategories)
            ->select('id', 'name', 'slug', 'image') // giả sử có trường `image`
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'image' => $category->image ?? 'products/default.png',
                ];
            })
            ->values();
    }
}
