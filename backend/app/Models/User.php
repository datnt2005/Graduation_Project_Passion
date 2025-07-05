<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'google_id', 'avatar',
        'role', 'status', 'otp', 'otp_expired_at', 'is_verified',
    ];

    protected $hidden = [
        'password', 'remember_token', 'otp',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_expired_at' => 'datetime',
        'is_verified' => 'boolean',
        'role' => 'string',
        'status' => 'string',
    ];


    public function seller()
    {
         return $this->hasOne(Seller::class);
    }
    public function discounts()
    {
        return $this->belongsToMany(Discount::class, 'discount_users', 'user_id', 'discount_id');
    }

    public function getAvatarUrlAttribute()
        {
            return $this->avatar ? Storage::disk('r2')->url($this->avatar) : null;
        }

        public function followedSellers()
        {
            return $this->belongsToMany(Seller::class, 'seller_followers')
            ->withTimestamps();
        }
        public function isFollowingSeller($sellerId)
        {
           return $this->followedSellers()->where('seller_id', $sellerId)->exists();
        }
    }
