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
        Schema::table('posts', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable(); // Add slug column
            $table->string('excerpt', 500)->nullable();  // Add excerpt for summary
            $table->enum('status', ['draft', 'published', 'pending'])->default('draft'); // Add status
            $table->timestamp('published_at')->nullable(); // Add publication date
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['slug', 'excerpt', 'status', 'published_at']);
        });
    }
};
