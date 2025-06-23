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
            ->get()
            ->map(function($inv) {
                $productVariant = $inv->productVariant;
                $product = $productVariant?->product;
                $categoryName = $product?->categories?->first()?->name;
                $status = 'Hết hàng';
                if ($inv->quantity > 0 && $inv->quantity <= 5) {
                    $status = 'Gần hết';
                } elseif ($inv->quantity > 5) {
                    $status = 'Còn hàng';
                }
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
                    'status' => $status,
                ];
            });
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
            ->where('quantity', '>', 0)
            ->where('quantity', '<=', 5)
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
        $bestSellers = \App\Models\OrderItem::select('product_variant_id', \DB::raw('SUM(quantity) as total_sold'))
            ->whereHas('order', function($q) {
                $q->where('status', 'delivered');
            })
            ->groupBy('product_variant_id')
            ->orderByDesc('total_sold')
            ->with(['productVariant.product.categories', 'productVariant.inventories'])
            ->take(10)
            ->get()
            ->map(function($item) {
                $variant = $item->productVariant;
                $product = $variant?->product;
                $categoryName = $product?->categories?->first()?->name;
                // Lấy tổng tồn kho của variant
                $quantity = $variant?->inventories?->sum('quantity');
                // Lấy trạng thái tồn kho giống như list
                $status = 'Hết hàng';
                if ($quantity > 0 && $quantity <= 5) {
                    $status = 'Gần hết';
                } elseif ($quantity > 5) {
                    $status = 'Còn hàng';
                }
                return [
                    'product_name' => $product ? $product->name : '',
                    'variant_name' => $variant?->attributes?->pluck('name')->implode(', '),
                    'variant_sku' => $variant?->sku,
                    'category_name' => $categoryName,
                    'quantity' => $quantity,
                    'cost_price' => $variant?->cost_price,
                    'sell_price' => $variant?->sale_price ?? $variant?->price,
                    'status' => $status,
                    'total_sold' => $item->total_sold,
                ];
            });
        return response()->json($bestSellers);
    }
}