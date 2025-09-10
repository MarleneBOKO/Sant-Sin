<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\Menu;

class MenuService
{
    public function getMenusForAuthenticatedUser()
    {
        $user = Auth::user();

        if (!$user) {
            return [];
        }

        $profil = $user->profil;

        if (!$profil) {
            return [];
        }

       $menuIds = $profil->menus()->pluck('menus.id')->toArray();

$menus = Menu::with('enfants')
    ->whereIn('id', $menuIds)
    ->orWhereIn('parent_id', $menuIds)
    ->get();

        $menuTree = [];

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

    return $icons[$menuName] ?? 'folder'; // icône par défaut
}


   private function formatMenu($menu, $allMenus)
{
    $subMenus = $allMenus->where('parent_id', $menu->id);

    return [
        'id' => $menu->id,
        'title' => $menu->nom,
        'page_name' => $menu->route ?? 'page-name-defaut',
        'icon' => $this->getMenuIcon($menu->nom), 
        'layout' => 'side-menu',
        'droits' => isset($menu->pivot) ? explode(',', $menu->pivot->droits) : [],
        'sub_menu' => $subMenus->map(function ($sub) use ($allMenus) {
            return $this->formatMenu($sub, $allMenus);
        })->values()->toArray(),
    ];
}

}
