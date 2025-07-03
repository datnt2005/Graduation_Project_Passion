<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Support extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'content',
        'admin_reply',
        'replied_at',
    ];
}
