<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CleanOldDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // Xóa trends cũ
        DB::table('trends')
            ->where('last_updated', '<', now()->subDays(30))
            ->where('search_count', '<', 10)
            ->delete();

        // Xóa lịch sử tìm kiếm session cũ
        DB::table('search_history')
            ->whereNotNull('session_id')
            ->where('created_at', '<', now()->subDays(7))
            ->delete();
    }
}