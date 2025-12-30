<?php

/**
 * ========================================
 * ÉTAPE 4 : PASSWORD CONTROLLER COMPLET
 * ========================================
 *
 * Commande : php artisan make:controller PasswordController
 * Fichier : app/Http/Controllers/PasswordController.php
 *
 * Gère tout ce qui concerne le changement de mot de passe
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Carbon\Carbon;

class PasswordController extends Controller
{
    /**
     * Afficher le formulaire de changement de mot de passe
     * Route : GET /change-password
     */


    public function showChangeForm()
    {
        return view('login.change-password', [
        'layout' => 'login',
        'theme' => 'light',  // ✅ Ajouté
        'page_name' => 'change-password',  // ✅ Ajouté
            'isFirstTime' => true
        ]);
    }

    /**
     * Afficher la page "Mot de passe expiré"
     * Route : GET /password-expired
     */
    public function showExpiredPage()
    {
        return view('auth.password-expired', [
            'layout' => 'login'
        ]);
    }

    /**
     * Traiter le changement de mot de passe
     * Route : POST /change-password
     */
    public function change(Request $request)
    {
        // Validation des données
        $request->validate([
            'current_password' => ['required'],
            'password' => [
                'required',
                'confirmed',
                Password::min(12)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ], [
            // Messages personnalisés en français
            'current_password.required' => 'Veuillez saisir votre mot de passe actuel.',
            'password.required' => 'Le nouveau mot de passe est obligatoire.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 12 caractères.',
            'password.mixed' => 'Le mot de passe doit contenir des majuscules et minuscules.',
            'password.numbers' => 'Le mot de passe doit contenir au moins un chiffre.',
            'password.symbols' => 'Le mot de passe doit contenir au moins un caractère spécial.',
            'password.uncompromised' => 'Ce mot de passe est trop commun. Choisissez-en un plus sécurisé.',
        ]);

        // Récupérer l'utilisateur connecté
        $user = Auth::user();

        // Vérifier que l'ancien mot de passe est correct
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Le mot de passe actuel est incorrect.'
            ]);
        }

        // Mettre à jour le mot de passe
        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
            'password_changed_at' => now(),
            'password_expired' => false,
            'password_expiry_notified_at' => null,
        ]);

        // Rediriger avec un message de succès
         return redirect()->back()
            ->with('success', '✅ Votre mot de passe a été changé avec succès.');
    }

    /**
     * Demander une réinitialisation à l'admin
     * Route : POST /password-request-reset
     */
    public function requestReset(Request $request)
    {

    }
}
