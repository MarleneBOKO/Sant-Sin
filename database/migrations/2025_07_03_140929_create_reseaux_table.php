<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReseauxTable extends Migration
{
    public function up()
    {
        Schema::create('reseaux', function (Blueprint $table) {
            $table->id();
            $table->string('code_reseau')->unique();
            $table->string('libelle_reseau');
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        Schema::create('profil_reseau', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profil_id')->constrained('profils')->onDelete('cascade');
            $table->foreignId('reseau_id')->constrained('reseaux')->onDelete('cascade');
            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('profil_reseau');
        Schema::dropIfExists('reseaux');
    }
}
