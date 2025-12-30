<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\Menu;

class MenuService
{
   public function getMenusForAuthenticatedUser()
{
    $user = Auth::user();
    if (!$user || !$user->profil) {
        return [];
    }

    // On récupère uniquement les IDs des menus explicitement autorisés pour ce profil
    $menuIds = $user->profil->menus()->pluck('menus.id')->toArray();

    // On récupère les menus qui sont dans cette liste d'autorisations
    // Suppression du orWhereIn('parent_id', $menuIds) pour éviter l'affichage automatique des enfants
    $menus = Menu::whereIn('id', $menuIds)->get();

    $menuTree = [];

    // On ne boucle que sur les menus parents autorisés
    foreach ($menus->where('parent_id', null) as $menu) {
        $menuTree[] = $this->formatMenu($menu, $menus);
    }

    return $menuTree;
}

    private function getMenuIcon($menuName)
    {
        $icons = [
            'Tableau de bord' => 'home',
            'Détail par réseau' => 'activity',
            'Gestion des factures' => 'file-text',
            'Courrier en instance' => 'inbox',
            'Gestion des appels de fonds' => 'dollar-sign',
            'Listing et reporting' => 'bar-chart',
            'Traitement spéciaux' => 'tool',
            'Admin' => 'settings',
            // Sous-menus
            'Enregistrer un dépôt individuel' => 'edit',
            'Transmission de facture' => 'send',
            'Saisie règlement' => 'dollar-sign',
            'Correction de facture' => 'refresh-cw',
            'Annulation de facture' => 'x-circle',
            'Listing facture' => 'list',
            'Demande validée dans Ixperta' => 'check-circle',
            'Listing règlement' => 'clipboard',
            'Gestion des utilisateurs' => 'users',
            'Gestion des profils' => 'shield',
            'Délais de traitement' => 'clock',
        ];

        return $icons[$menuName] ?? 'folder'; // Icône par défaut
    }
    private function formatMenu($menu, $allMenus)
    {
        $subMenus = $allMenus->where('parent_id', $menu->id);
        $hasSubMenu = $subMenus->isNotEmpty();

        $menuArray = [
            'id' => $menu->id,
            'title' => $menu->nom,
            // CORRECTION ICI : Si le menu a des sous-menus, on met 'javascript:;' pour empêcher la redirection
            'page_name' => $hasSubMenu ? 'javascript:;' : ($menu->route ?? 'page-name-defaut'),
            'icon' => $this->getMenuIcon($menu->nom),
            'layout' => 'side-menu',
            'droits' => isset($menu->pivot) ? explode(',', $menu->pivot->droits) : [],
        ];

        if ($hasSubMenu) {
            $menuArray['sub_menu'] = $subMenus->map(function ($sub) use ($allMenus) {
                return $this->formatMenu($sub, $allMenus);
            })->values()->toArray();
        }

        return $menuArray;
    }
}
