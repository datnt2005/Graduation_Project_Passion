<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PostCommentMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_comment_id',
        'media_url',
        'media_type',
    ];

    public function comment()
    {
        return $this->belongsTo(PostComment::class, 'post_comment_id');
    }
}
