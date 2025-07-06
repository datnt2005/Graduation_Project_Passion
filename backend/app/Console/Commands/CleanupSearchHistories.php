<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CleanupSearchHistories extends Command
{
    protected $signature = 'search:cleanup-db';
    protected $description = 'Xoá lịch sử tìm kiếm cũ trong DB (giống TTL Redis)';

    public function handle()
    {
        $now = Carbon::now();

        // 1. Xóa session_id không có user_id (quá 60 giây)
        $cutoffSession = $now->copy()->subSeconds(60);
        $deletedSession = DB::table('search_history')
            ->whereNull('user_id')
            ->where('created_at', '<', $cutoffSession)
            ->delete();

        // 2. Xóa user_id đã lâu (quá 7 ngày chẳng hạn)
        $cutoffUser = $now->copy()->subDays(7);
        $deletedUser = DB::table('search_history')
            ->whereNotNull('user_id')
            ->where('created_at', '<', $cutoffUser)
            ->delete();

        $this->info("Đã xoá $deletedSession dòng session và $deletedUser dòng user cũ trong DB.");
    }
}
