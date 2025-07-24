<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'sender_type',
        'sender_id',
        'message',
        'message_type',
        'status',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    // 👉 Tự động thêm sender vào mỗi ChatMessage trả về
    protected $appends = ['sender'];

    public function session()
    {
        return $this->belongsTo(ChatSession::class, 'session_id');
    }

    public function attachments()
    {
        return $this->hasMany(ChatAttachment::class, 'message_id');
    }

    // 👉 Quan hệ với User nếu sender_type là user
    public function userSender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // 👉 Quan hệ với Seller nếu sender_type là seller
    public function sellerSender()
    {
        return $this->belongsTo(Seller::class, 'sender_id');
    }

    // 👉 Getter cho trường ảo "sender"
    public function getSenderAttribute()
    {
        if ($this->sender_type === 'user') {
            return $this->userSender;
        }

        if ($this->sender_type === 'seller') {
            return $this->sellerSender;
        }

        return null;
    }
}