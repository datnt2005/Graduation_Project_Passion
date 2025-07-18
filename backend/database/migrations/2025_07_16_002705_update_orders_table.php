<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Cập nhật cột status để thêm failed_delivery và rejected_by_customer
            $table->enum('status', [
                'pending',
                'confirmed',
                'processing',
                'shipping',
                'delivered',
                'cancelled',
                'refunded',
                'failed',
                'failed_delivery',
                'rejected_by_customer'
            ])->default('pending')->change();

            // Thêm cột failure_reason
            $table->string('failure_reason', 255)->nullable()->after('note');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Khôi phục cột status về danh sách trạng thái cũ
            $table->enum('status', [
                'pending',
                'confirmed',
                'processing',
                'shipping',
                'delivered',
                'cancelled',
                'refunded',
                'failed'
            ])->default('pending')->change();

            // Xóa cột failure_reason
            $table->dropColumn('failure_reason');
        });
    }
};
