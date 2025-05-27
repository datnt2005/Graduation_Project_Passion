<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'status',
        'otp',
        'otp_expired_at',
        'is_verified',
        'otp_attempts',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp',
        'otp_expired_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_expired_at' => 'datetime',
        'password' => 'hashed',
        'is_verified' => 'boolean',
    ];
}
