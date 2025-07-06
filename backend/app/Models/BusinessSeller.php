<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BusinessSeller extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id', 'tax_code', 'company_name', 'company_address',
        'business_license', 'representative_name', 'representative_phone',
    ];


}
