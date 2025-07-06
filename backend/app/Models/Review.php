<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class Review extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'parent_id',
        'content',
        'rating',
        'likes',
        'status',
    ];

    public function reply()
    {
        return $this->hasOne(Review::class, 'parent_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function likes()
    {
        return $this->hasMany(ReviewLike::class, 'review_id');
    }

    public function likesCount()
    {
        return $this->likes()->count();
    }

    public function media()
    {
        return $this->hasMany(ReviewMedia::class, 'review_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'review_id');
    }
}
