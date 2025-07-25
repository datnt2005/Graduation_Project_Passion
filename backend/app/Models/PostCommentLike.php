<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PostCommentLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_comment_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comment()
    {
        return $this->belongsTo(PostComment::class, 'post_comment_id');
    }
}
