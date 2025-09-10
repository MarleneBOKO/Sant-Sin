<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Reseau;
class ReseauxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run()
    {
        $reseaux = [
            ['code_reseau' => '1', 'libelle_reseau' => 'Gros Prestataires'],
            ['code_reseau' => '2', 'libelle_reseau' => 'Autres Prestataires'],
            ['code_reseau' => '3', 'libelle_reseau' => 'Individuels'],
             ['code_reseau' => '4', 'libelle_reseau' => 'Evacuation'],
              ['code_reseau' => '5', 'libelle_reseau' => 'Tous les Reseaux'],
        ];

        foreach ($reseaux as $reseau) {
            Reseau::firstOrCreate($reseau);
        }
    }
}
