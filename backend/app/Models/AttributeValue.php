<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class AttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_id',
        'value',
    ];

    // Quan hệ với Attribute
    public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'attribute_id' , 'id');
    }


    public function productVariants()
{
    return $this->belongsToMany(ProductVariant::class, 'attribute_value_product_variant');
}

}
