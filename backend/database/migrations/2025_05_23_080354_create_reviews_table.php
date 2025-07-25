<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('reviews')->onDelete('set null');
            $table->text('content');
            $table->integer('rating')->unsigned()->check('rating >= 1 AND rating <= 5');
            $table->integer('likes')->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();

            $table->index(['product_id', 'user_id'], 'idx_reviews_product_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};