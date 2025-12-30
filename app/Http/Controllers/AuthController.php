<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function loginView()
    {
        return view('login.main', [
            'theme' => 'light',
            'page_name' => 'auth-login',
            'layout' => 'login'
        ]);
    }

    public function login(Request $request)
    {
        // Validation des identifiants
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required'],
        ]);

        // Tentative de connexion
        if (!Auth::attempt($credentials, $request->boolean('remember_me'))) {
            return response()->json([
                'message' => 'Login ou mot de passe incorrect.'
            ], 422);
        }

        $request->session()->regenerate();
        $user = Auth::user();

        // ✅ VÉRIFICATION DE L'EXPIRATION DU MOT DE PASSE
        // Calcul de l'âge (par dates complètes uniquement, sans l'heure)
        $passwordAge = $user->password_changed_at
            ? Carbon::parse($user->password_changed_at)->startOfDay()->diffInDays(now()->startOfDay())
            : 999; // Si jamais changé, considérer comme expiré

        // Vérifier si l'utilisateur est admin (les admins ne sont pas soumis à l'expiration)
        $isAdmin = $user->profil->code_profil == 'ADMIN';

        // ✅ CAS 1 : MOT DE PASSE EXPIRÉ (>= 30 jours) - SAUF ADMIN
        if ($passwordAge >= 30 && !$isAdmin) {

            // 🔥 MISE À JOUR AUTOMATIQUE DES CHAMPS EN BASE
            $user->update([
                'password_expired' => true,
                'must_change_password' => true
            ]);

            // Déconnexion immédiate
            Auth::logout();
            $request->session()->invalidate();

            return response()->json([
                'error' => true,
                'message' => '🔒 Votre mot de passe a expiré (plus de 30 jours). Contactez l\'administrateur pour le réinitialiser.',
                'redirect' => route('password.expired') // Optionnel : rediriger vers une page dédiée
            ], 403);
        }

        // ✅ CAS 2 : MOT DE PASSE VA EXPIRER BIENTÔT (entre 25 et 29 jours)
        if ($passwordAge >= 25 && $passwordAge < 30 && !$isAdmin) {
            $daysLeft = 30 - $passwordAge;

            return response()->json([
                'success' => true,
                'message' => 'Connexion réussie',
                'warning' => "⚠️ Votre mot de passe expire dans {$daysLeft} jour(s). Pensez à le changer rapidement.",
                'days_left' => $daysLeft,
                'show_warning' => true // Pour afficher un toast côté frontend
            ]);
        }

        // ✅ CAS 3 : MOT DE PASSE VALIDE
        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie'
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('login');
    }
}

