<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
{
    Schema::table('notifications', function (Blueprint $table) {
        $table->unsignedBigInteger('receiver_id')->nullable()->after('user_id');
        $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('notifications', function (Blueprint $table) {
        $table->dropForeign(['receiver_id']);
        $table->dropColumn('receiver_id');
    });
}

};
