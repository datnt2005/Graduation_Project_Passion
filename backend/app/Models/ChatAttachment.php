<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatAttachment extends Model
{
    protected $fillable = ['message_id', 'file_type', 'file_url', 'meta_data'];

    protected $casts = [
        'meta_data' => 'array',
    ];

    public function message()
    {
        return $this->belongsTo(ChatMessage::class, 'message_id');
    }
}
