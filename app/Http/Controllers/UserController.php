<?php


namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Profil;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * =========================================
     * MÉTHODE 1 : Afficher la liste des utilisateurs
     * =========================================
     * Route : GET /gestion-utilisateurs
     */
    public function index()
    {
        // Récupérer tous les utilisateurs avec leurs relations
        $users = User::with(['profil', 'service'])->get();

        // Récupérer tous les profils et services
        $profils = Profil::all();
        $services = Service::all();

        // Retourner la vue
        return view('pages.gestion-utilisateurs', compact('users', 'profils', 'services'));
    }

    /**
     * =========================================
     * MÉTHODE 2 : Créer un nouvel utilisateur
     * =========================================
     * Route : POST /gestion-utilisateurs
     */
    public function store(Request $request)
    {
        // Validation des données
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'login' => 'required|string|unique:users,login',
            'email' => 'required|email|unique:users,email',
              'userpass' => 'required|string|min:6|confirmed',
            'idserv' => 'required|exists:services,id',
            'Profil' => 'required|exists:profils,id',
        ]);


        // Créer l'utilisateur
        $user = User::create([
            'name' => $validated['name'],
            'prenom' => $validated['prenom'],
            'login' => $validated['login'],
            'email' => $validated['email'],
         'password' => bcrypt($validated['userpass']),
            'service_id' => $validated['idserv'],
            'profil_id' => $validated['Profil'],
            'active' => true,
            'must_change_password' => true,  // Forcer le changement
          'password_changed_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
    'created_at' => date('Y-m-d H:i:s'),
        ]);


        // Rediriger avec succès
        return redirect()->back()
            ->with('success', "✅ Utilisateur créé avec succès.");

    }

    /**
     * =========================================
     * MÉTHODE 3 : Afficher un utilisateur (AJAX)
     * =========================================
     * Route : GET /gestion-utilisateurs/{id}
     */
    public function show($id)
    {
        try {
            // Charger l'utilisateur avec ses relations
            $user = User::with(['profil', 'service'])->findOrFail($id);

            // Retourner en JSON
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
                    'must_change_password' => $user->must_change_password,
                    'password_expired' => $user->password_expired,
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

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Utilisateur non trouvé',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * =========================================
     * MÉTHODE 4 : Mettre à jour un utilisateur
     * =========================================
     * Route : PUT /gestion-utilisateurs/{id}
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Validation (email unique sauf pour cet utilisateur)
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'profil_id' => 'required|exists:profils,id',
            'service_id' => 'required|exists:services,id',
        ]);

        // Mise à jour
        $user->update([
            'name' => $validated['name'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'profil_id' => $validated['profil_id'],
            'service_id' => $validated['service_id'],
        ]);

         return redirect()->back()
            ->with('success', '✅ Utilisateur mis à jour avec succès.');
    }

    /**
     * =========================================
     * MÉTHODE 5 : Activer un utilisateur
     * =========================================
     * Route : PATCH /gestion-utilisateurs/{id}/activate
     */
    public function activate($id)
    {
        $user = User::findOrFail($id);
        $user->active = true;
        $user->save();

        return redirect()->back()
            ->with('success', "✅ {$user->name} a été activé avec succès.");
    }

    /**
     * =========================================
     * MÉTHODE 6 : Désactiver un utilisateur
     * =========================================
     * Route : PATCH /gestion-utilisateurs/{id}/deactivate
     */
    public function deactivate($id)
    {
        $user = User::findOrFail($id);
        $user->active = false;
        $user->save();

        return redirect()->back()
            ->with('success', "⛔ {$user->name} a été désactivé avec succès.");
    }

     public function resetPassword(Request $request, $id)
    {
        // Validation renforcée
        $validated = $request->validate([
            'password' => [
                'required',
                'confirmed',
                Password::min(12)
                    ->mixedCase()      // Majuscules et minuscules
                    ->numbers()        // Au moins un chiffre
                    ->symbols()        // Au moins un caractère spécial
                    ->uncompromised(), // Pas dans les fuites de données connues
            ],
        ], [
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 12 caractères.',
            'password.mixed' => 'Le mot de passe doit contenir des majuscules et minuscules.',
            'password.numbers' => 'Le mot de passe doit contenir au moins un chiffre.',
            'password.symbols' => 'Le mot de passe doit contenir au moins un caractère spécial.',
            'password.uncompromised' => 'Ce mot de passe est trop commun. Choisissez-en un plus sécurisé.',
        ]);
        // Trouver l'utilisateur
        $user = User::findOrFail($id);
        // Mettre à jour le mot de passe
        $user->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => true,  // Forcer le changement à la prochaine connexion
            'password_changed_at' => now(),
            'password_expired' => false,
             'updated_at' => date('Y-m-d H:i:s')
        ]);
        // Rediriger avec succès
        return redirect()->back()
            ->with('success', "🔑 Mot de passe de {$user->name} réinitialisé avec succès. L'utilisateur devra le changer à sa prochaine connexion.");
    }

}
