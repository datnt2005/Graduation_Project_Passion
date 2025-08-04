<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameOrderItemsIdToOrderIdInRefundsTable extends Migration
{
    public function up()
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->renameColumn('order_item_id', 'order_id');
        });
    }

    public function down()
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->renameColumn('order_id', 'order_items_id');
        });
    }
}
