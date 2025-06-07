<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountUser extends Model
{
    use HasFactory;

    protected $table = 'discount_users';

    protected $fillable = [
        'discount_id',
        'user_id',
        'redeemed_at',
        'is_used',
    ];

    protected $casts = [
        'redeemed_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    // Relationships
    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}