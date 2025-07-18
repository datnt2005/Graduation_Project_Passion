<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawRequest extends Model
{
    protected $fillable = [
        'seller_id',
        'amount',
        'status',
        'approved_at',
        'note',
        'bank_name',
        'bank_account',
        'bank_account_name',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function seller()
    {
        return $this->belongsTo(\App\Models\Seller::class, 'seller_id');
    }
}
