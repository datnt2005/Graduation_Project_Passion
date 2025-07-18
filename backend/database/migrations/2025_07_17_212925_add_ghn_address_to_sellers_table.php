<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGhnAddressToSellersTable extends Migration
{
    public function up()
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->integer('province_id')->nullable()->after('pickup_address');
            $table->integer('district_id')->nullable()->after('province_id');
            $table->string('ward_id')->nullable()->after('district_id');
            $table->string('address')->nullable()->after('ward_id');
        });
    }

    public function down()
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn(['province_id', 'district_id', 'ward_id', 'address']);
        });
    }
}