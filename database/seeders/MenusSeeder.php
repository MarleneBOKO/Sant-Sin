<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $menus = [
            ['nom' => 'Tableau de bord', 'route' => 'dashboard'],
            ['nom' => 'Détail par réseau', 'route' => 'detail-reseau'],
            ['nom' => 'Gestion des factures', 'route' => 'gestion-factures'],
            ['nom' => 'Courrier en instance', 'route' => 'courrier-instance'],
            ['nom' => 'Gestion des appels de fonds', 'route' => 'appels-fonds'],
            ['nom' => 'Listing et reporting', 'route' => 'listing-reporting'],
            ['nom' => 'Traitement spéciaux', 'route' => 'traitement-speciaux'],
            ['nom' => 'Admin', 'route' => 'admin'],
        ];

        $menuIds = [];
        foreach ($menus as $menu) {
            $menuIds[$menu['nom']] = Menu::create($menu)->id;
        }

        // Sous-menus
        $sousMenus = [
            ['nom' => 'Enregistrer un dépôt individuel', 'route' => 'depot-individuel', 'parent_id' => $menuIds['Gestion des factures']],
            ['nom' => 'Transmission de facture', 'route' => 'transmission-facture', 'parent_id' => $menuIds['Gestion des factures']],
            ['nom' => 'Saisie règlement', 'route' => 'saisie-reglement', 'parent_id' => $menuIds['Gestion des factures']],

            ['nom' => 'Correction de facture', 'route' => 'correction-facture', 'parent_id' => $menuIds['Traitement spéciaux']],
            ['nom' => 'Annulation de facture', 'route' => 'annulation-facture', 'parent_id' => $menuIds['Traitement spéciaux']],

            ['nom' => 'Listing facture', 'route' => 'listing-facture', 'parent_id' => $menuIds['Listing et reporting']],
            ['nom' => 'Demande validée dans Ixperta', 'route' => 'demande-ixperta', 'parent_id' => $menuIds['Listing et reporting']],
            ['nom' => 'Listing règlement', 'route' => 'listing-reglement', 'parent_id' => $menuIds['Listing et reporting']],

            ['nom' => 'Gestion des utilisateurs', 'route' => 'gestion-utilisateurs', 'parent_id' => $menuIds['Admin']],
            ['nom' => 'Gestion des profils', 'route' => 'gestion-profils', 'parent_id' => $menuIds['Admin']],
            ['nom' => 'Délais de traitement', 'route' => 'delais-traitement', 'parent_id' => $menuIds['Admin']],
        ];

        foreach ($sousMenus as $sous) {
            Menu::create($sous);
        }
    }
}
