<?php

namespace App\Services;

use App\Models\{Category, Product, SearchHistory, Trend};
use App\Repositories\{TrendRepository, SearchHistoryRepository};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, Redis, DB};
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

            $categoryIds = [];
            if (!$isSearchMode) {
                $category = Category::where('slug', $slug)->first();
                if ($category) {
                    $categoryIds = $this->getAllCategoryChildrenIds($category);
                    $categoryIds[] = $category->id;
                }
            }

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
            $ttl = 3600;

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
                    'tags'
                ])->where('status', 'active')
                    ->where('admin_status', 'approved');

                if (!$isSearchMode && !empty($categoryIds)) {
                    $query->whereHas('categories', fn($q) => $q->whereIn('categories.id', $categoryIds));
                }

                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%")
                            ->orWhere('description', 'like', "%$search%")
                            ->orWhereHas('tags', fn($q) => $q->where('name', 'like', "%$search%"));
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

                return $query->paginate($perPage);
            });

            // Track search trends
            if ($isSearchMode && !empty($search)) {
                $productIds = collect($products->items())->pluck('id')->toArray();
                $this->trackProductSearchViews($productIds);
            }

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
                    'image' => $product->productPic->first()->imagePath ?? $variant?->thumbnail ?? 'products/default.png',
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

            $brandsList = collect($formatted)->pluck('brand')->filter()->unique()->values();

            return [
                'success' => true,
                'message' => 'Lấy danh sách sản phẩm thành công.',
                'data' => [
                    'products' => $formatted,
                    'brands' => $brandsList,
                    'total' => $products->total(),
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                ],
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            return [
                'success' => false,
                'message' => 'Dữ liệu đầu vào không hợp lệ.',
                'errors' => $e->errors(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách sản phẩm.',
                'error' => env('APP_DEBUG', false) ? $e->getMessage() : null,
            ];
        }
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

    private function getAllCategoryChildrenIds(Category $category)
    {
        $ids = [];
        $children = $category->children()->get();
        foreach ($children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getAllCategoryChildrenIds($child));
        }
        return $ids;
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
                    'image' => $category->image ?? '/default-category.jpg',
                ];
            })
            ->values();
    }
}
