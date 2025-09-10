<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLigneSuiviTable extends Migration
{
    public function up()
    {
        Schema::create('Ligne_Suivi', function (Blueprint $table) {
            $table->integer('Id_Ligne')->primary();

            $table->char('Code_Prestataire', 5)->nullable();
            $table->char('Reference_Facture', 50)->nullable();
            $table->integer('Mois_Facture');
            $table->dateTime('Date_Debut');
            $table->dateTime('Date_Fin');
            $table->float('Montant_Ligne')->nullable();
            $table->dateTime('Date_Enregistrement')->nullable();
            $table->string('Numero_demande', 50)->nullable();
            $table->dateTime('Date_Demande')->nullable();
            $table->float('Montant_Reglement')->nullable();
            $table->dateTime('Date_Transmission')->nullable();
            $table->dateTime('Date_Cloture')->nullable();
            $table->char('Numero_Cheque', 50)->nullable();
            $table->char('Statut_Ligne', 2)->default('0');
            $table->string('Nom_Assure', 255)->nullable();
            $table->string('Redacteur', 255)->nullable();
            $table->integer('Code_Souscripteur')->nullable();
            $table->integer('Numero_Reception')->nullable();
            $table->integer('is_evac')->default(0);
            $table->dateTime('Date_Depot')->nullable();
            $table->integer('delai_traitement')->nullable();
            $table->dateTime('Date_fin_reglementaire')->nullable();
            $table->char('rejete', 1)->default('0');
            $table->string('motif_rejet', 250)->nullable();
            $table->string('Code_Banque', 2)->nullable();
            $table->char('Code_motifrejet', 2)->nullable();
            $table->dateTime('date_rejet')->nullable();
            $table->char('Annee_Facture', 4)->nullable();
            $table->decimal('montrejete', 18, 0)->nullable();
            $table->string('userrejet', 250)->nullable();
            $table->integer('annuler')->nullable();
            $table->date('datetransMedecin')->nullable();
            $table->date('dateEnregMedecin')->nullable();
            $table->string('usertransMedecin', 250)->nullable();
            $table->date('dateEnregRMedecin')->nullable();
            $table->string('usertransRMedecin', 250)->nullable();
            $table->date('dateRetourMedecin')->nullable();
            $table->string('userSaisieReg', 250)->nullable();
            $table->date('datesaisiereg')->nullable();
            $table->string('usertransmi', 100)->nullable();
            $table->dateTime('dateenrtrans')->nullable();
            $table->string('usercorretion', 100)->nullable();
            $table->dateTime('datecorretion')->nullable();
            $table->text('motifcorretion')->nullable();
            $table->string('userEnregM', 100)->nullable();
            $table->integer('code_etape')->default(0);
            $table->integer('nbfacture')->default(0);
            $table->integer('anneecourrier')->nullable();
            $table->string('CodeCour', 30)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('Ligne_Suivi');
    }
}
