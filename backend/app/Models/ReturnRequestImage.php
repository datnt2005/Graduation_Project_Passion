<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnRequestImage extends Model
{
    protected $fillable = ['return_request_id', 'path'];
}
