<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{

    protected $fillable = [
        'name',
        'carrier',
        'estimated_days',
        'cost',
        'status',
    ];

    protected $casts = [
        'cost' => 'float',
        'estimated_days' => 'integer',
    ];

    public function shippings()
    {
        return $this->hasMany(Shipping::class);
    }
    public function shipping()
    {
        return $this->hasOne(Shipping::class)->with('shippingMethod');
    }
}
