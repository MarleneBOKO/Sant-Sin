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
     Schema::create('menu_profil', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profil_id')->constrained()->onDelete('cascade');
            $table->foreignId('menu_id')->constrained()->onDelete('cascade');
            $table->string('droits')->nullable(); // par exemple : 'view,edit'
            $table->timestamps();
            $table->unique(['profil_id', 'menu_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('menu_profil');
    }
};
