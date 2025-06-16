<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;
    protected $table = 'product_variants';
    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'cost_price',
        'sale_price',
        'quantity',
        'thumbnail',
    ];
    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'variant_attributes')
                    ->withPivot('value_id')
                    ->withTimestamps();
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function inventories()
        {
            return $this->hasMany(Inventory::class, 'product_variant_id')->from('inventory');
        }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_variant_id');
    }
    
}
