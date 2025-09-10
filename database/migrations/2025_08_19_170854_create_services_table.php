<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
          $table->integer('codeserv')->unique();


            $table->string('libelle');

            $table->foreignId('direction_id')->constrained('directions')->onDelete('cascade');

            // Un service peut être aussi une direction (optionnel)
            $table->boolean('is_direction')->default(false);
$table->timestamp('created_at')->nullable();
$table->timestamp('updated_at')->nullable();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
