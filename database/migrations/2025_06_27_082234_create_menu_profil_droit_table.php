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
       Schema::create('menu_profil_droit', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('menu_profil_id');
    $table->unsignedBigInteger('droit_id');
    $table->timestamps();

    $table->foreign('menu_profil_id')->references('id')->on('menu_profil')->onDelete('cascade');
    $table->foreign('droit_id')->references('id')->on('droits')->onDelete('cascade');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_profil_droit');
    }
};
