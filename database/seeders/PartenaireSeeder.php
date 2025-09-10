<?php


namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Partenaire;

class PartenaireSeeder extends Seeder
{
    public function run()
    {
        // Exemples de prestataires
        $prestataires = [
            [
                'nom' => 'CLINIQUE PEDIATRIQUE DE PARAKOU',
                'type' => 'prestataire',
                'code_reseau' => '1',
                'code_type_prestataire' => '1',
                'telephone' => null,
                'email' => null,
                'coutierG' => 0,
            ],
            [
                'nom' => 'OLEA BENIN',
                'type' => 'prestataire',
                'code_reseau' => '1',
                'code_type_prestataire' => '0',
                'telephone' => null,
                'email' => null,
                'coutierG' => 1,
            ],
        ];

        // Exemples de souscripteurs
        $souscripteurs = [
            [
                'nom' => 'MAIRIE DE OUIDHA',
                'type' => 'souscripteur',
                'adresse' => 'BP 11',
                'telephone' => '21341401',
                'email' => null,
                'code_reseau' => '3',
            ],
            [
                'nom' => 'NSIA ASSURANCES BENIN',
                'type' => 'souscripteur',
                'adresse' => null,
                'telephone' => null,
                'email' => null,
                'code_reseau' => null,
            ],
        ];


        foreach ($prestataires as $item) {
            $model = new Partenaire($item);
            $model->timestamps = false; // ceci empêche Laravel d'ajouter created_at / updated_at
            $model->save();
        }

        foreach ($souscripteurs as $item) {
            $model = new Partenaire($item);
            $model->timestamps = false;
            $model->save();
        }
    }
}
