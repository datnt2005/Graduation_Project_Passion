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
        Schema::table('sellers', function (Blueprint $table) {
            $table->string('cccd_front')->nullable();
            $table->string('cccd_back')->nullable();
        });
    }

    public function down()
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn(['cccd_front', 'cccd_back']);
        });
    }
};
