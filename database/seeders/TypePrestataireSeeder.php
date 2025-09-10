<?php


namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TypePrestataire;

class TypePrestataireSeeder extends Seeder
{
    public function run()
    {
        $types = [
            ['code_type_prestataire' => 'A', 'libelle_type_prestataire' => 'Unité de soins primaires(USP)'],
            ['code_type_prestataire' => 'B', 'libelle_type_prestataire' => 'Dépôt de pharmacie'],
            ['code_type_prestataire' => 'C', 'libelle_type_prestataire' => 'Centre de santé communautaire'],
            ['code_type_prestataire' => '0', 'libelle_type_prestataire' => 'Pharmacie'],
            ['code_type_prestataire' => '1', 'libelle_type_prestataire' => 'Clinique'],
            ['code_type_prestataire' => '2', 'libelle_type_prestataire' => 'Laboratoire'],
            ['code_type_prestataire' => '3', 'libelle_type_prestataire' => 'Cabinet dentaire'],
            ['code_type_prestataire' => '4', 'libelle_type_prestataire' => 'Opticien'],
            ['code_type_prestataire' => '5', 'libelle_type_prestataire' => 'Centre hospitalier'],
            ['code_type_prestataire' => '6', 'libelle_type_prestataire' => 'Centre de radiologie'],
            ['code_type_prestataire' => '7', 'libelle_type_prestataire' => 'Cabinet médical'],
            ['code_type_prestataire' => '8', 'libelle_type_prestataire' => 'Centre d\'ophtalmologie'],
            ['code_type_prestataire' => '9', 'libelle_type_prestataire' => 'Polyclinique'],
            ['code_type_prestataire' => 'D', 'libelle_type_prestataire' => 'Centre hospitalier régional(CHR)'],
            ['code_type_prestataire' => 'E', 'libelle_type_prestataire' => 'Centre hospitalier universitaire(CHU)'],
            ['code_type_prestataire' => 'F', 'libelle_type_prestataire' => 'Centre hospitalier Préfectoral(CHP)'],
            ['code_type_prestataire' => 'H', 'libelle_type_prestataire' => 'PMI (protection maternelle & infantile)'],
            ['code_type_prestataire' => 'G', 'libelle_type_prestataire' => 'Dispensaire'],
            ['code_type_prestataire' => 'I', 'libelle_type_prestataire' => 'Maternité'],
            ['code_type_prestataire' => 'J', 'libelle_type_prestataire' => 'Centre confessionnel'],
        ];

        foreach ($types as $type) {
            $model = new TypePrestataire($type);
            $model->timestamps = false; // <- Pas de timestamps
            $model->save();
        }
    }
}
