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
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\CourrierController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect()->route('login-view');
});


    Route::get('login', [AuthController::class, 'loginView'])->name('login-view');
    Route::post('login', [AuthController::class, 'login'])->name('login');









Route::middleware(['auth'])->group(function () {



    // Afficher le formulaire de changement
    Route::get('change-password', [PasswordController::class, 'showChangeForm'])
        ->name('password.change.form');

    // Traiter le changement
    Route::post('change-password', [PasswordController::class, 'change'])
        ->name('password.change');

          Route::get('password-expired', [PasswordController::class, 'showExpiredPage'])
        ->name('password.expired');

    // Demander une réinitialisation à l'admin
    Route::post('password-request-reset', [PasswordController::class, 'requestReset'])
        ->name('password.request-reset');

 // Réinitialiser le mot de passe d'un utilisateur
    Route::patch('/users/{id}/reset-password', [UserController::class, 'resetPassword'])
        ->name('users.resetPassword');

    Route::get('logout', [AuthController::class, 'logout'])->name('logout');








    Route::get('register', [AuthController::class, 'registerView'])->name('register-view');
    Route::post('register', [AuthController::class, 'register'])->name('register');


    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', [PageController::class, 'loadPage'])->name('dashboard');
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


            Route::get('/courriers/{numCour}/saisie-modal', [CourrierController::class, 'saisieModal'])->name('courriers.saisie-modal');
Route::post('/factures/save-by-courrier', [CourrierController::class, 'saveFactureByCourrier'])->name('factures.save-by-courrier');

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
            Route::post('/ligne_suivi/valider', [LigneSuiviController::class, 'transmettreALaTreso'])->name('ligne_suivi.valider');
            Route::get('/ligne_suivi/regler/{id}', [LigneSuiviController::class, 'showReglementForm'])->name('ligne_suivi.regler.form');
            Route::post('/ligne_suivi/regler/{id}', [LigneSuiviController::class, 'regler'])->name('ligne_suivi.regler');

            Route::get('/ligne-suivi/edit-modal', [LigneSuiviController::class, 'editModal'])->name('ligne_suivi.editModal');
            Route::put('/ligne-suivi/{id}', [LigneSuiviController::class, 'update'])->name('ligne_suivi.update');

            // web.php
           
  Route::post('/correction/update', [LigneSuiviController::class, 'updateCorrection'])->name('correction.update');
  Route::post('/annulation/update', [LigneSuiviController::class, 'updateAnnulation'])->name('annulation.update');

Route::get('/page/side-menu/light/situation-prestataire', [PageController::class, 'loadPage'])->name('situation-prestataire');

// Routes pour la page situation-prestataire
Route::get('/situation-prestataire/prestataires', [PageController::class, 'getPrestataires'])->name('situation-prestataire.prestataires');
Route::get('/situation-prestataire/data', [PageController::class, 'getSituationData'])->name('situation-prestataire.data');

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
            Route::get('/dashboard/data', [PageController::class, 'getDashboardDataAjax'])->name('dashboard.data');




            // Page principale de transmission/retour
                Route::get('/transmission-retour-factures', [LigneSuiviController::class, 'indexTransmission'])
                    ->name('transmission.index');

                // Actions batch (multiples factures à la fois)
                Route::post('/factures/transmit-batch', [LigneSuiviController::class, 'transmitBatch'])
                    ->name('transmit.batch');

                Route::post('/factures/retour-batch', [LigneSuiviController::class, 'retourBatch'])
                    ->name('retour.batch');



                    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
                Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
                Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');
                Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unreadCount');
    });
});
