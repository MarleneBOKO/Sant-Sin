<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Droit;

class DroitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $droits = [
            ['code' => 'view', 'libelle' => 'Voir'],
            ['code' => 'edit', 'libelle' => 'Modifier'],
            ['code' => 'delete', 'libelle' => 'Supprimer'],
        ];

        foreach ($droits as $droit) {
            Droit::firstOrCreate($droit);
        }
    }
}
