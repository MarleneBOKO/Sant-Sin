<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Profil;
use App\Models\Reseau;
use Illuminate\Support\Facades\DB;

class ProfilReseauSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET DATEFORMAT ymd');
        // Récupérer les IDs des profils
        $rsin = Profil::where('code_profil', 'RSIN')->first();
        $rstp = Profil::where('code_profil', 'RSTP')->first();
        $rsi = Profil::where('code_profil', 'RSI')->first();
        $rrstp = Profil::where('code_profil', 'RRSTP')->first();
        $rrsi = Profil::where('code_profil', 'RRSI')->first();

        // Récupérer les IDs des réseaux
        $pharmacie = Reseau::where('code_reseau', '1')->first();
        $parapharmacie = Reseau::where('code_reseau', '2')->first();
        $individuels = Reseau::where('code_reseau', '3')->first();
        $evacuation = Reseau::where('code_reseau', '4')->first();
        $appelFond = Reseau::where('code_reseau', '5')->first();

        // Liaisons
        $liaisons = [
            // RSIN : tous les réseaux
            ['profil_id' => $rsin->id, 'reseau_id' => $pharmacie->id],
            ['profil_id' => $rsin->id, 'reseau_id' => $parapharmacie->id],
            ['profil_id' => $rsin->id, 'reseau_id' => $individuels->id],
            ['profil_id' => $rsin->id, 'reseau_id' => $evacuation->id],
            ['profil_id' => $rsin->id, 'reseau_id' => $appelFond->id],

            // RSTP : Pharmacie, Parapharmacie, Appel de fond
            ['profil_id' => $rstp->id, 'reseau_id' => $pharmacie->id],
            ['profil_id' => $rstp->id, 'reseau_id' => $parapharmacie->id],
            ['profil_id' => $rstp->id, 'reseau_id' => $appelFond->id],

            // RSI : Individuels, Evacuation
            ['profil_id' => $rsi->id, 'reseau_id' => $individuels->id],
            ['profil_id' => $rsi->id, 'reseau_id' => $evacuation->id],

            // RRSTP : Même que RSTP
            ['profil_id' => $rrstp->id, 'reseau_id' => $pharmacie->id],
            ['profil_id' => $rrstp->id, 'reseau_id' => $parapharmacie->id],
            ['profil_id' => $rrstp->id, 'reseau_id' => $appelFond->id],

            // RRSI : Même que RSI
            ['profil_id' => $rrsi->id, 'reseau_id' => $individuels->id],
            ['profil_id' => $rrsi->id, 'reseau_id' => $evacuation->id],
        ];

        foreach ($liaisons as $liaison) {
         DB::table('profil_reseau')->updateOrInsert(
    ['profil_id' => $liaison['profil_id'], 'reseau_id' => $liaison['reseau_id']],
    ['created_at' => DB::raw('SYSDATETIME()'), 'updated_at' => DB::raw('SYSDATETIME()')]
);
        }
    }
}
