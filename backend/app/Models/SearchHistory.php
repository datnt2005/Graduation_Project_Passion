<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchHistory extends Model
{
    protected $table = 'search_history';
    protected $primaryKey = 'id'; // Chỉ dùng id
    public $incrementing = true; // id tự tăng
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'session_id',
        'keyword',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->take(10);
    }

    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId)
                    ->orderBy('created_at', 'desc')
                    ->take(10);
    }

    public static function limitHistory($userId, $sessionId)
    {
        if ($userId) {
            static::forUser($userId)->offset(10)->delete();
        } else {
            static::forSession($sessionId)->offset(10)->delete();
        }
    }
}