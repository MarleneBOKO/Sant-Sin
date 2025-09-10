<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Affiche la page de connexion
    public function loginView()
    {
        return view('login.main', [
            'theme' => 'light',
            'page_name' => 'auth-login',
            'layout' => 'login'
        ]);
    }

    // Traite la connexion
    public function login(Request $request)
    {
        $credentials = $request->validate([
    'name' => ['required', 'string'],
    'password' => ['required'],
]);


        if (!Auth::attempt($credentials, $request->boolean('remember_me'))) {
            return response()->json([
                'message' => 'Nom ou mot de passe incorrect.'
            ], 422);
        }

        $request->session()->regenerate();

        return response()->json(['message' => 'Connexion réussie']);
    }

    // Déconnexion
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('login');
    }
}
