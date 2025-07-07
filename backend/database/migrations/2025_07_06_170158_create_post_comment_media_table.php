<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('post_comment_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_comment_id')->constrained('post_comments')->onDelete('cascade');
            $table->string('media_url', 255);
            $table->enum('media_type', ['image', 'video']);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('post_comment_media');
    }
};
