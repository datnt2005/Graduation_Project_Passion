<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('reviews')->onDelete('cascade');
            $table->string('media_url', 255);
            $table->enum('media_type', ['image', 'video']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_media');
    }
};