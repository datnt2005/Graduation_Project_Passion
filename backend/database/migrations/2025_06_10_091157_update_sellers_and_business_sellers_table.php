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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn([
                'seller_type',
                'identity_card_number',
                'date_of_birth',
                'personal_address',
                'phone_number',
                'identity_card_file',
                'verified_at',
                'deleted_at',
            ]);
        });
    }
};
