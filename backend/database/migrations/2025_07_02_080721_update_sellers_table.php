<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSellersTable extends Migration
{
    public function up(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            // Rename các cột nếu có
            if (Schema::hasColumn('sellers', 'cccd_front')) {
                $table->renameColumn('cccd_front', 'id_card_front_url');
            }

            if (Schema::hasColumn('sellers', 'cccd_back')) {
                $table->renameColumn('cccd_back', 'id_card_back_url');
            }

            // Thêm cột nếu chưa có
            if (!Schema::hasColumn('sellers', 'pickup_address')) {
                $table->text('pickup_address')->nullable()->after('personal_address');
            }

            if (!Schema::hasColumn('sellers', 'tax_code')) {
                $table->string('tax_code', 20)->nullable()->after('seller_type');
            }

            if (!Schema::hasColumn('sellers', 'business_name')) {
                $table->string('business_name')->nullable()->after('tax_code');
            }

            if (!Schema::hasColumn('sellers', 'business_email')) {
                $table->string('business_email')->nullable()->after('business_name');
            }

            if (!Schema::hasColumn('sellers', 'shipping_options')) {
                $table->json('shipping_options')->nullable()->after('business_email');
            }

            // Cứ thêm index user_id nếu chưa có
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn([
                'pickup_address',
                'tax_code',
                'business_name',
                'business_email',
                'shipping_options',
            ]);

            if (Schema::hasColumn('sellers', 'id_card_front_url')) {
                $table->renameColumn('id_card_front_url', 'cccd_front');
            }

            if (Schema::hasColumn('sellers', 'id_card_back_url')) {
                $table->renameColumn('id_card_back_url', 'cccd_back');
            }

            // Bỏ index user_id
            $table->dropIndex(['user_id']);
        });
    }
}
