<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'type',
        'to_roles',
        'from_role',
        'user_id',
        'link',
        'image_url',
        'status',
        'channels',
        'sent_at',
    ];

    protected $casts = [
        'channels' => 'array',
        'to_roles' => 'array', // ✅ Tự động decode JSON thành mảng
    ];

    // Notification.php

    public function recipients()
    {
        return $this->hasMany(NotificationRecipient::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'notification_recipients', 'notification_id', 'user_id');
    }

    public function sender()
{
    return $this->belongsTo(User::class, 'user_id');
}

public function receiver()
{
    return $this->belongsTo(User::class, 'receiver_id');
}

}
