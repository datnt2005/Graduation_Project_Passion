<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/Notification.php
class Notification extends Model
{
    protected $fillable = [ 'user_id', 'type',
        'title', 'content', 'link', 'to_role', 'to_user_id', 'is_read', 'read_at', 'image_url', 'status',
    ];
}
