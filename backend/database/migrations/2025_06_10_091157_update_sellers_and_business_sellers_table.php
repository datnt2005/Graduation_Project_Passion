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
        // Update bảng sellers
        Schema::table('sellers', function (Blueprint $table) {
            // $table->enum('seller_type', ['personal', 'business'])->default('personal')->after('store_slug');
            // $table->string('identity_card_number', 20)->nullable()->unique()->after('bio');
            // $table->date('date_of_birth')->nullable()->after('identity_card_number');
            // $table->text('personal_address')->nullable()->after('date_of_birth');
            // $table->string('phone_number', 20)->nullable()->after('personal_address');
            // $table->string('identity_card_file')->nullable()->after('phone_number');
            // $table->timestamp('verified_at')->nullable()->after('verification_status');
            // $table->softDeletes();

            // Nếu muốn đổi tên column 'document' thành 'identity_card_file' thì bỏ comment dòng dưới
            // $table->renameColumn('document', 'identity_card_file');
        });

        // Update bảng business_sellers
        Schema::table('business_sellers', function (Blueprint $table) {
            // $table->string('representative_name')->nullable()->after('business_license');
            // $table->string('representative_phone', 20)->nullable()->after('representative_name');
            // $table->string('province', 100)->nullable()->after('company_address');
            // $table->string('district', 100)->nullable()->after('province');
            // $table->enum('status', ['active', 'inactive', 'revoked'])->default('active')->after('representative_phone');
            // $table->string('business_license_file')->nullable()->after('business_license');
            // $table->softDeletes();

            // Nếu muốn đổi tên column 'business_license' thành 'business_license_file' thì bỏ comment dòng dưới
            // $table->renameColumn('business_license', 'business_license_file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback bảng sellers
        // Schema::table('sellers', function (Blueprint $table) {
        //     $table->dropColumn([
        //         'seller_type',
        //         'identity_card_number',
        //         'date_of_birth',
        //         'personal_address',
        //         'phone_number',
        //         'identity_card_file',
        //         'verified_at',
        //         'deleted_at',
        //     ]);
            // $table->renameColumn('identity_card_file', 'document');
        // });

        // Rollback bảng business_sellers
        Schema::table('business_sellers', function (Blueprint $table) {
//             $table->dropColumn([
//                 'representative_name',
//                 'representative_phone',
//                 'province',
// 'district',
//                 'status',
//                 'business_license_file',
//                 'deleted_at',
            // ]);
            // $table->renameColumn('business_license_file', 'business_license');
        });
    }
};
