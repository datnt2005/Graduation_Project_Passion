<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatMessage extends Model
{
    protected $fillable = ['session_id', 'sender_type', 'sender_id', 'message', 'message_type', 'is_read'];

    public function session()
    {
        return $this->belongsTo(ChatSession::class, 'session_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ChatAttachment::class, 'message_id');
    }
}
