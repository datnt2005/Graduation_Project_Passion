<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Seller extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'store_name',
        'store_slug',
        'seller_type',
        'bio',
        'identity_card_number',
        'date_of_birth',
        'personal_address',
        'phone_number',
        'document',
        'verification_status',
        'cccd_front',
        'cccd_back',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function business()
    {
        return $this->hasOne(BusinessSeller::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'seller_followers')
            ->withTimestamps();
    }
    public function followersCount()
    {
        return $this->followers()->count();
    }

    public function followed()
    {
        return $this->belongsToMany(User::class, 'seller_followed')
            ->withTimestamps();
    }
    public function follows()
    {
        return $this->belongsToMany(User::class, 'seller_followed', 'seller_id', 'followed_user_id')
            ->withTimestamps();
    }
}
