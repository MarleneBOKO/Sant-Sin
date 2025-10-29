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
        Schema::table('type_prestataires', function (Blueprint $table) {
            $table->integer('Id_Categorie')->nullable()->after('libelle_type_prestataire'); // Optionnel : 'after()' pour placer le champ après un existant
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('type_prestaires', function (Blueprint $table) {
            $table->dropColumn('Id_Categorie');
        });
    }
};
