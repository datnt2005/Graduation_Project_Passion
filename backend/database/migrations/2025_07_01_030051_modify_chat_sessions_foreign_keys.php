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
        // Chỉ thêm nếu chưa có foreign key
        if (!DB::select("SELECT * FROM information_schema.TABLE_CONSTRAINTS
                         WHERE CONSTRAINT_NAME = 'chat_sessions_seller_id_foreign'
                         AND TABLE_NAME = 'chat_sessions'")) {
            $table->foreign('seller_id')->references('id')->on('sellers')->onDelete('cascade');
        }
    });
}

public function down()
{
    Schema::table('chat_sessions', function (Blueprint $table) {
        $table->dropForeign(['seller_id']);
    });
}

};
