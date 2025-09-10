<?php

namespace App\Http\Controllers;

use App\Models\MenuProfilDroit;
use Illuminate\Http\Request;

class MenuProfilDroitController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'menu_profil_id' => 'required|exists:menu_profil,id',
            'droit_id' => 'required|exists:droits,id'
        ]);

        return MenuProfilDroit::create($request->only(['menu_profil_id', 'droit_id']));
    }

    public function destroy($id)
    {
        return MenuProfilDroit::destroy($id);
    }
}
