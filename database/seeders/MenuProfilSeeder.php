<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Profil;
use App\Models\Menu;


class MenuProfilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Récupérer les profils
        $auditeur = Profil::where('code_profil', 'AUD')->first();
        $controleur = Profil::where('code_profil', 'CTRL')->first();
        $regleurSinistre = Profil::where('code_profil', 'RSIN')->first();
        $regleurSinistreInter = Profil::where('code_profil', 'RSI')->first();
        $admin = Profil::where('code_profil', 'ADMIN')->first();
        $regleurSinistreTier = Profil::where('code_profil, RSTP')->firts()  ;

        // Récupérer les menus principaux
        $tableauDeBord = Menu::where('nom', 'Tableau de bord')->first();
        $listingReporting = Menu::where('nom', 'Listing et reporting')->first();
        $detailsReseau = Menu::where('nom', 'Détail par réseau')->first();
        $gestionFactures = Menu::where('nom', 'Gestion des factures')->first();
        $courrierEnInstance = Menu::where('nom', 'Courrier en instance')->first();
        $gestionAppelFonds = Menu::where('nom', 'Gestion des appels de fonds')->first();
        $traitementSpeciaux = Menu::where('nom', 'Traitement spéciaux')->first();
        $adminMenu = Menu::where('nom', 'Admin')->first();

        // Profils Auditeur et Controleur : accès à Tableau de bord, Listing et Reporting, Détail par réseau
        $auditeur->menus()->syncWithoutDetaching([
            $tableauDeBord->id => ['droits' => 'view'],
            $listingReporting->id => ['droits' => 'view'],
            $detailsReseau->id => ['droits' => 'view'],
        ]);
        $controleur->menus()->syncWithoutDetaching([
            $tableauDeBord->id => ['droits' => 'view'],
            $listingReporting->id => ['droits' => 'view'],
            $detailsReseau->id => ['droits' => 'view'],
        ]);

        // Profils Régleur Sinistre (RSIN) - accès à plusieurs menus mais PAS traitement spéciaux
        $regleurSinistre->menus()->syncWithoutDetaching([
            $tableauDeBord->id => ['droits' => 'view,edit'],
            $detailsReseau->id => ['droits' => 'view,edit'],
            $gestionFactures->id => ['droits' => 'view,edit'],
            $courrierEnInstance->id => ['droits' => 'view,edit'],
            $gestionAppelFonds->id => ['droits' => 'view,edit'],
            $listingReporting->id => ['droits' => 'view,edit'],
            // pas traitement spéciaux
        ]);

        // Profils Régleur Chef Équipe Santé (RSCES) - accès à TOUS menus y compris traitement spéciaux
        $regleurSinistreInter->menus()->syncWithoutDetaching([
            $tableauDeBord->id => ['droits' => 'view,edit'],
            $detailsReseau->id => ['droits' => 'view,edit'],
            $gestionFactures->id => ['droits' => 'view,edit'],
            $courrierEnInstance->id => ['droits' => 'view,edit'],
            $gestionAppelFonds->id => ['droits' => 'view,edit'],
            $listingReporting->id => ['droits' => 'view,edit'],
            $traitementSpeciaux->id => ['droits' => 'view,edit'],
        ]);
 $regleurSinistreTier->menus()->syncWithoutDetaching([
            $tableauDeBord->id => ['droits' => 'view,edit'],
            $detailsReseau->id => ['droits' => 'view,edit'],
            $gestionFactures->id => ['droits' => 'view,edit'],
            $courrierEnInstance->id => ['droits' => 'view,edit'],
            $gestionAppelFonds->id => ['droits' => 'view,edit'],
            $listingReporting->id => ['droits' => 'view,edit'],
            $traitementSpeciaux->id => ['droits' => 'view,edit'],
        ]);

        // Admin : accès total
        $admin->menus()->syncWithoutDetaching([
            $tableauDeBord->id => ['droits' => 'view,edit,delete'],
            $detailsReseau->id => ['droits' => 'view,edit,delete'],
            $gestionFactures->id => ['droits' => 'view,edit,delete'],
            $courrierEnInstance->id => ['droits' => 'view,edit,delete'],
            $gestionAppelFonds->id => ['droits' => 'view,edit,delete'],
            $listingReporting->id => ['droits' => 'view,edit,delete'],
            $traitementSpeciaux->id => ['droits' => 'view,edit,delete'],
            $adminMenu->id => ['droits' => 'view,edit,delete'],
        ]);
    }
}
