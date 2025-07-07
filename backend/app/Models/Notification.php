<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
<<<<<<< HEAD
    use HasFactory;
=======
>>>>>>> origin/Tai_dev

    protected $fillable = [
        'title',
        'content',
        'type',
<<<<<<< HEAD
        'to_roles',
        'from_role',
=======
        'to_role',
        'to_user_id',
>>>>>>> origin/Tai_dev
        'user_id',
        'link',
        'image_url',
        'status',
<<<<<<< HEAD
        'channels',
        'sent_at',
=======
        'is_read',
        'read_at',
        'is_hidden'
>>>>>>> origin/Tai_dev
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
}
