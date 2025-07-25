<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('notification_recipients', function (Blueprint $table) {
            $table->tinyInteger('is_hidden')->default(0)->after('read_at')->comment('0 = hiển thị, 1 = ẩn');
        });
    }

    public function down()
    {
        Schema::table('notification_recipients', function (Blueprint $table) {
            $table->dropColumn('is_hidden');
        });
    }
};
