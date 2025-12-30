<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Profil;
use App\Models\Menu;

class MenuProfilSeeder extends Seeder
{
    public function run()
    {
        // 1. Profils
        $auditeur = Profil::where('code_profil', 'AUD')->first();
        $controleur = Profil::where('code_profil', 'CTRL')->first();
        $regleurSinistre = Profil::where('code_profil', 'RSIN')->first();
        $regleurSinistreInter = Profil::where('code_profil', 'RSI')->first();
        $regleurSinistreTier = Profil::where('code_profil', 'RSTP')->first();
        $rrstp = Profil::where('code_profil', 'RRSTP')->first();
        $rrsi = Profil::where('code_profil', 'RRSI')->first();
        $admin = Profil::where('code_profil', 'ADMIN')->first();

        // 2. Menus Parents
        $mDashboard = Menu::where('nom', 'Tableau de bord')->first();
        $mReporting = Menu::where('nom', 'Listing et reporting')->first();
        $mReseau    = Menu::where('nom', 'Détail par réseau')->first();
        $mFactures  = Menu::where('nom', 'Gestion des factures')->first();
        $mCourrier  = Menu::where('nom', 'Courrier en instance')->first();
        $mAppels    = Menu::where('nom', 'Gestion des appels de fonds')->first();
        $mSpeciaux  = Menu::where('nom', 'Traitement spéciaux')->first();
        $mAdminMenu = Menu::where('nom', 'Admin')->first();
        $mSituation = Menu::where('nom', 'Situation prestataire')->first();

        // 3. Menus spécifiques (Sous-menus)
        $mDepotIndiv = Menu::where('nom', 'Enregistrer un dépôt individuel')->first();

        // On récupère tous les sous-menus pour l'attribution automatique
        $allSubMenusFactures = Menu::where('parent_id', $mFactures->id)->pluck('id')->toArray();
        $allSubMenusReporting = Menu::where('parent_id', $mReporting->id)->pluck('id')->toArray();
        $allSubMenusSpeciaux = Menu::where('parent_id', $mSpeciaux->id)->pluck('id')->toArray();
        $allSubMenusAdmin = Menu::where('parent_id', $mAdminMenu->id)->pluck('id')->toArray();

        // --- FONCTION UTILITAIRE POUR GÉNÉRER LES DROITS ---
        $format = function($ids, $droit = 'view,edit') {
            return array_fill_keys($ids, ['droits' => $droit]);
        };

        // --- ATTRIBUTION ---

        // 1. RÉGLEUR SINISTRE BASE (Tous sauf Tiers Payant)
        // Ils ont tout dans factures (y compris dépôt)
        $menusRegleurComplet = [
            $mDashboard->id => ['droits' => 'view,edit'],
            $mReseau->id    => ['droits' => 'view,edit'],
            $mFactures->id  => ['droits' => 'view,edit'],
            $mCourrier->id  => ['droits' => 'view,edit'],
            $mAppels->id    => ['droits' => 'view,edit'],
            $mReporting->id => ['droits' => 'view,edit'],
            $mSituation->id => ['droits' => 'view'],
        ] + $format($allSubMenusFactures) + $format($allSubMenusReporting);

        $regleurSinistre->menus()->sync($menusRegleurComplet);
        $regleurSinistreInter->menus()->sync($menusRegleurComplet);
        $rrsi->menus()->sync($menusRegleurComplet + [
            $mSpeciaux->id => ['droits' => 'view,edit']
        ] + $format($allSubMenusSpeciaux));

        // 2. TIERS PAYANT (RSTP & RRSTP)
        // On retire l'ID du dépôt individuel de la liste des factures
        $subFacturesSansDepot = array_diff($allSubMenusFactures, [$mDepotIndiv->id]);

        $menusTiersPayant = [
            $mDashboard->id => ['droits' => 'view,edit'],
            $mReseau->id    => ['droits' => 'view,edit'],
            $mFactures->id  => ['droits' => 'view,edit'],
            $mCourrier->id  => ['droits' => 'view,edit'],
            $mAppels->id    => ['droits' => 'view,edit'],
            $mReporting->id => ['droits' => 'view,edit'],
            $mSituation->id => ['droits' => 'view'],
        ] + $format($subFacturesSansDepot) + $format($allSubMenusReporting);

        $regleurSinistreTier->menus()->sync($menusTiersPayant);
        $rrstp->menus()->sync($menusTiersPayant + [
            $mSpeciaux->id => ['droits' => 'view,edit']
        ] + $format($allSubMenusSpeciaux));

        // 3. ADMIN (Tout)
        $admin->menus()->sync(
            $format(Menu::pluck('id')->toArray(), 'view,edit,delete')
        );
    }
}
