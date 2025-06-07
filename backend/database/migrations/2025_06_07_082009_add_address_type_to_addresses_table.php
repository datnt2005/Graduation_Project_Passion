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
        Schema::table('addresses', function (Blueprint $table) {
            $table->enum('address_type', ['home', 'company'])->default('home')->after('is_default');
            // hoặc nếu bạn thích lưu kiểu text rõ ràng:
            // $table->string('address_type', 50)->default('Nhà riêng / Chung cư')->after('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn('address_type');
        });
    }
};
