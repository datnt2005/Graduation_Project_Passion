<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
// public function up(): void
// {
//     Schema::create('chat_sessions', function (Blueprint $table) {
//         $table->id();
//         $table->unsignedBigInteger('user_id');
//         $table->unsignedBigInteger('seller_id');
//         $table->enum('status', ['open', 'closed'])->default('open');
//         $table->timestamp('last_message_at')->nullable();
//         $table->timestamps();

//         // Đặt tên rõ ràng cho foreign key để tránh trùng tên ngầm
//         $table->foreign('user_id', 'fk_chat_sessions_user_id')
//               ->references('id')->on('users')->onDelete('cascade');

//         $table->foreign('seller_id', 'fk_chat_sessions_seller_id')
//               ->references('id')->on('sellers')->onDelete('cascade');
//     });
// }


//     /**
//      * Reverse the migrations.
//      */
//     public function down(): void
//     {
//         Schema::dropIfExists('chat_sessions');
//     }
};
