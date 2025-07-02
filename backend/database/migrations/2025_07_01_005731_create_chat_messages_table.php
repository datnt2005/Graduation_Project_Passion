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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->enum('sender_type', ['user', 'seller']);
            $table->unsignedBigInteger('sender_id');
            $table->text('message')->nullable();
            $table->enum('message_type', ['text', 'image', 'product'])->default('text');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('chat_sessions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
