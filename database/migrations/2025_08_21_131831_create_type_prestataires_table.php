<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypePrestatairesTable extends Migration
{
    public function up(): void
    {
        Schema::create('type_prestataires', function (Blueprint $table) {
            $table->char('code_type_prestataire', 1)->primary();
            $table->string('libelle_type_prestataire', 250);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('type_prestataires');
    }
}
