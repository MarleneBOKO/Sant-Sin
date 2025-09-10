<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Profil;

class ProfilsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $profils = [
            ['code_profil' => 'AUD', 'libelle' => 'Auditeur'],
            ['code_profil' => 'CTRL', 'libelle' => 'Contrôleur'],
            ['code_profil' => 'RSIN', 'libelle' => 'Régleur Sinistre'],
            ['code_profil' => 'RSTP', 'libelle' => 'Régleur Sinistre Tiers Payant'],
            ['code_profil' => 'RSI', 'libelle' => 'Régleur Sinistre Intermediaire '],
            ['code_profil' => 'ADMIN', 'libelle' => 'Administrateur'],
        ];


        foreach ($profils as $profil) {
           Profil::firstOrCreate(
                ['code_profil' => $profil['code_profil']],
                [
                    'libelle' => $profil['libelle'],
                    'created_at' => null,
                    'updated_at' => null,
                ]
            );
        }
    }
}
