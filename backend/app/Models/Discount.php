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
        'seller_id',
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
        return $this->belongsToMany(Product::class, 'discount_products', 'discount_id', 'product_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'discount_categories', 'discount_id', 'category_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'discount_users', 'discount_id', 'user_id');
    }

    public function flashSales()
    {
        return $this->hasMany(FlashSale::class);
    }
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'discount_order');
    }
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
}