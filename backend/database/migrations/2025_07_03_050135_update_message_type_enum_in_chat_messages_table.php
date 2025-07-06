<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ✅ Thêm giá trị mới vào ENUM cột `message_type`
        DB::statement("ALTER TABLE chat_messages
            MODIFY COLUMN message_type ENUM('text', 'image', 'product', 'revoked', 'edited')
            NOT NULL DEFAULT 'text'");
    }

    public function down(): void
    {
        // ⏪ Quay lại giá trị ENUM cũ nếu rollback
        DB::statement("ALTER TABLE chat_messages
            MODIFY COLUMN message_type ENUM('text', 'image', 'product')
            NOT NULL DEFAULT 'text'");
    }
};