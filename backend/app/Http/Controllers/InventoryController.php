<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\ProductVariant;
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
}