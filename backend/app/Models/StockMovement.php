<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_variant_id',
        'action_type',
        'quantity',
        'note',
        'created_by',
        'created_by_type',
    ];

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
    public function creator()
{
    return $this->belongsTo(\App\Models\User::class, 'created_by');
}

}
