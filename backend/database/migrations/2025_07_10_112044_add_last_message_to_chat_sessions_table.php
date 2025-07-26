<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Kiểm tra nếu cột chưa tồn tại thì mới thêm
        if (Schema::hasTable('chat_sessions') && !Schema::hasColumn('chat_sessions', 'last_message')) {
            Schema::table('chat_sessions', function (Blueprint $table) {
                $table->text('last_message')->nullable()->after('seller_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('chat_sessions') && Schema::hasColumn('chat_sessions', 'last_message')) {
            Schema::table('chat_sessions', function (Blueprint $table) {
                $table->dropColumn('last_message');
            });
        }
    }
};
