<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Điều chỉnh kho hàng và ghi nhận lịch sử biến động.
     */
    public function adjustStock(
        int $productVariantId,
        int $quantity,
        string $actionType,       // import, export, adjust, damage, return
        string $note = null,
        $createdBy = null,
        string $createdByType = 'system'
    ): bool {
        return DB::transaction(function () use (
            $productVariantId, $quantity, $actionType, $note, $createdBy, $createdByType
        ) {
            // Lấy kho hợp lệ
            $inventory = Inventory::where('product_variant_id', $productVariantId)
                ->where('status', 'available')
                ->where('is_locked', false)
                ->first();

            if (!$inventory) {
                throw new \Exception("Không tìm thấy tồn kho hợp lệ.");
            }

            // Kiểm tra khi xuất/trừ kho
            if (in_array($actionType, ['export', 'adjust', 'damage', 'return'])) {
                if ($inventory->quantity < $quantity) {
                    throw new \Exception("Không đủ hàng trong kho.");
                }
                $inventory->quantity -= $quantity;
            } else {
                $inventory->quantity += $quantity;
            }

            $inventory->last_updated = now();
            $inventory->save();

            // Cập nhật tổng tồn kho cho biến thể
            $inventory->variant->update([
                'quantity' => $inventory->variant->inventories()->sum('quantity')
            ]);

            // Ghi lại lịch sử
            StockMovement::create([
                'product_variant_id' => $productVariantId,
                'action_type'        => $actionType,
                'quantity'           => $quantity,
                'note'               => $note,
                'created_by'         => $createdBy,
                'created_by_type'    => $createdByType,
            ]);

            return true;
        });
    }
}
