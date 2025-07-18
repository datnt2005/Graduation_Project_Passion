<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterKeyColumnInSettingsTable extends Migration
 {
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('key', 255)->change(); // Thay đổi key thành kiểu VARCHAR(255)
        });
    }

    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->integer('key')->change(); // Khôi phục nếu cần rollback
        });
    }
}
