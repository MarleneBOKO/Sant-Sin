<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\DroitController;
use App\Http\Controllers\MenuProfilController;
use App\Http\Controllers\MenuProfilDroitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LigneSuiviController;
Route::get('/', function () {
    return redirect()->route('login-view');
});


    Route::get('login', [AuthController::class, 'loginView'])->name('login-view');
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::get('register', [AuthController::class, 'registerView'])->name('register-view');
    Route::post('register', [AuthController::class, 'register'])->name('register');


Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [PageController::class, 'loadPage'])->name('dashboard');
    Route::get('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('page/{layout}/{theme}/{pageName}', [PageController::class, 'loadPage'])->name('page');

Route::get('/gestion-profils', [ProfilController::class, 'index'])->name('profils.index');
Route::get('/profils/create', [ProfilController::class, 'create'])->name('profils.create');
Route::post('/profils', [ProfilController::class, 'store'])->name('profils.store');
Route::get('/profils/{id}/edit', [ProfilController::class, 'edit'])->name('profils.edit');
Route::put('/profils/{id}', [ProfilController::class, 'update'])->name('profils.update');
Route::delete('/profils/{id}', [ProfilController::class, 'destroy'])->name('profils.destroy');

    // Menus
    Route::get('/menus', [MenuController::class, 'index']);
    Route::post('/menus', [MenuController::class, 'store']);
    Route::put('/menus/{id}', [MenuController::class, 'update']);
    Route::delete('/menus/{id}', [MenuController::class, 'destroy']);

    // Droits
    Route::get('/droits', [DroitController::class, 'index']);
    Route::post('/droits', [DroitController::class, 'store']);
    Route::delete('/droits/{id}', [DroitController::class, 'destroy']);

    // Association Menu-Profil
    Route::post('/menu-profil', [MenuProfilController::class, 'store']);
    Route::delete('/menu-profil/{id}', [MenuProfilController::class, 'destroy']);

    // Association MenuProfil-Droit
    Route::post('/menu-profil-droit', [MenuProfilDroitController::class, 'store']);
    Route::delete('/menu-profil-droit/{id}', [MenuProfilDroitController::class, 'destroy']);

// web.php
Route::put('/ligne_suivi/{id}/traiter', [LigneSuiviController::class, 'traiter'])->name('ligne_suivi.traiter');
// Route POST pour rejeter une facture
Route::post('/ligne_suivi/{id}/rejeter', [LigneSuiviController::class, 'rejeter'])->name('ligne_suivi.rejeter');

Route::put('/ligne_suivi/{id}/cloturer', [LigneSuiviController::class, 'cloturer'])->name('ligne_suivi.cloturer');


// Affichage des lignes de suivi (page principale)
Route::get('/ligne-suivi', [LigneSuiviController::class, 'index'])->name('ligne_suivi.index');

// Enregistrement d'une nouvelle ligne de facture
Route::post('/ligne-suivi', [LigneSuiviController::class, 'store'])->name('ligne_suivi.store');
// Chargement du modal d'édition via AJAX (si utilisé)
Route::get('/ligne-suivi/edit-modal', [LigneSuiviController::class, 'editModal'])->name('ligne_suivi.editModal');
Route::put('/ligne-suivi/{id}', [LigneSuiviController::class, 'update'])->name('ligne_suivi.update');




Route::resource('ligne_suivi', LigneSuiviController::class)->only(['index', 'store', 'show', 'update']);

// Si vous avez besoin des autres routes
Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');



Route::get('/gestion-utilisateurs', [UserController::class, 'index'])->name('users.index');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
Route::put('/gestion-utilisateurs/{id}', [UserController::class, 'update'])->name('users.update');
Route::patch('/users/{id}/activer', [UserController::class, 'activate'])->name('users.activate');
Route::patch('/users/{id}/desactiver', [UserController::class, 'deactivate'])->name('users.deactivate');

});
