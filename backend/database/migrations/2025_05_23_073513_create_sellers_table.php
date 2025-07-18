    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('sellers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('store_name');
                $table->string('store_slug')->unique();
                $table->text('bio')->nullable();
                $table->string('document', 255)->nullable();
                $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
                $table->timestamps();
            });
        }

        public function down(): void
        {
            Schema::dropIfExists('sellers');
        }
    };
