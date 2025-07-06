<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('chat_sessions', function (Blueprint $table) {
        // Nếu chắc chắn chưa có thì KHÔNG cần drop
        $table->foreign('seller_id')->references('id')->on('sellers')->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('chat_sessions', function (Blueprint $table) {
        $table->dropForeign(['seller_id']);
    });
}

};