<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('to_role');
            $table->longText('to_roles')->nullable()->after('user_id'); // 👈 thêm mới
        });
    }

    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('to_roles');
            $table->enum('to_role', ['admin', 'seller', 'user'])->nullable();
        });
    }
};
