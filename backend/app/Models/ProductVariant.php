<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Attribute;
use App\Models\Product;

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
            ->using(AttributeValueProductVariant::class)
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

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'variant_attributes', 'product_variant_id', 'value_id')
            ->using(AttributeValueProductVariant::class)
            ->withPivot('attribute_id')
            ->withTimestamps();
    }
}
