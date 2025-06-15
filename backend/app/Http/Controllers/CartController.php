<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        try {
            \Log::info('Cart index accessed');
            
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login.',
                    'code' => 'AUTH_REQUIRED'
                ], 401);
            }

            $cart = Cart::with(['items.productVariant.product'])
                ->where('user_id', Auth::id())
                ->where('status', 'active')
                ->first();

            if (!$cart) {
                return response()->json([
                    'success' => true,
                    'message' => 'Giỏ hàng trống',
                    'data' => [
                        'items' => [],
                        'total' => 0
                    ]
                ]);
            }

            $total = $cart->items->sum(function ($item) {
                return $item->price * $item->quantity;
            });

            // Debug log
            \Log::info('Cart items:', $cart->items->toArray());

            $formattedItems = $cart->items->map(function($item) {
                $variant = $item->productVariant;
                $product = $variant ? $variant->product : null;
                
                return [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'product_variant_id' => $item->product_variant_id,
                    'productVariant' => [
                        'id' => $variant ? $variant->id : null,
                        'thumbnail' => $variant ? $variant->thumbnail : null,
                        'product' => [
                            'id' => $product ? $product->id : null,
                            'name' => $product ? $product->name : 'Sản phẩm không xác định'
                        ]
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Lấy giỏ hàng thành công',
                'data' => [
                    'items' => $formattedItems,
                    'total' => $total
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in cart index: ' . $e->getMessage(), [
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

            // Get or create cart for authenticated user
            $cart = Cart::where('user_id', Auth::id())
                ->where('status', 'active')
                ->first();
            
            if (!$cart) {
                $cart = Cart::create([
                    'user_id' => Auth::id(),
                    'status' => 'active'
                ]);
            }

            // Check if item already exists in cart
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
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_variant_id' => $request->product_variant_id,
                    'quantity' => $request->quantity,
                    'price' => $productVariant->sale_price ?? $productVariant->price
                ]);
            }

            DB::commit();

            // Refresh cart data
            $cart = Cart::with(['items.productVariant.product'])
                ->find($cart->id);

            return response()->json([
                'success' => true,
                'message' => 'Thêm vào giỏ hàng thành công',
                'data' => [
                    'cart' => [
                        'id' => $cart->id,
                        'user_id' => $cart->user_id,
                        'status' => $cart->status,
                        'items_count' => $cart->items->count(),
                        'total' => $cart->items->sum(function($item) {
                            return $item->price * $item->quantity;
                        })
                    ],
                    'items' => $cart->items
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
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
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login.',
                    'code' => 'AUTH_REQUIRED'
                ], 401);
            }

            $request->validate([
                'quantity' => 'required|integer|min:1'
            ]);

            DB::beginTransaction();

            $cartItem = CartItem::findOrFail($itemId);
            
            // Verify cart ownership
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

            $productVariant = ProductVariant::findOrFail($cartItem->product_variant_id);
            
            if ($productVariant->quantity < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Số lượng sản phẩm trong kho không đủ',
                    'code' => 'INSUFFICIENT_STOCK'
                ], 400);
            }

            $cartItem->quantity = $request->quantity;
            $cartItem->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật giỏ hàng thành công'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật giỏ hàng: ' . $e->getMessage(),
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

            $cartItem = CartItem::findOrFail($itemId);
            
            // Verify cart ownership
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

            return response()->json([
                'success' => true,
                'message' => 'Xóa sản phẩm khỏi giỏ hàng thành công'
            ]);
        } catch (\Exception $e) {
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

            $cart = Cart::where('user_id', Auth::id())
                ->where('status', 'active')
                ->first();

            if ($cart) {
                $cart->items()->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Xóa giỏ hàng thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa giỏ hàng: ' . $e->getMessage(),
                'code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    public function getRedisCart($cartId)
    {
        try {
            $cartData = Redis::get("cart:{$cartId}");
            
            if (!$cartData) {
                return response()->json([
                    'success' => true,
                    'message' => 'Giỏ hàng trống',
                    'data' => [
                        'items' => [],
                        'total' => 0
                    ]
                ]);
            }

            $cartItems = json_decode($cartData, true);
            $total = collect($cartItems)->sum(function ($item) {
                return $item['price'] * $item['quantity'];
            });

            return response()->json([
                'success' => true,
                'message' => 'Lấy giỏ hàng thành công',
                'data' => [
                    'items' => $cartItems,
                    'total' => $total
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getRedisCart: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy giỏ hàng: ' . $e->getMessage(),
                'code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    public function addToRedisCart(Request $request, $cartId)
    {
        try {
            $request->validate([
                'product_variant_id' => 'required|exists:product_variants,id',
                'quantity' => 'required|integer|min:1'
            ]);

            $productVariant = ProductVariant::findOrFail($request->product_variant_id);
            
            if ($productVariant->quantity < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Số lượng sản phẩm trong kho không đủ',
                    'code' => 'INSUFFICIENT_STOCK'
                ], 400);
            }

            $cartData = Redis::get("cart:{$cartId}");
            $cartItems = $cartData ? json_decode($cartData, true) : [];

            // Check if item already exists
            $itemIndex = collect($cartItems)->search(function ($item) use ($request) {
                return $item['product_variant_id'] == $request->product_variant_id;
            });

            if ($itemIndex !== false) {
                $newQuantity = $cartItems[$itemIndex]['quantity'] + $request->quantity;
                if ($newQuantity > $productVariant->quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Số lượng sản phẩm trong kho không đủ',
                        'code' => 'INSUFFICIENT_STOCK'
                    ], 400);
                }
                $cartItems[$itemIndex]['quantity'] = $newQuantity;
            } else {
                $cartItems[] = [
                    'id' => count($cartItems) + 1,
                    'product_variant_id' => $request->product_variant_id,
                    'quantity' => $request->quantity,
                    'price' => $productVariant->sale_price ?? $productVariant->price,
                    'productVariant' => [
                        'id' => $productVariant->id,
                        'thumbnail' => $productVariant->thumbnail,
                        'product' => [
                            'id' => $productVariant->product->id,
                            'name' => $productVariant->product->name
                        ]
                    ]
                ];
            }

            Redis::set("cart:{$cartId}", json_encode($cartItems));
            Redis::expire("cart:{$cartId}", 60 * 60 * 24 * 30); // 30 days

            return response()->json([
                'success' => true,
                'message' => 'Thêm vào giỏ hàng thành công'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in addToRedisCart: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi thêm vào giỏ hàng: ' . $e->getMessage(),
                'code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    public function updateRedisCartItem(Request $request, $cartId, $itemId)
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:1'
            ]);

            $cartData = Redis::get("cart:{$cartId}");
            if (!$cartData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy giỏ hàng',
                    'code' => 'CART_NOT_FOUND'
                ], 404);
            }

            $cartItems = json_decode($cartData, true);
            $itemIndex = collect($cartItems)->search(function ($item) use ($itemId) {
                return $item['id'] == $itemId;
            });

            if ($itemIndex === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy sản phẩm trong giỏ hàng',
                    'code' => 'ITEM_NOT_FOUND'
                ], 404);
            }

            $productVariant = ProductVariant::findOrFail($cartItems[$itemIndex]['product_variant_id']);
            
            if ($productVariant->quantity < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Số lượng sản phẩm trong kho không đủ',
                    'code' => 'INSUFFICIENT_STOCK'
                ], 400);
            }

            $cartItems[$itemIndex]['quantity'] = $request->quantity;
            Redis::set("cart:{$cartId}", json_encode($cartItems));
            Redis::expire("cart:{$cartId}", 60 * 60 * 24 * 30); // 30 days

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật giỏ hàng thành công'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in updateRedisCartItem: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật giỏ hàng: ' . $e->getMessage(),
                'code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    public function removeRedisCartItem($cartId, $itemId)
    {
        try {
            $cartData = Redis::get("cart:{$cartId}");
            if (!$cartData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy giỏ hàng',
                    'code' => 'CART_NOT_FOUND'
                ], 404);
            }

            $cartItems = json_decode($cartData, true);
            $cartItems = collect($cartItems)->filter(function ($item) use ($itemId) {
                return $item['id'] != $itemId;
            })->values()->all();

            Redis::set("cart:{$cartId}", json_encode($cartItems));
            Redis::expire("cart:{$cartId}", 60 * 60 * 24 * 30); // 30 days

            return response()->json([
                'success' => true,
                'message' => 'Xóa sản phẩm khỏi giỏ hàng thành công'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in removeRedisCartItem: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa sản phẩm khỏi giỏ hàng: ' . $e->getMessage(),
                'code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    public function clearRedisCart($cartId)
    {
        try {
            Redis::del("cart:{$cartId}");
            return response()->json([
                'success' => true,
                'message' => 'Xóa giỏ hàng thành công'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in clearRedisCart: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa giỏ hàng: ' . $e->getMessage(),
                'code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    public function mergeRedisCart($cartId)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login.',
                    'code' => 'AUTH_REQUIRED'
                ], 401);
            }

            $cartData = Redis::get("cart:{$cartId}");
            if (!$cartData) {
                return response()->json([
                    'success' => true,
                    'message' => 'Không có sản phẩm để đồng bộ'
                ]);
            }

            $redisCartItems = json_decode($cartData, true);
            
            DB::beginTransaction();

            // Get or create user cart
            $cart = Cart::where('user_id', Auth::id())
                ->where('status', 'active')
                ->first();
            
            if (!$cart) {
                $cart = Cart::create([
                    'user_id' => Auth::id(),
                    'status' => 'active'
                ]);
            }

            // Merge items
            foreach ($redisCartItems as $item) {
                $productVariant = ProductVariant::findOrFail($item['product_variant_id']);
                
                // Check if item exists in user cart
                $cartItem = CartItem::where('cart_id', $cart->id)
                    ->where('product_variant_id', $item['product_variant_id'])
                    ->first();

                if ($cartItem) {
                    $newQuantity = $cartItem->quantity + $item['quantity'];
                    if ($newQuantity > $productVariant->quantity) {
                        $newQuantity = $productVariant->quantity;
                    }
                    $cartItem->quantity = $newQuantity;
                    $cartItem->save();
                } else {
                    CartItem::create([
                        'cart_id' => $cart->id,
                        'product_variant_id' => $item['product_variant_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price']
                    ]);
                }
            }

            DB::commit();

            // Clear Redis cart
            Redis::del("cart:{$cartId}");

            return response()->json([
                'success' => true,
                'message' => 'Đồng bộ giỏ hàng thành công'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in mergeRedisCart: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi đồng bộ giỏ hàng: ' . $e->getMessage(),
                'code' => 'SERVER_ERROR'
            ], 500);
        }
    }
} 