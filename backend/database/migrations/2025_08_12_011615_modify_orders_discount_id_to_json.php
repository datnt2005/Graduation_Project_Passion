<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Xóa foreign key constraint trước
            $table->dropForeign(['discount_id']);
            
            // Thay đổi cột discount_id từ integer sang json
            $table->json('discount_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Đổi lại về integer
            $table->unsignedBigInteger('discount_id')->nullable()->change();
            
            // Thêm lại foreign key constraint
            $table->foreign('discount_id')->references('id')->on('discounts');
        });
    }
};
