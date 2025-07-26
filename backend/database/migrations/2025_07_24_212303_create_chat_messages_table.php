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
        // Kiểm tra nếu bảng chưa tồn tại thì mới tạo
        if (!Schema::hasTable('chat_messages')) {
            Schema::create('chat_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('session_id');
                $table->unsignedBigInteger('sender_id');
                $table->enum('sender_type', ['user', 'seller']);
                $table->text('message')->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
                $table->enum('message_type', ['text', 'image', 'product'])->default('text');
                $table->enum('status', ['normal', 'deleted'])->default('normal');
                $table->timestamps();

                $table->foreign('session_id')->references('id')->on('chat_sessions')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};