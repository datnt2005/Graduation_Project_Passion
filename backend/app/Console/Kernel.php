<?php

namespace App\Console;

use App\Jobs\SyncTrendsToDatabaseJob;
use App\Jobs\CleanOldDataJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        $schedule->job(new SyncTrendsToDatabaseJob)->everyFiveMinutes();
        $schedule->job(new CleanOldDataJob)->daily();
        $schedule->command('cleanup:search-history')->hourly(); // Xoá từ khoá tìm kiếm cũ mỗi giờ

    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}