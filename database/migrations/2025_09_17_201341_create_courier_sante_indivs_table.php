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
    Schema::create('courier_sante_indivs', function (Blueprint $table) {
        $table->increments('NumCour'); // clé primaire auto-incrémentée
        $table->string('CodeCour')->unique()->nullable();
        $table->string('NomDeposant');
        $table->string('PrenomDeposant');
        $table->string('structure');
        $table->text('motif');
        $table->datetime('DateDepot');
        $table->string('Comptede');
        $table->integer('nbreetatdepot');
        $table->integer('nbrerecu')->nullable();
        $table->datetime('datesysteme');
        $table->datetime('datereception');
        $table->string('Receptioniste');
        $table->string('utilisateurSaisie');

    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courier_sante_indivs');
    }
};
