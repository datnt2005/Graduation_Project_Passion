<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'parent_id',
    ];

    protected $appends = ['image_url'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function likes()
    {
        return $this->hasMany(PostCommentLike::class);
    }

    public function parent()
    {
        return $this->belongsTo(PostComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(PostComment::class, 'parent_id');
    }

    public function reports()
    {
        return $this->morphMany(Report::class, 'target');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::disk('r2')->url($this->image) : null;
    }
    
    public function media()
    {
        return $this->hasMany(PostCommentMedia::class);
    }
}
