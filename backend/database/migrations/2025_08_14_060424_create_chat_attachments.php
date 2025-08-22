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
        Schema::create('chat_attachments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('message_id');
            $table->longText('attachments')
                  ->charset('utf8mb4')
                  ->collation('utf8mb4_bin')
                  ->nullable()
                  ->check("json_valid(`attachments`)");
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Index & Foreign key
            $table->index('message_id', 'chat_attachments_message_id_foreign');
            $table->foreign('message_id')
                  ->references('id')
                  ->on('chat_messages')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_attachments');
    }
};
