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
    Schema::create('courrier', function (Blueprint $table) {
        $table->integer('NumCour');
        $table->string('CodeCour', 50);
        $table->string('RefCour', 650);
        $table->date('DateEdit');
        $table->date('DateRecep');
        $table->date('DateEnreg');
        $table->binary('Image')->nullable();
        $table->string('Chemin', 900)->nullable();
        $table->integer('CodeNat');
        $table->integer('CodeType');
        $table->string('Civilite', 150);
        $table->string('Nom', 550);
        $table->string('Prenom', 550);
        $table->string('Objet', 850)->nullable();
        $table->string('expediteur', 900)->nullable();
        $table->string('usersaisie', 250)->nullable();
        $table->integer('Code_Civilite')->nullable();
        $table->integer('annee')->default(0);
        $table->string('telephone', 20)->nullable();

        $table->primary(['NumCour', 'annee']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courrier');
    }
};
