<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tạo bảng sequence để sinh id
        Schema::create('search_history_sequence', function (Blueprint $table) {
            $table->bigIncrements('id');
        });

        // Tạo bảng search_history
        Schema::create('search_history', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_id', 255)->nullable();
            $table->string('keyword', 255);
            $table->timestamp('created_at')->useCurrent();

            $table->primary(['id', 'created_at']);
            $table->index(['user_id', 'session_id', 'created_at'], 'idx_user_session_created');
        });

        // Trigger để tự động sinh id từ sequence
        DB::statement("
            CREATE TRIGGER search_history_id_trigger
            BEFORE INSERT ON search_history
            FOR EACH ROW
            BEGIN
                INSERT INTO search_history_sequence VALUES (NULL);
                SET NEW.id = LAST_INSERT_ID();
            END
        ");

        // Partition theo tháng
        DB::statement("
            ALTER TABLE search_history
            PARTITION BY RANGE (UNIX_TIMESTAMP(created_at)) (
                PARTITION p202507 VALUES LESS THAN (UNIX_TIMESTAMP('2025-08-01 00:00:00')),
                PARTITION p202508 VALUES LESS THAN (UNIX_TIMESTAMP('2025-09-01 00:00:00')),
                PARTITION p202509 VALUES LESS THAN (UNIX_TIMESTAMP('2025-10-01 00:00:00')),
                PARTITION p_future VALUES LESS THAN MAXVALUE
            )
        ");
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS search_history_id_trigger');
        Schema::dropIfExists('search_history');
        Schema::dropIfExists('search_history_sequence');
    }
};