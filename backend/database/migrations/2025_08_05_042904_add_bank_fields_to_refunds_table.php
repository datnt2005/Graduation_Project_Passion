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
        Schema::table('refunds', function (Blueprint $table) {
            $table->string('bank_account_number')->nullable()->after('amount');
            $table->string('bank_name')->nullable()->after('bank_account_number');
        });
    }

    public function down()
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropColumn(['bank_account_number', 'bank_name']);
        });
    }
};
