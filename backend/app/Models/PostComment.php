<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PostComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_id',
        'content',
        'rating',
        'image',
        'admin_reply',
    ];

    // Tự động append vào JSON output
    protected $appends = ['image_url'];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function post()
    {
        return $this->belongsTo(\App\Models\Post::class);
    }

    // Accessor: trả về URL đầy đủ từ R2
    public function getImageUrlAttribute(): ?string
    {
        return $this->image
            ? Storage::disk('r2')->url($this->image)
            : null;
    }
}
