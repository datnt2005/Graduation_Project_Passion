<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShippingDiscountToShippingTable extends Migration
{
    public function up()
    {
        Schema::table('shipping', function (Blueprint $table) {
            $table->decimal('shipping_discount', 10, 2)->nullable()->after('shipping_fee');
        });
    }

    public function down()
    {
        Schema::table('shipping', function (Blueprint $table) {
            $table->dropColumn('shipping_discount');
        });
    }
}