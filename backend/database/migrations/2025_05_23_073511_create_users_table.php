<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->string('google_id', 255)->nullable();
            $table->string('avatar', 255)->nullable();
            $table->enum('role', ['user', 'seller', 'admin'])->default('user');
            $table->enum('status', ['active', 'inactive', 'banned'])->default('active');
            $table->string('otp', 6)->nullable();
            $table->timestamp('otp_expired_at')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('email', 'idx_users_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};