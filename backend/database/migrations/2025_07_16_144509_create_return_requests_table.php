
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_item_id');
            $table->unsignedBigInteger('user_id');
            $table->text('reason');
            $table->enum('type', ['return', 'exchange'])->default('return');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->longText('images')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->foreign('order_item_id')->references('id')->on('order_items')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_requests');
    }
};
