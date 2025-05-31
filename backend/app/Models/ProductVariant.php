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
    public function attributes(){
        return $this->hasMany(VariantAttribute::class, 'product_variant_id', 'id');
    }
    
    public function product(){
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }   
    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'product_variant_id', 'id');
    }
}
