<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GhnSyncLog extends Model
{
    protected $fillable = [
        'order_id', 'tracking_code', 'ghn_status', 'success', 'message'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}