<?php

namespace App\Http\Controllers;

use App\Models\Droit;
use Illuminate\Http\Request;

class DroitController extends Controller
{
    public function index()
    {
        return Droit::all();
    }

    public function store(Request $request)
    {
        $request->validate(['libelle' => 'required|unique:droits']);
        return Droit::create($request->only(['libelle']));
    }

    public function destroy($id)
    {
        return Droit::destroy($id);
    }
}
