<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id', 'attachments'
    ];

    protected $casts = [
        'attachments' => 'array',
    ];
    public function message()
    {
        return $this->belongsTo(ChatMessage::class, 'message_id');
    }
}