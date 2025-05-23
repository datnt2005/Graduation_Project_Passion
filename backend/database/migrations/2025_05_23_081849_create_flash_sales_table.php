<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flash_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('discount_id')->constrained('discounts')->onDelete('cascade');
            $table->timestamp('start_time') ->nullable();
            $table->timestamp('end_time') ->nullable();
            $table->integer('quantity')->default(0);
            $table->decimal('price', 10, 2);
            $table->decimal('discounted_price', 10, 2);
            $table->text('description')->nullable();
            $table->string('image', 255)->nullable();
            $table->string('video', 255)->nullable();
            $table->string('link', 255)->nullable();
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flash_sales');
    }
};