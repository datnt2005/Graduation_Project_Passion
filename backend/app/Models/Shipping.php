<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipping extends Model
{
    protected $table = 'shipping';
    protected $fillable = [
        'order_id',
        'shipping_method_id',
        'estimated_delivery',
        'shipping_fee',
        'tracking_code',
        'status',
    ];

    protected $casts = [
        'estimated_delivery' => 'datetime',
        'shipping_fee' => 'float',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class);
    }
}
