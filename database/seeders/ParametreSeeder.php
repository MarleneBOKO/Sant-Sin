<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Parametre;

class ParametreSeeder extends Seeder
{
    public function run()
    {
        $parametres = [
            ['codeparam' => 1, 'typaram' => 'Typereseau', 'codtyparam' => '04', 'libelleparam' => 'BUREAUX DIRECTS'],
            ['codeparam' => 2, 'typaram' => 'Typereseau', 'codtyparam' => '1', 'libelleparam' => 'COURTIERS'],
            ['codeparam' => 3, 'typaram' => 'Typereseau', 'codtyparam' => '3', 'libelleparam' => 'AGENCES'],
            ['codeparam' => 4, 'typaram' => 'Typereseau', 'codtyparam' => '4', 'libelleparam' => 'REASSURANCE'],
            ['codeparam' => 5, 'typaram' => 'Typereseau', 'codtyparam' => '5', 'libelleparam' => 'BUREAUX DE PARIS'],
            ['codeparam' => 6, 'typaram' => 'Typereseau', 'codtyparam' => '6', 'libelleparam' => 'BANCASSURANCE'],
            ['codeparam' => 7, 'typaram' => 'Typereseau', 'codtyparam' => '7', 'libelleparam' => 'FORCES DE VENTE'],

            // Mois
            ['codeparam' => 8, 'typaram' => 'MoisFacture', 'codtyparam' => '1', 'libelleparam' => 'Janvier'],
            ['codeparam' => 9, 'typaram' => 'MoisFacture', 'codtyparam' => '2', 'libelleparam' => 'Février'],
            ['codeparam' => 10, 'typaram' => 'MoisFacture', 'codtyparam' => '3', 'libelleparam' => 'Mars'],
            ['codeparam' => 11, 'typaram' => 'MoisFacture', 'codtyparam' => '4', 'libelleparam' => 'Avril'],
            ['codeparam' => 12, 'typaram' => 'MoisFacture', 'codtyparam' => '5', 'libelleparam' => 'Mai'],
            ['codeparam' => 13, 'typaram' => 'MoisFacture', 'codtyparam' => '6', 'libelleparam' => 'Juin'],
            ['codeparam' => 14, 'typaram' => 'MoisFacture', 'codtyparam' => '7', 'libelleparam' => 'Juillet'],
            ['codeparam' => 15, 'typaram' => 'MoisFacture', 'codtyparam' => '8', 'libelleparam' => 'Août'],
            ['codeparam' => 16, 'typaram' => 'MoisFacture', 'codtyparam' => '9', 'libelleparam' => 'Septembre'],
            ['codeparam' => 17, 'typaram' => 'MoisFacture', 'codtyparam' => '10', 'libelleparam' => 'Octobre'],
            ['codeparam' => 18, 'typaram' => 'MoisFacture', 'codtyparam' => '11', 'libelleparam' => 'Novembre'],
            ['codeparam' => 19, 'typaram' => 'MoisFacture', 'codtyparam' => '12', 'libelleparam' => 'Décembre'],

            // Étapes de facture
            ['codeparam' => 20, 'typaram' => 'etape_Facture', 'codtyparam' => '0', 'libelleparam' => 'Non Traitée'],
            ['codeparam' => 21, 'typaram' => 'etape_Facture', 'codtyparam' => '1', 'libelleparam' => 'Traitée'],
        ];

        foreach ($parametres as $param) {
            Parametre::create($param);
        }

        // Ajout dynamique des années (2000 à 2030)
        $codeparam = 22;
        for ($year = 2000; $year <= 2030; $year++) {
            Parametre::create([
                'codeparam' => $codeparam++,
                'typaram' => 'AnneFacture',
                'codtyparam' => $year,
                'libelleparam' => (string)$year,
            ]);
        }
    }
}
