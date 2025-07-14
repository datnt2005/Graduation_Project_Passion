<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'order_id',
        'amount',
        'status',
        'note',
        'created_at',
        'transferred_at',
    ];

    public $timestamps = false;

    protected $casts = [
        'amount' => 'float',
        'created_at' => 'datetime',
        'transferred_at' => 'datetime',
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
} 