<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trend extends Model
{
    protected $table = 'trends';

    protected $fillable = [
        'entity_type',
        'entity_id',
        'search_count',
        'click_count',
        'last_updated',
    ];

    protected $casts = [
        'entity_type' => 'string', // Hoặc có thể dùng enum class nếu Laravel 12 hỗ trợ
        'search_count' => 'integer',
        'click_count' => 'integer',
        'last_updated' => 'datetime',
    ];

    public $timestamps = false; // Không dùng created_at/updated_at mặc định

    // Quan hệ với Product (nếu entity_type là 'product')
    public function product()
    {
        return $this->belongsTo(Product::class, 'entity_id', 'id')
                    ->where('entity_type', 'product');
    }

    // Scope để lấy từ khóa phổ biến
    public function scopeKeywords($query)
    {
        return $query->where('entity_type', 'keyword');
    }

    // Scope để lấy sản phẩm phổ biến
    public function scopeProducts($query)
    {
        return $query->where('entity_type', 'product');
    }
}