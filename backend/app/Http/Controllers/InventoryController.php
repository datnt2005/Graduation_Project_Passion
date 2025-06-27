<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\ProductVariant;
use App\Models\Order;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function update(Request $request, $inventoryId)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
            'location' => 'required|string|max:255',
        ]);

        // Cập nhật tồn kho
        $inventory = Inventory::findOrFail($inventoryId);
        $inventory->update([
            'quantity' => $validated['quantity'],
            'location' => $validated['location'],
            'last_updated' => now(),
        ]);

        // Cập nhật quantity trong product_variants
        $variant = $inventory->variant;
        $totalQuantity = $variant->inventories()->sum('quantity');
        $variant->update(['quantity' => $totalQuantity]);

        return response()->json(['message' => 'Inventory updated successfully', 'inventory' => $inventory]);
    }

    public function list(Request $request)
    {
        $inventories = \App\Models\Inventory::with(['productVariant.product.categories'])
            ->whereHas('productVariant.product', function($query) {
                $query->where('status', '!=', 'trash');
            })
            ->get()
            ->groupBy('productVariant.product_id') // Group by main product ID
            ->map(function($groupedInventories) {
                $firstInventory = $groupedInventories->first();
                $product = $firstInventory->productVariant->product;
                $categoryName = $product->categories->first()?->name;
                
                // Calculate total quantity for all variants
                $totalQuantity = $groupedInventories->sum('quantity');
                
                // Get the first variant's prices
                $firstVariant = $firstInventory->productVariant;
                $costPrice = $firstVariant->cost_price ?? 0;
                $sellPrice = $firstVariant->price ?? 0;
                
                // Debug log
                \Log::info('Product Price Debug', [
                    'product_name' => $firstVariant->product->name,
                    'sale_price' => $firstVariant->sale_price,
                    'price' => $firstVariant->price,
                    'variant_id' => $firstVariant->id
                ]);
                
                // Determine status based on total quantity
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
                    'cost_price' => round($costPrice),
                    'sell_price' => round($sellPrice),
                    'category_name' => $categoryName,
                    'status' => $status,
                ];
            })
            ->values(); // Convert to array and reindex
        return response()->json($inventories);
    }

    /**
     * Deduct inventory quantities for an order's items
     */
    public function deductInventoryForOrder(Order $order)
    {
        try {
            foreach ($order->orderItems as $item) {
                $variant = $item->productVariant;
                if ($variant) {
                    $quantityToDeduct = $item->quantity;
                    $inventories = $variant->inventories()->orderBy('id')->get();
                    foreach ($inventories as $inventory) {
                        if ($quantityToDeduct <= 0) break;
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
        $inventories = \App\Models\Inventory::with(['productVariant.product.categories'])
            ->where(function($query) {
                $query->where('quantity', '<=', 5); // Bao gồm cả sản phẩm hết hàng và gần hết hàng
            })
            ->whereHas('productVariant.product', function($query) {
                $query->where('status', '!=', 'trash');
            })
            ->get()
            ->map(function($inv) {
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
    public function bestSellers()
    {
        try {
            $bestSellers = \App\Models\OrderItem::select('product_variant_id', \DB::raw('SUM(quantity) as total_sold'))
                ->whereHas('order', function($q) {
                    $q->where('status', 'delivered');
                })
                ->whereHas('productVariant.product', function($query) {
                    $query->where('status', '!=', 'trash');
                })
                ->with(['productVariant.product.categories', 'productVariant.inventories'])
                ->groupBy('product_variant_id')
                ->get()
                ->groupBy('productVariant.product_id') // Group by main product ID
                ->map(function($items) {
                    $firstItem = $items->first();
                    $product = $firstItem->productVariant->product;
                    $categoryName = $product->categories->first()?->name;
                    
                    // Calculate total quantity for all variants
                    $totalQuantity = $items->reduce(function($carry, $item) {
                        return $carry + $item->productVariant->inventories->sum('quantity');
                    }, 0);
                    
                    // Calculate total sold for all variants
                    $totalSold = $items->sum('total_sold');
                    
                    // Get first variant's prices
                    $firstVariant = $firstItem->productVariant;
                    $costPrice = $firstVariant->cost_price ?? 0;
                    $sellPrice = $firstVariant->price ?? 0;
                    
                    // Determine status based on total quantity
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
                        'cost_price' => round($costPrice),
                        'sell_price' => round($sellPrice),
                        'status' => $status,
                        'total_sold' => $totalSold,
                    ];
                })
                ->sortByDesc('total_sold')
                ->take(10)
                ->values();
                
            return response()->json($bestSellers);
        } catch (\Exception $e) {
            \Log::error('Best Sellers Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Không thể tải dữ liệu sản phẩm bán chạy!'], 500);
        }
    }
}