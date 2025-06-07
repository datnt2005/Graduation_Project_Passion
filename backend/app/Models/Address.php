<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'user_id', 'name', 'phone',
        'province_id', 'district_id', 'ward_code', 'detail', 'address_type',
        'is_default',
    ];
}
