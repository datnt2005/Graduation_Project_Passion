<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'google_id',
        'avatar',
        'role',
        'status',
        'otp',
        'otp_expired_at',
        'is_verified',
        'warning_email_sent',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_expired_at' => 'datetime',
        'is_verified' => 'boolean',
        'role' => 'string',
        'status' => 'string',
        'warning_email_sent' => 'boolean',
    ];

    public function seller()
    {
        return $this->hasOne(Seller::class, 'user_id', 'id');
    }

    public function discounts()
    {
        return $this->belongsToMany(Discount::class, 'discount_users', 'user_id', 'discount_id')
            ->withTimestamps();
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

    public function searchHistory()
    {
        return $this->hasMany(SearchHistory::class);
    }

    public function approvals()
    {
        return $this->hasMany(ProductApproval::class, 'admin_id');
    }

    public function savedVouchers(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'discount_users', 'user_id', 'discount_id')
            ->withPivot(['created_at'])
            ->withTimestamps();
    }

    public function saveVoucher($discount)
    {
        if ($this->savedVouchers()->where('discount_id', $discount->id)->exists()) {
            return false; // Voucher đã được lưu
        }
        return $this->savedVouchers()->attach($discount->id, ['created_at' => now()]);
    }

    public function removeVoucher($discount)
    {
        return $this->savedVouchers()->detach($discount->id);
    }

    // Trong app/Models/User.php
    public function notifications()
{
    return $this->hasMany(Notification::class, 'receiver_id', 'id');
}
}
