<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Repositories\TrendRepository;
use App\Repositories\SearchHistoryRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
        $this->searchHistoryRepository->addSearch($userId, $sessionId, $keyword);
        $this->trendRepository->incrementKeyword($keyword);
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
                $slug, $search, $page, $perPage, $priceMin, $priceMax, $brandKey,
                $ratingsKey, $onSale, $sort, $priceOrder, $categoryIds
            ]));
            $cacheKey = "products_{$slug}_page_{$page}_per_{$perPage}_{$keyHash}";
            $ttl = 3600;

            $products = Cache::store('redis')->tags(['products'])->remember($cacheKey, $ttl, function () use (
                $isSearchMode, $categoryIds, $search, $perPage, $priceMin, $priceMax,
                $brands, $ratings, $onSale, $sort, $priceOrder
            ) {
                $query = Product::with([
                    'categories', 'productPic', 'productVariants.inventories',
                    'productVariants.orderItems', 'reviews', 'seller', 'tags'
                ])->where('status', 'active');

                if (!$isSearchMode && !empty($categoryIds)) {
                    $query->whereHas('categories', fn($q) => $q->whereIn('categories.id', $categoryIds));
                }

                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%')
                            ->orWhere('description', 'like', '%' . $search . '%')
                            ->orWhereHas('tags', fn($q) => $q->where('name', 'like', '%' . $search . '%'));
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
        $history = $this->searchHistoryRepository->getRecentSearches($userId, $sessionId);
        $topKeywords = $this->trendRepository->getTopKeywords(10);
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
                    'image' => $product->productPic->first()->imagePath ?? '/default-image.jpg',
                ]];
            })->toArray();

        return [
            'history' => $history,
            'top_keywords' => array_keys($topKeywords),
            'top_products' => array_values($products),
        ];
    }

    public function syncSessionToUser($sessionId, $userId)
    {
        $this->searchHistoryRepository->syncSessionToUser($sessionId, $userId);
    }

    public function deleteSearchHistory($userId, $sessionId, $keyword = null)
    {
        $this->searchHistoryRepository->deleteSearch($userId, $sessionId, $keyword);
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
}

