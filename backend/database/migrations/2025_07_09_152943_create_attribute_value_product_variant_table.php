<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_value_product_variant', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('product_variant_id');
            $table->unsignedBigInteger('attribute_value_id');

            $table->timestamps();

            // Khóa ngoại
            $table->foreign('product_variant_id')
                  ->references('id')->on('product_variants')
                  ->onDelete('cascade');

            $table->foreign('attribute_value_id')
                  ->references('id')->on('attribute_values')
                  ->onDelete('cascade');

            // Không cho trùng cặp giá trị
            $table->unique(['product_variant_id', 'attribute_value_id'], 'unique_variant_attribute');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_value_product_variant');
    }
};
