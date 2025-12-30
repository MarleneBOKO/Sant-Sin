<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
       public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('facture_id')->nullable();
            $table->string('type', 50); // 'delai_depassement', 'delai_approche', 'action_requise'
            $table->string('titre');
            $table->text('message');
            $table->boolean('lue')->default(false);
            $table->enum('priorite', ['basse', 'moyenne', 'haute'])->default('moyenne');
            $table->datetime('date_limite')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'lue']);
            $table->index('date_limite');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::dropIfExists('notifications');
    }
};
