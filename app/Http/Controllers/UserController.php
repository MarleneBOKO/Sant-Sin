<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\MenuProfil;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Profil;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['profil', 'service'])->get();
        $profils = Profil::all();
        $services = Service::all();

        return view('pages.gestion-utilisateurs', compact('users', 'profils', 'services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'login' => 'required|string|unique:users,login',
            'email' => 'required|email|unique:users,email',
            'userpass' => 'required|string|min:6|confirmed',
            'idserv' => 'required|exists:services,id',
            'Profil' => 'required|exists:profils,id',
        ]);

        User::create([
            'name' => $validated['name'],
            'prenom' => $validated['prenom'],
            'login' => $validated['login'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['userpass']),
            'service_id' => $validated['idserv'],
            'profil_id' => $validated['Profil'],
            'active' => true,
        ]);

        return redirect()->route('users.index')->with('success', 'Utilisateur ajouté avec succès.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $profils = Profil::all();
        $services = Service::all();

        return view('pages.edit-user', compact('user', 'profils', 'services'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'profil_id' => 'required|exists:profils,id',
            'service_id' => 'required|exists:services,id',
        ]);

        $user->update([
            'name' => $validated['name'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'profil_id' => $validated['profil_id'],
            'service_id' => $validated['service_id'],
        ]);

        return redirect()->route('users.index')->with('success', 'Utilisateur mis à jour avec succès.');
    }

    public function show($id)
    {
        try {
            // Charger l'utilisateur avec ses relations de base
            $user = User::with(['profil', 'service'])->findOrFail($id);

            // Retourner seulement les données essentielles
            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'prenom' => $user->prenom,
                    'login' => $user->login,
                    'email' => $user->email,
                    'service_id' => $user->service_id,
                    'profil_id' => $user->profil_id,
                    'active' => $user->active,
                    'service' => $user->service ? [
                        'id' => $user->service->id,
                        'libelle' => $user->service->libelle
                    ] : null,
                    'profil' => $user->profil ? [
                        'id' => $user->profil->id,
                        'libelle' => $user->profil->libelle
                    ] : null
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Utilisateur non trouvé'
            ], 404);
        } catch (\Exception $e) {
            // Log de l'erreur pour le débogage
            \Log::error("Erreur dans UserController@show pour l'utilisateur {$id}: " . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Erreur lors de la récupération des données utilisateur',
                'message' => config('app.debug') ? $e->getMessage() : 'Une erreur interne s\'est produite',
                'line' => config('app.debug') ? $e->getLine() : null,
                'file' => config('app.debug') ? $e->getFile() : null
            ], 500);
        }
    }

    public function activate($id)
    {
        $user = User::findOrFail($id);
        $user->active = true;
        $user->save();

        return redirect()->route('users.index')->with('success', 'Utilisateur activé avec succès.');
    }

    public function deactivate($id)
    {
        $user = User::findOrFail($id);
        $user->active = false;
        $user->save();

        return redirect()->route('users.index')->with('success', 'Utilisateur désactivé avec succès.');
    }
}
