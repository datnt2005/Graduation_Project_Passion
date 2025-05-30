<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventory';

    protected $fillable = [
        'product_variant_id',
        'quantity',
        'location',
        'last_updated',
    ];

    /**
     * Mối quan hệ với product_variant
     */
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Đồng bộ quantity với product_variants sau khi tạo hoặc cập nhật
     */
    protected static function booted()
    {
        static::created(function ($inventory) {
            $inventory->syncVariantQuantity();
        });

        static::updated(function ($inventory) {
            $inventory->syncVariantQuantity();
        });
    }

    /**
     * Hàm đồng bộ quantity trong product_variants
     */
    public function syncVariantQuantity()
    {
        $variant = $this->variant;
        if ($variant) {
            $totalQuantity = $variant->inventories()->sum('quantity');
            $variant->update(['quantity' => $totalQuantity]);
        }
    }
}