<?php

namespace App\Http\Controllers;

use App\Models\MenuProfil;
use Illuminate\Http\Request;

class MenuProfilController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'profil_id' => 'required|exists:profils,id',
            'menu_id' => 'required|exists:menus,id',
        ]);

        return MenuProfil::create($request->only(['profil_id', 'menu_id']));
    }

    public function destroy($id)
    {
        return MenuProfil::destroy($id);
    }
}
