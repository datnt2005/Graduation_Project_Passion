<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();                     // Tên cấu hình (ví dụ: site_name, logo, etc)
            $table->text('value')->nullable();                   // Giá trị cấu hình
            $table->string('type')->default('text');             // text, image, boolean, json,...
            $table->string('group')->nullable();                 // Nhóm (ví dụ: general, seo, payment)
            $table->text('description')->nullable();             // Mô tả chức năng cấu hình
            $table->boolean('is_editable')->default(true);       // Có thể chỉnh sửa từ giao diện admin không
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
