<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    private function refreshCartCache($userId, $selectedItemIds = [])
    {
        try {
            $cacheKey = "cart_user_{$userId}";

            // Clear existing cart cache
            Cache::store('redis')->forget($cacheKey);

            // Fetch and cache cart data
            $cartData = Cache::store('redis')->remember($cacheKey, 86400, function () use ($userId, $selectedItemIds) {
                $cart = Cart::with([
                    'items' => fn($query) => $query->select('id', 'cart_id', 'product_variant_id', 'quantity', 'price'),
                    'items.productVariant' => fn($query) => $query->select('id', 'product_id', 'sku', 'thumbnail', 'sale_price', 'quantity'),
                    'items.productVariant.product' => fn($query) => $query->select('id', 'seller_id', 'name', 'slug'),
                    'items.productVariant.product.seller' => fn($query) => $query->select('id', 'store_name', 'store_slug'),
                    'items.productVariant.attributes.values' => fn($query) => $query->select('id', 'attribute_id', 'value'),
                    'items.productVariant.product.productPic' => fn($query) => $query->select('id', 'product_id', 'imagePath')
                ])
                    ->where('user_id', $userId)
                    ->where('status', 'active')
                    ->select('id', 'user_id', 'status')
                    ->first();

                if (!$cart) {
                    return [
                        'stores' => [],
                        'total' => '0'
                    ];
                }

                // Group items by seller
                $itemsByStore = $cart->items->groupBy(function ($item) {
                    return $item->productVariant->product->seller->id ?? 0;
                });

                $formattedStores = $itemsByStore->map(function ($items, $sellerId) use ($selectedItemIds) {
                    $seller = $items->first()->productVariant->product->seller;

                    $storeItems = $items->map(function ($item) use ($selectedItemIds) {
                        $variant = $item->productVariant;
                        $product = $variant->product;

                        $attributes = $variant->attributes->map(function ($attr) {
                            $value = $attr->pivot->value_id
                                ? $attr->values->find($attr->pivot->value_id)
                                : null;
                            return [
                                'attribute' => $attr->name,
                                'value' => $value ? $value->value : null
                            ];
                        })->filter()->values();

                        return [
                            'id' => $item->id,
                            'quantity' => $item->quantity,
                            'price' => number_format($item->price, 0, ',', '.'),
                            'sale_price' => $variant->sale_price ? number_format($variant->sale_price, 0, ',', '.') : null,
                            'product_variant_id' => $item->product_variant_id,
                            'stock' => $variant->quantity ?? 0,
                            'is_selected' => in_array($item->id, $selectedItemIds),
                            'productVariant' => [
                                'id' => $variant->id,
                                'sku' => $variant->sku,
                                'thumbnail' => $variant->thumbnail
                                    ? config('app.media_base_url') . $variant->thumbnail
                                    : ($product->productPic->first()
                                        ? config('app.media_base_url') . $product->productPic->first()->imagePath
                                        : '/default.jpg'),
                                'attributes' => $attributes
                            ],
                            'product' => [
                                'id' => $product->id,
                                'name' => $product->name,
                                'slug' => $product->slug,
                                'images' => $product->productPic->map(function ($pic) {
                                    return config('app.media_base_url') . $pic->imagePath;
                                })->toArray()
                            ]
                        ];
                    })->values();

                    $storeTotal = $storeItems->sum(function ($item) {
                        $price = $item['sale_price'] ? (float) str_replace('.', '', $item['sale_price']) : (float) str_replace('.', '', $item['price']);
                        return $price * $item['quantity'];
                    });

                    return [
                        'seller_id' => $seller->id,
                        'store_name' => $seller->store_name ?? 'N/A',
                        'store_url' => "/seller/{$seller->store_slug}",
                        'items' => $storeItems,
                        'store_total' => number_format($storeTotal, 0, ',', '.')
                    ];
                })->values();

                $cartTotal = $formattedStores->sum(function ($store) {
                    return (float) str_replace('.', '', $store['store_total']);
                });

                return [
                    'stores' => $formattedStores,
                    'total' => number_format($cartTotal, 0, ',', '.')
                ];
            });

            return $cartData;
        } catch (\Exception $e) {
            Log::error("Error in refreshCartCache for user {$userId}: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'message' => 'Lỗi khi làm mới cache giỏ hàng: ' . $e->getMessage(),
                'code' => 'CACHE_REFRESH_ERROR'
            ];
        }
    }

    public function index(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login.',
                    'code' => 'AUTH_REQUIRED'
                ], 401);
            }

            $userId = Auth::id();
            $selectedItemsKey = "cart_selected_items_{$userId}";
            $selectedItemIds = Cache::store('redis')->get($selectedItemsKey, []);

            // Refresh cart cache and get data
            $cartData = $this->refreshCartCache($userId, $selectedItemIds);

            if (isset($cartData['success']) && $cartData['success'] === false) {
                return response()->json($cartData, 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lấy giỏ hàng thành công',
                'data' => $cartData,
                'valid_item_ids' => $selectedItemIds
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error in cart index for user {$userId}: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy giỏ hàng: ' . $e->getMessage(),
                'code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    public function addItem(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login.',
                    'code' => 'AUTH_REQUIRED'
                ], 401);
            }

            $request->validate([
                'product_variant_id' => 'required|exists:product_variants,id',
                'quantity' => 'required|integer|min:1'
            ]);

            DB::beginTransaction();

            $productVariant = ProductVariant::findOrFail($request->product_variant_id);

            if ($productVariant->quantity < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Số lượng sản phẩm trong kho không đủ',
                    'code' => 'INSUFFICIENT_STOCK'
                ], 400);
            }

            $cart = Cart::where('user_id', Auth::id())
                ->where('status', 'active')
                ->first();

            if (!$cart) {
                $cart = Cart::create([
                    'user_id' => Auth::id(),
                    'status' => 'active'
                ]);
            }

            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('product_variant_id', $request->product_variant_id)
                ->first();

            if ($cartItem) {
                $newQuantity = $cartItem->quantity + $request->quantity;
                if ($newQuantity > $productVariant->quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Số lượng sản phẩm trong kho không đủ',
                        'code' => 'INSUFFICIENT_STOCK'
                    ], 400);
                }
                $cartItem->quantity = $newQuantity;
                $cartItem->save();
            } else {
                $cartItem = CartItem::create([
                    'cart_id' => $cart->id,
                    'product_variant_id' => $request->product_variant_id,
                    'quantity' => $request->quantity,
                    'price' => $productVariant->sale_price ?? $productVariant->price
                ]);
            }

            // Add new item to selected items
            $userId = Auth::id();
            $selectedItemsKey = "cart_selected_items_{$userId}";
            $selectedItemIds = Cache::store('redis')->get($selectedItemsKey, []);
            if (!in_array($cartItem->id, $selectedItemIds)) {
                $selectedItemIds[] = $cartItem->id;
                Cache::store('redis')->put($selectedItemsKey, $selectedItemIds, 86400);
            }

            // Refresh cache
            $cartData = $this->refreshCartCache($userId, $selectedItemIds);

            if (isset($cartData['success']) && $cartData['success'] === false) {
                DB::rollBack();
                return response()->json($cartData, 500);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Thêm vào giỏ hàng thành công',
                'data' => $cartData,
                'valid_item_ids' => $selectedItemIds
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in addItem: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi thêm vào giỏ hàng: ' . $e->getMessage(),
                'code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    public function updateItem(Request $request, $itemId)
    {
        try {
            $userId = Auth::id();
            $quantity = $request->input('quantity', 1);
            $cartItem = CartItem::where('id', $itemId)
                ->whereHas('cart', function ($query) use ($userId) {
                    $query->where('user_id', $userId)->where('status', 'active');
                })
                ->firstOrFail();

            $productVariant = ProductVariant::findOrFail($cartItem->product_variant_id);
            if ($quantity > $productVariant->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Số lượng vượt quá tồn kho',
                    'code' => 'INSUFFICIENT_STOCK'
                ], 400);
            }

            $cartItem->update(['quantity' => $quantity]);

            // Refresh cache
            $selectedItemsKey = "cart_selected_items_{$userId}";
            $selectedItemIds = Cache::store('redis')->get($selectedItemsKey, []);
            $cartData = $this->refreshCartCache($userId, $selectedItemIds);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật số lượng thành công',
                'data' => $cartData,
                'valid_item_ids' => $selectedItemIds
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error in updateItem for item {$itemId}: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật số lượng: ' . $e->getMessage(),
                'code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    public function removeItem($itemId)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login.',
                    'code' => 'AUTH_REQUIRED'
                ], 401);
            }

            DB::beginTransaction();

            $cartItem = CartItem::findOrFail($itemId);
            $cart = Cart::where('id', $cartItem->cart_id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$cart) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy giỏ hàng',
                    'code' => 'CART_NOT_FOUND'
                ], 404);
            }

            $cartItem->delete();

            // Update selected items cache
            $userId = Auth::id();
            $selectedItemsKey = "cart_selected_items_{$userId}";
            $selectedItemIds = Cache::store('redis')->get($selectedItemsKey, []);
            if (in_array($itemId, $selectedItemIds)) {
                $selectedItemIds = array_values(array_diff($selectedItemIds, [$itemId]));
                Cache::store('redis')->put($selectedItemsKey, $selectedItemIds, 86400);
            }

            // Refresh cache
            $cartData = $this->refreshCartCache($userId, $selectedItemIds);

            if (isset($cartData['success']) && $cartData['success'] === false) {
                DB::rollBack();
                return response()->json($cartData, 500);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Xóa sản phẩm khỏi giỏ hàng thành công',
                'data' => $cartData,
                'valid_item_ids' => $selectedItemIds
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in removeItem: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa sản phẩm khỏi giỏ hàng: ' . $e->getMessage(),
                'code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    public function clear()
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login.',
                    'code' => 'AUTH_REQUIRED'
                ], 401);
            }

            DB::beginTransaction();

            $cart = Cart::where('user_id', Auth::id())
                ->where('status', 'active')
                ->first();

            if ($cart) {
                $cart->items()->delete();
            }

            // Clear selected items cache
            $userId = Auth::id();
            $selectedItemsKey = "cart_selected_items_{$userId}";
            Cache::store('redis')->forget($selectedItemsKey);

            // Refresh cache
            $cartData = $this->refreshCartCache($userId);

            if (isset($cartData['success']) && $cartData['success'] === false) {
                DB::rollBack();
                return response()->json($cartData, 500);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Xóa giỏ hàng thành công',
                'data' => $cartData,
                'valid_item_ids' => []
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in clear: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa giỏ hàng: ' . $e->getMessage(),
                'code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    public function selectItems(Request $request)
    {
        try {
            $userId = Auth::id();
            $itemIds = $request->input('item_ids', []);
            $selectAll = $request->input('select_all', false);
            $cacheKey = "cart_selected_items_{$userId}";

            if ($selectAll) {
                $validItemIds = CartItem::whereHas('cart', function ($query) use ($userId) {
                    $query->where('user_id', $userId)->where('status', 'active');
                })->pluck('id')->toArray();
            } else {
                $validItemIds = CartItem::whereIn('id', $itemIds)
                    ->whereHas('cart', function ($query) use ($userId) {
                        $query->where('user_id', $userId)->where('status', 'active');
                    })
                    ->pluck('id')
                    ->toArray();
            }

            Cache::store('redis')->put($cacheKey, $validItemIds, 86400);

            // Refresh cart cache with updated selections
            $cartData = $this->refreshCartCache($userId, $validItemIds);

            if (isset($cartData['success']) && $cartData['success'] === false) {
                return response()->json($cartData, 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật danh sách sản phẩm đã chọn thành công',
                'data' => $cartData,
                'valid_item_ids' => $validItemIds
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error in selectItems for user {$userId}: {$e->getMessage()}", [
                'item_ids' => $itemIds,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật sản phẩm đã chọn: ' . $e->getMessage(),
                'code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    public function getSelectedItems(Request $request)
    {
        try {
            if (!Auth::check()) {
                Log::warning('Unauthorized access to getSelectedItems', ['ip' => $request->ip()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login.',
                    'code' => 'AUTH_REQUIRED'
                ], 401);
            }

            $userId = Auth::id();
            $redisKey = "cart_selected_items_{$userId}";
            $selectedItemIds = Cache::store('redis')->get($redisKey, []);

            // Validate selected item IDs
            $validItemIds = CartItem::whereIn('id', $selectedItemIds)
                ->whereHas('cart', function ($query) use ($userId) {
                    $query->where('user_id', $userId)->where('status', 'active');
                })
                ->pluck('id')
                ->toArray();

            if ($validItemIds !== $selectedItemIds) {
                Cache::store('redis')->put($redisKey, $validItemIds, 86400);
                Log::info("Updated Redis with valid selected items for user {$userId}", [
                    'valid_selected_items' => $validItemIds
                ]);
            }

            // Refresh cart cache with valid selected items
            $cartData = $this->refreshCartCache($userId, $validItemIds);

            if (isset($cartData['success']) && $cartData['success'] === false) {
                return response()->json($cartData, 500);
            }

            // Filter stores to include only selected items
            $cartData['stores'] = collect($cartData['stores'])->map(function ($store) use ($validItemIds) {
                $store['items'] = collect($store['items'])->filter(function ($item) use ($validItemIds) {
                    return in_array($item['id'], $validItemIds);
                })->values();
                if ($store['items']->isEmpty()) {
                    return null;
                }
                $store['store_total'] = number_format($store['items']->sum(function ($item) {
                    $price = $item['sale_price'] ? (float) str_replace('.', '', $item['sale_price']) : (float) str_replace('.', '', $item['price']);
                    return $price * $item['quantity'];
                }), 0, ',', '.');
                return $store;
            })->filter()->values();

            $cartData['total'] = number_format($cartData['stores']->sum(function ($store) {
                return (float) str_replace('.', '', $store['store_total']);
            }), 0, ',', '.');

            if ($cartData['stores']->isEmpty()) {
                Cache::store('redis')->forget($redisKey);
                return response()->json([
                    'success' => false,
                    'message' => 'Không có sản phẩm nào được chọn',
                    'data' => ['stores' => [], 'total' => '0'],
                    'valid_item_ids' => []
                ], 200);
            }

            Log::info("Successfully retrieved selected items for user {$userId}", [
                'stores_count' => count($cartData['stores']),
                'total' => $cartData['total']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách sản phẩm đã chọn thành công',
                'data' => $cartData,
                'valid_item_ids' => $validItemIds
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error in getSelectedItems for user {$userId}: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách sản phẩm đã chọn: ' . $e->getMessage(),
                'code' => 'SERVER_ERROR'
            ], 500);
        }
    }
}