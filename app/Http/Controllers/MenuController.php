<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        return Menu::with('children')->whereNull('parent_id')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'libelle' => 'required',
            'parent_id' => 'nullable|exists:menus,id'
        ]);

        return Menu::create($request->only(['libelle', 'parent_id']));
    }

    public function update(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);
        $menu->update($request->only(['libelle', 'parent_id']));
        return $menu;
    }

    public function destroy($id)
    {
        return Menu::destroy($id);
    }
}
