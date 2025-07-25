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
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'confirmed',
                'processing',
                'shipping',
                'delivered',
                'cancelled',
                'refunded',
                'failed'
            ])->default('pending');
        });

        Schema::table('shipping', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'ready_to_pick',
                'picking',
                'picked',
                'delivering',
                'delivered',
                'return',
                'returned',
                'cancel',
                'cancelled'
            ])->default('pending');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('shipping', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
