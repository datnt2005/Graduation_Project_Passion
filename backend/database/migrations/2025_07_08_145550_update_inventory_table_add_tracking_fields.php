<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->string('status')->default('available'); // available, damaged, returned, etc.
            $table->text('note')->nullable(); // ghi chú hàng lỗi, sai mẫu
            $table->string('batch_number')->nullable();
            $table->timestamp('imported_at')->nullable(); // ngày nhập kho
            $table->string('imported_by')->nullable(); // người nhập tay hoặc user
            $table->text('import_source')->nullable(); // ví dụ: chợ, cửa hàng cũ,...
            $table->boolean('is_locked')->default(false); // để xử lý hàng tranh chấp
        });
    }

    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'note',
                'batch_number',
                'imported_at',
                'imported_by',
                'import_source',
                'is_locked',
            ]);
        });
    }

};
