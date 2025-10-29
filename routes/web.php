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
use App\Http\Controllers\FactureController;
use App\Http\Controllers\CourierSanteIndivController;

use App\Http\Controllers\DashboardController;

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



Route::post('transmit', [LigneSuiviController::class, 'transmitBatch'])->name('transmit.batch');
Route::post('retour', [LigneSuiviController::class, 'handleRetourBatch'])->name('retour.batch');

// Individual routes (WITH {id} parameter)
Route::post('{id}/transmit', [LigneSuiviController::class, 'transmitIndividual'])->name('transmit.individual');
Route::post('{id}/retour', [LigneSuiviController::class, 'handleRetourIndividual'])->name('retour.individual');

// Modals (AJAX GET)
Route::get('{id}/transmission-modal', [LigneSuiviController::class, 'loadTransmissionModal'])->name('transmission.modal');
Route::get('{id}/retour-modal', [LigneSuiviController::class, 'loadRetourModal'])->name('retour.modal');
Route::get('{id}/edit-modal', [LigneSuiviController::class, 'editModal'])->name('edit.modal');

// Edit/Update
Route::get('{id}/edit', [LigneSuiviController::class, 'edit'])->name('ligne_suivi.edit');
Route::match(['put', 'patch'], '{id}', [LigneSuiviController::class, 'update'])->name('ligne_suivi.update');


Route::get('/detailReseau', [LigneSuiviController::class, 'detailReseau'])->name('detailReseau.index');

Route::get('/courriers/fiche/{numCour}', [CourierSanteIndivController::class, 'printFiche'])
    ->name('courriers.printFiche');
// Ajoutez ceci (ajustez si vous avez un groupe de routes avec middleware auth)
Route::get('/courriers/{numCour}/saisie-facture', [CourierSanteIndivController::class, 'saisieFactureModal'])->name('courriers.saisieFacture');

Route::get('/courriers/{numCour}/saisie-facture-modal', [CourierSanteIndivController::class, 'saisieFactureModal'])
    ->name('courriers.saisie-facture-modal');

Route::get('/courriers/{numCour}/saisie-form', [CourierSanteIndivController::class, 'getSaisieForm'])->name('courriers.saisieForm');

// Routes pour courriers (ajoutez à la fin de web.php, dans le groupe web/auth si vous en avez)
Route::get('/courriers', [CourierSanteIndivController::class, 'index'])->name('courriers.index');
Route::get('/courriers/create', [CourierSanteIndivController::class, 'create'])->name('courriers.create');
Route::post('/courriers', [CourierSanteIndivController::class, 'store'])->name('courriers.store');
Route::get('/courriers/{numCour}/print-fiche', [CourierSanteIndivController::class, 'printFiche'])->name('courriers.printFiche');
Route::get('/courriers/{numCour}/saisie-facture', [CourierSanteIndivController::class, 'saisieFactureModal'])->name('courriers.saisieFacture');

 Route::get('/courriers', [CourierSanteIndivController::class, 'index'])->name('courriers.index');
    Route::get('/courriers/create', [CourierSanteIndivController::class, 'create'])->name('courriers.create');
    Route::post('/courriers', [CourierSanteIndivController::class, 'store'])->name('courriers.store');


Route::post('/courriers/store-ligne-suivi', [CourierSanteIndivController::class, 'storeLigneSuivi'])->name('courriers.storeLigneSuivi');


Route::get('/courriers/instance', [CourierSanteIndivController::class, 'indexCourriersInstance'])->name('courriers.instance');


Route::get('/factures', [FactureController::class, 'index'])->name('factures.index');


 Route::get('/factures/listing', [FactureController::class, 'index'])
        ->name('factures.listing');




    // Routes pour LigneSuiviController (gardez vos existantes, mais corrigez si conflictuelles)
Route::get('/ligne-suivi', [LigneSuiviController::class, 'index'])->name('ligne_suivi.index');
Route::post('/ligne-suivi', [LigneSuiviController::class, 'store'])->name('ligne_suivi.store'); // Utilisez LigneSuiviController ici, pas Courier
Route::put('/ligne_suivi/{id}', [LigneSuiviController::class, 'update'])->name('ligne_suivi.update');
Route::put('/ligne_suivi/{id}/traiter', [LigneSuiviController::class, 'traiter'])->name('ligne_suivi.traiter');
Route::post('/ligne_suivi/{id}/rejeter', [LigneSuiviController::class, 'rejeter'])->name('ligne_suivi.rejeter');
Route::put('/ligne_suivi/{id}/cloturer', [LigneSuiviController::class, 'cloturer'])->name('ligne_suivi.cloturer');
Route::post('/ligne_suivi/valider', [LigneSuiviController::class, 'valider'])->name('ligne_suivi.valider');
Route::get('/ligne_suivi/regler/{id}', [LigneSuiviController::class, 'showReglementForm'])->name('ligne_suivi.regler.form');
Route::post('/ligne_suivi/regler/{id}', [LigneSuiviController::class, 'regler'])->name('ligne_suivi.regler');

Route::get('/ligne-suivi/edit-modal', [LigneSuiviController::class, 'editModal'])->name('ligne_suivi.editModal');
Route::put('/ligne-suivi/{id}', [LigneSuiviController::class, 'update'])->name('ligne_suivi.update');

// web.php
Route::get('/ligne_suivi/regler/{id}', [LigneSuiviController::class, 'showReglementForm'])->name('ligne_suivi.regler.form');
Route::post('/ligne_suivi/regler/{id}', [LigneSuiviController::class, 'regler'])->name('ligne_suivi.regler');



// Routes pour CourierSanteIndivController (nouvelles/corrigées – remplacez les doublons)
Route::get('/courriers', [CourierSanteIndivController::class, 'index'])->name('courriers.index');
Route::get('/courriers/create', [CourierSanteIndivController::class, 'create'])->name('courriers.create');
Route::post('/courriers', [CourierSanteIndivController::class, 'store'])->name('courriers.store');

// Route pour imprimer la fiche (ajustée pour cohérence)
Route::get('/courriers/{numCour}/print-fiche', [CourierSanteIndivController::class, 'printFiche'])->name('courriers.printFiche');

// Route pour charger le modal saisie facture via AJAX (GET)
Route::get('/courriers/{numCour}/saisie-facture-modal', [CourierSanteIndivController::class, 'saisieFactureModal'])->name('courriers.saisie-facture-modal');


Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');



Route::get('/gestion-utilisateurs', [UserController::class, 'index'])->name('users.index');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
Route::put('/gestion-utilisateurs/{id}', [UserController::class, 'update'])->name('users.update');
Route::patch('/users/{id}/activer', [UserController::class, 'activate'])->name('users.activate');
Route::patch('/users/{id}/desactiver', [UserController::class, 'deactivate'])->name('users.deactivate');





Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/data', [DashboardController::class, 'getData'])->name('dashboard.data');
Route::get('/dashboard/data', [PageController::class, 'getDashboardDataAjax'])->name('dashboard.data');
});
