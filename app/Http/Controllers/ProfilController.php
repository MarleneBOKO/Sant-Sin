<?php

namespace App\Http\Controllers;

use App\Models\Profil;
use Illuminate\Http\Request;

class ProfilController extends Controller
{
   public function index()
{
    $profils = Profil::all();
    return view('pages.gestion-profils', compact('profils'));
}

public function create()
{
    return view('profils.create');
}

public function store(Request $request)
{
    $validated = $request->validate([
        'code_profil' => 'required|unique:profils,code_profil',
        'libelle' => 'required'
    ]);
    Profil::create($validated);
    return redirect()->route('profils.index')->with('success', 'Profil ajouté avec succès.');
}

public function edit($id)
{
    $profil = Profil::findOrFail($id);
    return view('profils.edit', compact('profil'));
}

public function update(Request $request, $id)
{
    $profil = Profil::findOrFail($id);
    $validated = $request->validate([
        'libelle' => 'required'
    ]);
    $profil->update($validated);
    return redirect()->route('profils.index')->with('success', 'Profil mis à jour.');
}

public function destroy($id)
{
    Profil::destroy($id);
    return redirect()->route('profils.index')->with('success', 'Profil supprimé.');
}
}
