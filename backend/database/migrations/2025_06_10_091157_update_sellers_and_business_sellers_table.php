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
            if (!Schema::hasColumn('sellers', 'seller_type')) {
                $table->enum('seller_type', ['personal', 'business'])->default('personal')->after('store_slug');
            }

            if (!Schema::hasColumn('sellers', 'identity_card_number')) {
                $table->string('identity_card_number', 20)->nullable()->unique()->after('bio');
            }

            if (!Schema::hasColumn('sellers', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('identity_card_number');
            }

            if (!Schema::hasColumn('sellers', 'personal_address')) {
                $table->text('personal_address')->nullable()->after('date_of_birth');
            }

            if (!Schema::hasColumn('sellers', 'phone_number')) {
                $table->string('phone_number', 20)->nullable()->after('personal_address');
            }

            if (!Schema::hasColumn('sellers', 'identity_card_file')) {
                $table->string('identity_card_file')->nullable()->after('phone_number');
            }

            if (!Schema::hasColumn('sellers', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('verification_status');
            }

            if (!Schema::hasColumn('sellers', 'deleted_at')) {
                $table->softDeletes();
            }
        });


        // // Update bảng business_sellers
        // Schema::table('business_sellers', function (Blueprint $table) {

        //     if (!Schema::hasColumn('business_sellers', 'representative_name')) {
        //         $table->string('representative_name')->nullable()->after('business_license');
        //     }

        //     if (!Schema::hasColumn('business_sellers', 'representative_phone')) {
        //         $table->string('representative_phone', 20)->nullable()->after('representative_name');
        //     }

        //     if (!Schema::hasColumn('business_sellers', 'province')) {
        //         $table->string('province', 100)->nullable()->after('company_address');
        //     }

        //     if (!Schema::hasColumn('business_sellers', 'district')) {
        //         $table->string('district', 100)->nullable()->after('province');
        //     }

        //     if (!Schema::hasColumn('business_sellers', 'status')) {
        //         $table->enum('status', ['active', 'inactive', 'revoked'])->default('active')->after('representative_phone');
        //     }

        //     if (!Schema::hasColumn('business_sellers', 'business_license_file')) {
        //         $table->string('business_license_file')->nullable()->after('business_license');
        //     }

        //     if (!Schema::hasColumn('business_sellers', 'deleted_at')) {
        //         $table->softDeletes();
        //     }
        // });
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

        // // Rollback bảng business_sellers
        // Schema::table('business_sellers', function (Blueprint $table) {
        //     $table->dropColumn([
        //         'representative_name',
        //         'representative_phone',
        //         'province',
        //         'district',
        //         'status',
        //         'business_license_file',
        //         'deleted_at',
        //     ]);
        //     // $table->renameColumn('business_license_file', 'business_license');
        // });
    }
};
