<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Cập nhật ENUM trong cột type (chỉ dùng được với MySQL)
        DB::statement("ALTER TABLE reports MODIFY type ENUM('product', 'user', 'review', 'post', 'post_comment') NOT NULL");
    }

    public function down(): void
    {
        // Quay về ENUM cũ
        DB::statement("ALTER TABLE reports MODIFY type ENUM('product', 'user', 'review', 'post') NOT NULL");
    }
};

