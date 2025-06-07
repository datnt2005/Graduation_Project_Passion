<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function replies()
{
    return $this->hasMany(Review::class, 'parent_id');
}

}

