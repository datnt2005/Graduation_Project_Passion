<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/Notification.php
class Notification extends Model
{

    protected $fillable = [
        'title',
        'content',
        'type',
        'to_role',
        'to_user_id',
        'user_id',
        'link',
        'image_url',
        'status',
        'is_read',
        'read_at',
        'is_hidden'
    ];
}
