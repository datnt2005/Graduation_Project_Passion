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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_variant_id');
            $table->enum('action_type', ['import', 'export', 'adjust', 'damage', 'return']);
            $table->integer('quantity');
            $table->text('note')->nullable(); // lý do nhập/xuất/điều chỉnh
            $table->string('created_by')->nullable(); // admin/seller/system
            $table->string('created_by_type')->nullable(); // admin, seller, system
            $table->timestamps();

            $table->foreign('product_variant_id')->references('id')->on('product_variants')->onDelete('cascade');
        });
}

public function down(): void
{
    Schema::dropIfExists('stock_movements');
}

};
