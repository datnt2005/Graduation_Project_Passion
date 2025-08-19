<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\ProductVariant;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\InventoryService;
use App\Models\StockMovement;

class InventoryController extends Controller
{
    public function update(Request $request, $inventoryId)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
            'location' => 'required|string|max:255',
        ]);

        $inventory = Inventory::findOrFail($inventoryId);

        $diffQuantity = $validated['quantity'] - $inventory->quantity;

        // Nếu có sự thay đổi về số lượng
        if ($diffQuantity !== 0) {
            $actionType = $diffQuantity > 0 ? 'import' : 'adjust';

            // Lấy người thực hiện
            $user = auth()->user();
            $createdBy = $user?->id ?? 'system';
            $createdByType = $user && $user->role === 'seller' ? 'seller' : 'admin';

            // Ghi lại biến động tồn kho
            $service = new InventoryService();
            $service->adjustStock(
                $inventory->product_variant_id,
                abs($diffQuantity),
                $actionType,
                'Điều chỉnh tồn kho thủ công',
                $createdBy,
                $createdByType
            );
        }

        // Cập nhật tồn kho
        $inventory->update([
            'quantity' => $validated['quantity'],
            'location' => $validated['location'],
            'last_updated' => now(),
        ]);

        // Cập nhật tồn kho tổng cho product_variant
        $variant = $inventory->variant;
        if ($variant) {
            $variant->quantity = $variant->inventories()->sum('quantity');
            $variant->save();
        }

        return response()->json([
            'message' => 'Cập nhật tồn kho thành công!',
            'inventory' => $inventory
        ]);
    }


  public function list(Request $request)
{
    $user = $request->user();
    if (!$user) {
        return response()->json(['error' => 'Chưa đăng nhập!'], 401);
    }

    $isAdmin = $user->role === 'admin';
    $seller = \App\Models\Seller::where('user_id', $user->id)->first();

    // Nếu không phải admin và cũng không phải seller thì chặn
    if (!$isAdmin && !$seller) {
        return response()->json(['error' => 'Bạn không có quyền truy cập!'], 403);
    }

    $inventories = Inventory::with([
        'productVariant.product.categories',
        'productVariant.attributes',
    ])
        ->when(!$isAdmin, function ($query) use ($seller) {
            // Nếu là seller, lọc sản phẩm theo seller_id
            $query->whereHas('productVariant.product', function ($q) use ($seller) {
                $q->where('seller_id', $seller->id)
                    ->where('status', '!=', 'trash');
            });
        })
        ->get()
        ->map(function ($inventory) {
            $variant = $inventory->productVariant;
            $product = $variant->product;
            $categoryName = $product->categories->first()?->name;

            $status = 'Hết hàng';
            if ($inventory->quantity > 0 && $inventory->quantity <= 5) {
                $status = 'Gần hết';
            } elseif ($inventory->quantity > 5) {
                $status = 'Còn hàng';
            }

            $attributes = $variant->attributes->map(function ($attr) {
                $value = null;
                if ($attr->pivot instanceof \App\Models\AttributeValueProductVariant) {
                    $value = optional($attr->pivot->value)->value;
                }

                return [
                    'name' => $attr->name,
                    'value' => $value,
                ];
            });

            return [
                'id' => $inventory->id,
                'product_variant_id' => $variant->id,
                'sku' => $variant->sku,
                'product_name' => $product->name,
                'quantity' => $inventory->quantity,
                'cost_price' => round($variant->cost_price),
                'sell_price' => round($variant->price),
                'location' => $inventory->location,
                'last_updated' => $inventory->last_updated,
                'created_at' => $inventory->created_at,
                'updated_at' => $inventory->updated_at,
                'note' => $inventory->note,
                'batch_number' => $inventory->batch_number,
                'imported_at' => $inventory->imported_at,
                'imported_by' => $inventory->imported_by,
                'import_source' => $inventory->import_source,
                'is_locked' => $inventory->is_locked,
                'category_name' => $categoryName,
                'status' => $status,
                'attributes' => $attributes,
            ];
        });

    return response()->json($inventories->values());
}


    public function deductInventoryForOrder(Order $order)
    {
        try {
            foreach ($order->orderItems as $item) {
                $variant = $item->productVariant;
                if ($variant) {
                    $quantityToDeduct = $item->quantity;
                    $inventories = $variant->inventories()->orderBy('id')->get();
                    foreach ($inventories as $inventory) {
                        if ($quantityToDeduct <= 0)
                            break;
                        $deduct = min($inventory->quantity, $quantityToDeduct);
                        $inventory->quantity -= $deduct;
                        $inventory->last_updated = now();
                        $inventory->save();
                        $quantityToDeduct -= $deduct;
                    }
                    // Sau khi trừ, cập nhật lại tổng quantity cho variant
                    $totalQuantity = $variant->inventories()->sum('quantity');
                    $variant->quantity = $totalQuantity;
                    $variant->save();

                    StockMovement::create([
                        'product_variant_id' => $variant->id,
                        'action_type' => 'export',
                        'quantity' => $item->quantity,
                        'note' => 'Xuất kho cho đơn hàng #' . $order->id,
                        'created_by' => 'system',
                        'created_by_type' => 'system',
                    ]);

                }
            }
            return true;
        } catch (\Exception $e) {
            \Log::error('Inventory Deduction Error:', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Lấy danh sách sản phẩm gần hết hàng (quantity <= 5)
     */
    public function lowStock()
    {
        $inventories = Inventory::with(['productVariant.product.categories'])
            ->where(function ($query) {
                $query->where('quantity', '<=', 5); // Bao gồm cả sản phẩm hết hàng và gần hết hàng
            })
            ->whereHas('productVariant.product', function ($query) {
                $query->where('status', '!=', 'trash');
            })
            ->get()
            ->map(function ($inv) {
                $productVariant = $inv->productVariant;
                $product = $productVariant?->product;
                $categoryName = $product?->categories?->first()?->name;
                return [
                    'id' => $inv->id,
                    'product_name' => $product ? $product->name : '',
                    'variant_sku' => $productVariant ? $productVariant->sku : '',
                    'variant_name' => $productVariant?->attributes?->pluck('name')->implode(', '),
                    'quantity' => $inv->quantity,
                    'location' => $inv->location,
                    'last_updated' => $inv->last_updated,
                    'cost_price' => $productVariant?->cost_price,
                    'sell_price' => $productVariant?->sale_price ?? $productVariant?->price,
                    'category_name' => $categoryName,
                ];
            });
        return response()->json($inventories);
    }

    /**
     * Lấy danh sách sản phẩm bán chạy nhất dựa theo số lượng bán ra (order_items)
     */
    public function bestSellers(Request $request)
    {
        $user = $request->user();
        $seller = \App\Models\Seller::where('user_id', $user->id)->first();
        $bestSellers = \App\Models\OrderItem::select('product_variant_id', \DB::raw('SUM(quantity) as total_sold'))
            ->whereHas('order', function ($q) {
                $q->where('status', 'delivered');
            })
            ->whereHas('productVariant.product', function ($query) use ($seller) {
                $query->where('seller_id', $seller->id)
                    ->where('status', '!=', 'trash');
            })
            ->with(['productVariant.product.categories', 'productVariant.inventories'])
            ->groupBy('product_variant_id')
            ->get()
            ->groupBy('productVariant.product_id')
            ->map(function ($items) {
                $firstItem = $items->first();
                $product = $firstItem->productVariant->product;
                $categoryName = $product->categories->first()?->name;
                $totalQuantity = $items->reduce(function ($carry, $item) {
                    return $carry + $item->productVariant->inventories->sum('quantity');
                }, 0);
                $totalSold = $items->sum('total_sold');
                $firstVariant = $firstItem->productVariant;
                $costPrice = $firstVariant->cost_price ?? 0;
                $sellPrice = $firstVariant->price ?? 0;
                $status = 'Hết hàng';
                if ($totalQuantity > 0 && $totalQuantity <= 5) {
                    $status = 'Gần hết';
                } elseif ($totalQuantity > 5) {
                    $status = 'Còn hàng';
                }
                return [
                    'product_name' => $product->name,
                    'category_name' => $categoryName,
                    'quantity' => $totalQuantity,
                    'total_sold' => $totalSold,
                    'cost_price' => round($costPrice),
                    'sell_price' => round($sellPrice),
                    'status' => $status,
                ];
            })
            ->values();
        return response()->json($bestSellers);
    }

 public function stockHistory(Request $request)
{
    $user = $request->user();
    if (!$user || !in_array($user->role, ['admin', 'seller'])) {
        return response()->json(['error' => 'Bạn không có quyền truy cập.'], 403);
    }

    $isAdmin = $user->role === 'admin';

    $query = StockMovement::with(['productVariant.product', 'creator', 'productVariant.inventories'])
        ->when(
            $request->filled('product_variant_id'),
            fn($q) => $q->where('product_variant_id', $request->product_variant_id)
        )
        ->when(
            !$isAdmin,
            function ($q) use ($user) {
                $seller = \App\Models\Seller::where('user_id', $user->id)->first();
                if ($seller) {
                    // Lọc theo sản phẩm của seller
                    $q->whereHas('productVariant.product', function ($query) use ($seller) {
                        $query->where('seller_id', $seller->id);
                    });
                }
            }
        )
        ->orderByDesc('created_at');

    $movements = $query->paginate(20);

    return response()->json($movements);
}





    public function markDamagedOrExport(Request $request, Inventory $inventory)
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, ['admin', 'seller'])) {
            return response()->json(['error' => 'Bạn không có quyền thực hiện thao tác này.'], 403);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string',
            'action_type' => 'required|in:damage,export', // 👈 Xác định loại hành động
        ]);

        if ($inventory->quantity < $validated['quantity']) {
            return response()->json(['error' => 'Không đủ số lượng để thực hiện thao tác'], 400);
        }

        // Trừ số lượng tồn kho
        $inventory->decrement('quantity', $validated['quantity']);
        $inventory->last_updated = now();
        $inventory->save();

        // Cập nhật tồn kho tổng cho product_variant
        $variant = $inventory->productVariant;
        if ($variant) {
            $variant->quantity = $variant->inventories()->sum('quantity');
            $variant->save();
        }

        // Lưu lịch sử biến động kho
        StockMovement::create([
            'product_variant_id' => $inventory->product_variant_id,
            'action_type' => $validated['action_type'], // 👈 damage hoặc export
            'quantity' => $validated['quantity'],
            'note' => $validated['note'] ?? ($validated['action_type'] === 'damage' ? 'Hàng lỗi' : 'Xuất kho'),
            'created_by' => $user->id,
            'created_by_type' => $user->role,
        ]);

        return response()->json([
            'message' => $validated['action_type'] === 'damage'
                ? 'Đã đánh dấu hàng lỗi'
                : 'Đã xuất kho thành công'
        ]);
    }



    public function import(Request $request)
    {
        $validated = $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'batch_number' => 'nullable|string|max:255',
            'import_source' => 'nullable|string|max:255',
        ]);

        $user = $request->user();
        $createdBy = $user?->id ?? 'system';
        $createdByType = $user && $user->role === 'seller' ? 'seller' : 'admin';
        $importedByName = $user?->name ?? 'system';

        // Tìm hoặc tạo inventory
// 📝 Tìm inventory cũ khớp cả 3: variant + location + batch_number
        $inventory = Inventory::where('product_variant_id', $validated['product_variant_id'])
            ->where('location', $validated['location'] ?? 'Kho mặc định')
            ->where('batch_number', $validated['batch_number'] ?? null)
            ->where('status', 'available')
            ->where('is_locked', false)
            ->first();

        if ($inventory) {
            // 📝 Nếu tìm thấy => cập nhật số lượng và thông tin
            $inventory->increment('quantity', $validated['quantity']);
            $inventory->last_updated = now();
            $inventory->imported_at = now();
            $inventory->imported_by = $importedByName;

            if (isset($validated['note'])) {
                $inventory->note = $validated['note'];
            }
            if (isset($validated['import_source'])) {
                $inventory->import_source = $validated['import_source'];
            }

            $inventory->save();
        } else {
            // 📝 Nếu không có => tạo inventory mới
            $inventory = Inventory::create([
                'product_variant_id' => $validated['product_variant_id'],
                'quantity' => $validated['quantity'],
                'location' => $validated['location'] ?? 'Kho mặc định',
                'batch_number' => $validated['batch_number'] ?? null,
                'import_source' => $validated['import_source'] ?? null,
                'note' => $validated['note'] ?? null,
                'status' => 'available',
                'is_locked' => false,
                'imported_by' => $importedByName,
                'imported_at' => now(),
                'last_updated' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Cập nhật tổng tồn kho cho biến thể sản phẩm
        $variant = ProductVariant::findOrFail($validated['product_variant_id']);
        $variant->quantity = $variant->inventories()->sum('quantity');
        $variant->save();

        // Lưu lịch sử nhập kho
        StockMovement::create([
            'product_variant_id' => $validated['product_variant_id'],
            'action_type' => 'import',
            'quantity' => $validated['quantity'],
            'note' => $validated['note'] ?? 'Nhập kho',
            'created_by' => $createdBy,
            'created_by_type' => $createdByType,
        ]);

        return response()->json(['message' => 'Nhập kho thành công!']);
    }

    public function sellerList(Request $request)
    {
        try {
            $user = $request->user();
            $seller = \App\Models\Seller::where('user_id', $user->id)->first();
            if (!$seller) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không phải seller hoặc chưa đăng nhập!'
                ], 403);
            }

            // Lấy tất cả sản phẩm của seller (không lấy sản phẩm đã xóa)
            $products = \App\Models\Product::with(['productVariants.inventories', 'categories'])
                ->where('seller_id', $seller->id)
                ->where('status', '!=', 'trash')
                ->get();

            $inventories = $products->map(function ($product) {
                $variants = $product->productVariants;
                $totalQuantity = 0;
                $totalCost = 0;
                $totalSell = 0;
                $variantCount = 0;
                foreach ($variants as $variant) {
                    $quantity = $variant->inventories->sum('quantity');
                    $totalQuantity += $quantity;
                    $totalCost += ($variant->cost_price ?? 0);
                    $totalSell += ($variant->price ?? 0);
                    $variantCount++;
                }
                $avgCost = $variantCount > 0 ? round($totalCost / $variantCount) : 0;
                $avgSell = $variantCount > 0 ? round($totalSell / $variantCount) : 0;
                $categoryName = $product->categories->first()?->name;
                $status = 'Hết hàng';
                if ($totalQuantity > 0 && $totalQuantity <= 5) {
                    $status = 'Gần hết';
                } elseif ($totalQuantity > 5) {
                    $status = 'Còn hàng';
                }
                return [
                    'id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $totalQuantity,
                    'cost_price' => $avgCost,
                    'sell_price' => $avgSell,
                    'category_name' => $categoryName,
                    'status' => $status
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $inventories->values()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách tồn kho: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sellerBestSellers(Request $request)
    {
        try {
            $user = $request->user();
            $seller = \App\Models\Seller::where('user_id', $user->id)->first();
            if (!$seller) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không phải seller hoặc chưa đăng nhập!'
                ], 403);
            }

            $bestSellers = \App\Models\OrderItem::select('product_variant_id', \DB::raw('SUM(quantity) as total_sold'))
                ->whereHas('order', function ($q) {
                    $q->where('status', 'delivered');
                })
                ->whereHas('productVariant.product', function ($query) use ($seller) {
                    $query->where('seller_id', $seller->id)
                        ->where('status', '!=', 'trash');
                })
                ->with(['productVariant.product.categories', 'productVariant.inventories'])
                ->groupBy('product_variant_id')
                ->get()
                ->groupBy('productVariant.product_id')
                ->map(function ($items) {
                    $firstItem = $items->first();
                    $product = $firstItem->productVariant->product;
                    $categoryName = $product->categories->first()?->name;
                    $totalQuantity = $items->reduce(function ($carry, $item) {
                        return $carry + $item->productVariant->inventories->sum('quantity');
                    }, 0);
                    $totalSold = $items->sum('total_sold');
                    $firstVariant = $firstItem->productVariant;
                    $costPrice = $firstVariant->cost_price ?? 0;
                    $sellPrice = $firstVariant->price ?? 0;
                    $status = 'Hết hàng';
                    if ($totalQuantity > 0 && $totalQuantity <= 5) {
                        $status = 'Gần hết';
                    } elseif ($totalQuantity > 5) {
                        $status = 'Còn hàng';
                    }
                    return [
                        'product_name' => $product->name,
                        'category_name' => $categoryName,
                        'quantity' => $totalQuantity,
                        'total_sold' => $totalSold,
                        'cost_price' => round($costPrice),
                        'sell_price' => round($sellPrice),
                        'status' => $status,
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'data' => $bestSellers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách sản phẩm bán chạy: ' . $e->getMessage()
            ], 500);
        }
    }


}
