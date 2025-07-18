<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class TrendRepository
{
    public function incrementKeyword($keyword)
    {
        Redis::zIncrBy('trends:keywords', 1, $keyword);
        Redis::expire('trends:keywords', 3600); // TTL: 1 hour
    }

    public function incrementProduct($productId)
    {
        Redis::zIncrBy('trends:products', 1, $productId);
        Redis::expire('trends:products', 3600); // TTL: 1 hour
    }

    public function getTopKeywords($limit = 10)
    {
        return Redis::zRevRange('trends:keywords', 0, $limit - 1, ['withscores' => true]);
    }

    public function getTopProducts($limit = 10)
    {
        return Redis::zRevRange('trends:products', 0, $limit - 1, ['withscores' => true]);
    }

    public function syncToDatabase()
    {
        $keywords = Redis::zRevRange('trends:keywords', 0, -1, ['withscores' => true]);
        foreach ($keywords as $keyword => $count) {
            DB::table('trends')->updateOrInsert(
                ['entity_type' => 'keyword', 'entity_id' => $keyword],
                ['search_count' => $count, 'last_updated' => now()]
            );
        }

        $products = Redis::zRevRange('trends:products', 0, -1, ['withscores' => true]);
        foreach ($products as $productId => $count) {
            DB::table('trends')->updateOrInsert(
                ['entity_type' => 'product', 'entity_id' => $productId],
                ['search_count' => $count, 'last_updated' => now()]
            );
        }
    }
}