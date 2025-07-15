<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id', 'sender_type', 'sender_id', 'message', 'message_type', 'status', 'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean'
    ];

    public function session()
    {
        return $this->belongsTo(ChatSession::class, 'session_id');
    }



    public function attachments()
    {
        return $this->hasMany(ChatAttachment::class, 'message_id');
    }




        public function userSender()
    {
        return $this->belongsTo(User::class, 'sender_id')->where('sender_type', 'user');
    }

    public function sellerSender()
    {
        return $this->belongsTo(Seller::class, 'sender_id')->where('sender_type', 'seller');
    }

}
