<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;

    protected $table = 'discounts';

    protected $fillable = [
        'name',
        'code',
        'description',
        'discount_type',
        'discount_value',
        'usage_limit',
        'used_count',
        'min_order_value',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'discount_type' => 'string',
        'status' => 'string',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    // Relationships
    public function products()
    {
        return $this->hasMany(DiscountProduct::class);
    }

    public function categories()
    {
        return $this->hasMany(DiscountCategory::class);
    }

    public function users()
    {
        return $this->hasMany(DiscountUser::class);
    }

    public function flashSales()
    {
        return $this->hasMany(FlashSale::class);
    }
}