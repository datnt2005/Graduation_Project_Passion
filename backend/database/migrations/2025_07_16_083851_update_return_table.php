<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            // Xóa order_id nếu tồn tại
            if (Schema::hasColumn('refunds', 'order_id')) {
                // Gỡ ràng buộc foreign key nếu có (tránh lỗi migrate)
                DB::statement("ALTER TABLE `refunds` DROP FOREIGN KEY IF EXISTS `refunds_order_id_foreign`;");
                $table->dropColumn('order_id');
            }

            // Thêm order_item_id thay thế
            if (!Schema::hasColumn('refunds', 'order_item_id')) {
                $table->unsignedBigInteger('order_item_id')->after('id');
                $table->foreign('order_item_id')->references('id')->on('order_items')->onDelete('cascade');
            }

            // Thêm cột images (dạng JSON)
            if (!Schema::hasColumn('refunds', 'images')) {
                $table->json('images')->nullable()->after('amount');
            }

            // Thêm ghi chú admin
            if (!Schema::hasColumn('refunds', 'admin_note')) {
                $table->text('admin_note')->nullable()->after('images');
            }
        });
    }

    public function down(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            if (Schema::hasColumn('refunds', 'order_item_id')) {
                $table->dropForeign(['order_item_id']);
                $table->dropColumn('order_item_id');
            }

            if (Schema::hasColumn('refunds', 'images')) {
                $table->dropColumn('images');
            }

            if (Schema::hasColumn('refunds', 'admin_note')) {
                $table->dropColumn('admin_note');
            }

            // Thêm lại order_id nếu rollback
            if (!Schema::hasColumn('refunds', 'order_id')) {
                $table->unsignedBigInteger('order_id')->after('id');
                // Có thể thêm foreign key nếu cần
                // $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            }
        });
    }
};
