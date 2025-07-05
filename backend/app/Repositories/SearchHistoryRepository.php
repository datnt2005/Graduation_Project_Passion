<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class SearchHistoryRepository
{
    public function addSearch($userId, $sessionId, $keyword)
    {
        // Sanitize keyword
        $keyword = htmlspecialchars(trim($keyword));
        if (empty($keyword)) return;

        // Lưu vào Redis
        $key = $userId ? "search:history:user:$userId" : "search:history:session:$sessionId";
        $history = Redis::lRange($key, 0, 9);
        if (!in_array($keyword, $history)) {
            Redis::lPush($key, $keyword);
            Redis::lTrim($key, 0, 9); // Giữ 10 từ khóa
            Redis::expire($key, 3 * 24 * 3600); // TTL: 3 days
        }

        // Kiểm tra trùng lặp trong database (trong 1 giờ gần nhất)
        $exists = DB::table('search_history')
            ->where(function ($q) use ($userId, $sessionId) {
                if ($userId) {
                    $q->where('user_id', $userId);
                } else {
                    $q->where('session_id', $sessionId);
                }
            })
            ->where('keyword', $keyword)
            ->where('created_at', '>=', now()->subHour(1))
            ->exists();

        if (!$exists) {
            DB::table('search_history')->insert([
                'user_id' => $userId,
                'session_id' => $sessionId,
                'keyword' => $keyword,
                'created_at' => now(),
            ]);
        }

        // Xóa lịch sử cũ
        $this->cleanOldHistory($userId, $sessionId);
    }

    public function getRecentSearches($userId, $sessionId)
    {
        // Lấy từ Redis trước
        $key = $userId ? "search:history:user:$userId" : "search:history:session:$sessionId";
        $history = Redis::lRange($key, 0, 9);

        // Nếu không có trong Redis, lấy từ database
        if (empty($history)) {
            $history = DB::table('search_history')
                ->where(function ($q) use ($userId, $sessionId) {
                    if ($userId) {
                        $q->where('user_id', $userId);
                    } else {
                        $q->where('session_id', $sessionId);
                    }
                })
                ->orderByDesc('created_at')
                ->limit(10)
                ->pluck('keyword')
                ->toArray();
        }

        return $history;
    }

    public function syncSessionToUser($sessionId, $userId)
    {
        $history = Redis::lRange("search:history:session:$sessionId", 0, 9);
        foreach ($history as $keyword) {
            $exists = DB::table('search_history')
                ->where('user_id', $userId)
                ->where('keyword', $keyword)
                ->where('created_at', '>=', now()->subHour(1))
                ->exists();
            if (!$exists) {
                Redis::lPush("search:history:user:$userId", $keyword);
                DB::table('search_history')->insert([
                    'user_id' => $userId,
                    'keyword' => $keyword,
                    'created_at' => now(),
                ]);
            }
        }
        Redis::lTrim("search:history:user:$userId", 0, 9);
        Redis::del("search:history:session:$sessionId");

        // Cập nhật database
        DB::table('search_history')
            ->where('session_id', $sessionId)
            ->whereNull('user_id')
            ->update(['user_id' => $userId, 'session_id' => null]);
    }

    public function deleteSearch($userId, $sessionId, $keyword = null)
    {
        $key = $userId ? "search:history:user:$userId" : "search:history:session:$sessionId";

        if ($keyword) {
            // Xóa từ khóa cụ thể
            Redis::lRem($key, 0, $keyword);
            DB::table('search_history')
                ->where(function ($q) use ($userId, $sessionId) {
                    if ($userId) {
                        $q->where('user_id', $userId);
                    } else {
                        $q->where('session_id', $sessionId);
                    }
                })
                ->where('keyword', $keyword)
                ->delete();
        } else {
            // Xóa toàn bộ lịch sử
            Redis::del($key);
            DB::table('search_history')
                ->where(function ($q) use ($userId, $sessionId) {
                    if ($userId) {
                        $q->where('user_id', $userId);
                    } else {
                        $q->where('session_id', $sessionId);
                    }
                })
                ->delete();
        }
    }

    private function cleanOldHistory($userId, $sessionId)
    {
        // Xóa các bản ghi cũ hơn ngoài 10 từ khóa gần nhất
        DB::statement("
            DELETE FROM search_history
            WHERE (user_id = ? OR session_id = ?)
            AND id NOT IN (
                SELECT id FROM (
                    SELECT id
                    FROM search_history
                    WHERE user_id = ? OR session_id = ?
                    ORDER BY created_at DESC
                    LIMIT 10
                ) AS sub
            )
        ", [$userId, $sessionId, $userId, $sessionId]);
    }
}