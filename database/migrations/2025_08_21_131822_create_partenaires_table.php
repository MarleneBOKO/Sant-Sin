<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartenairesTable extends Migration
{
    public function up(): void
    {
        Schema::create('partenaires', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['souscripteur', 'prestataire']);


            $table->string('nom', 255)->nullable();
            $table->string('adresse', 255)->nullable();
            $table->string('telephone', 50)->nullable();
            $table->string('email', 255)->nullable();


            $table->char('code_type_prestataire', 1)->nullable();
            $table->integer('coutierG')->default(0);

            $table->char('code_reseau', 1)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partenaires');
    }
}
