<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('address_id')->constrained('addresses')->onDelete('restrict');
            $table->foreignId('discount_id')->nullable()->constrained('discounts')->onDelete('set null');
            $table->text('note')->nullable();
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->decimal('total_price', 10, 2);
            $table->decimal('discount_price', 10, 2)->default(0.00);
            $table->decimal('final_price', 10, 2);
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id', 'idx_orders_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};