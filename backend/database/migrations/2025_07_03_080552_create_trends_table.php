<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trends', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->string('entity_type', 50); // 'keyword' hoặc 'product'
            $table->string('entity_id', 255); // ID hoặc từ khóa
            $table->unsignedBigInteger('search_count')->default(0); // Số lần tìm kiếm
            $table->timestamp('last_updated')->useCurrent(); // Thời gian cập nhật cuối
            $table->unique(['entity_type', 'entity_id'], 'uniq_entity'); // UNIQUE để upsert
            $table->index('last_updated', 'idx_last_updated'); // INDEX cho dọn dẹp
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trends');
    }
};