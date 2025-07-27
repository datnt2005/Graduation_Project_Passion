<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    protected $fillable = [
        'order_item_id', // Thay 'order_id' bằng 'order_item_id'
        'user_id',
        'reason',
        'status',
        'amount',
        'images',
        'admin_note',
    ];

    public function order()
    {
        return $this->belongsTo(\App\Models\Order::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
