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
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('title')->nullable()->after('id');
            $table->string('link')->nullable()->after('content');
            $table->enum('to_role', ['admin', 'seller', 'user'])->nullable()->after('link');
            $table->unsignedBigInteger('to_user_id')->nullable()->after('to_role');
            $table->timestamp('read_at')->nullable()->after('is_read');
        });
    }

    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['title', 'link', 'to_role', 'to_user_id', 'read_at']);
        });
    }
};
