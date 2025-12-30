<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Faker\Factory as Faker;
use Faker\Factory as FakerFactory;
use App\Services\MenuService;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Service;
use App\Models\Profil;
use App\Models\LigneSuivi;
use App\Models\Partenaire;
use App\Models\parametres;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\CourierSanteIndiv;



class PageController extends Controller
{
    /**
     * Show specified view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

protected $menuService;

public function __construct(MenuService $menuService)
{
    $this->menuService = $menuService;
}



public function loadPage($layout = 'side-menu', $theme = 'light', $pageName = 'dashboard')
{

     $side_menu = $this->menuService->getMenusForAuthenticatedUser();
    $activeMenu = $this->activeMenu($layout, $pageName);
    $fakers = [];
    $faker = FakerFactory::create();
   $time = $faker->time();

 // Extraction des pages actives
    $first_page_name = $side_menu[0]['page_name'] ?? null;
    $second_page_name = null;
    $third_page_name = null;


        $top_menu = $this->topMenu() ?? [];
        $simple_menu = $this->simpleMenu() ?? [];

        $annees = [];

    for ($i = 0; $i < 10; $i++) {
        $fakers[] = [
            'photos' => ['profile-1.jpg', 'profile-2.jpg', 'profile-3.jpg'],
            'images' => ['product-1.jpg', 'product-2.jpg', 'product-3.jpg'],
            'true_false' => [rand(0, 1)],
             'formatted_times' => [date('H:i', strtotime($time))],
            'totals' => [
                'orders' => rand(100, 1000),
                'purchases' => rand(50, 500),
                'reviews' => rand(10, 100),
            ],

            'stocks' => [rand(1, 100)],

            'files' => [
                [
                    'type' => $faker->randomElement(['Empty Folder', 'Folder', 'Image', 'File']),
                    'file_name' => $faker->randomElement(['report.pdf', 'photo.jpg', 'archive.zip']),
                    'size' => $faker->randomElement(['200 KB', '1.5 MB', '3.2 MB', '875 KB']),
                ]
            ],
            'foods' => [
                [
                    'image' => $faker->randomElement([
                        'preview-1.jpg', 'preview-2.jpg', 'preview-3.jpg', 'preview-4.jpg',
                    ]),
                    'name' => $faker->words(2, true),
                ],
            ],
           'users' => [
                ['name' => $faker->name, 'email' => $faker->email],
                ['name' => $faker->name, 'email' => $faker->email],
                ['name' => $faker->name, 'email' => $faker->email],
            ],

            'dates' => [$faker->date(), $faker->date(), $faker->date()],
            'products' => [
                ['name' => $faker->word, 'category' => $faker->word],
            ],

            'times' => [$faker->time()],
            'news' => [
                [
                    'super_short_content' => $faker->words(3, true),
                     'title' => $faker->sentence(3),
                    'short_content' => $faker->sentence,
                ],
            ],
            'jobs' => [$faker->jobTitle],
            'notification_count' => rand(1, 5), // 🔧 Ajouté pour éviter l’erreur dans chat.blade.php
        ];
    }
if ($pageName === 'gestion-utilisateurs') {
        $users = User::with(['service', 'profil'])->paginate(10);
    $services = Service::all(); // <-- Ajouté
    $profils = Profil::all();   // <-- Ajouté

        $fakers = [];
        $annees = [];
    return view('pages.' . $pageName, [
        'top_menu' => $top_menu,
            'side_menu' => $side_menu,
            'simple_menu' => $simple_menu,
            'first_page_name' => $activeMenu['first_page_name'],
            'second_page_name' => $activeMenu['second_page_name'],
            'third_page_name' => $activeMenu['third_page_name'],
            'page_name' => $pageName,
            'theme' => $theme,
            'layout' => $layout,
        'users' => $users,
        'services' => $services, // <-- Ajouté
        'profils' => $profils,   // <-- Ajouté
        'fakers' => $fakers,
    ]);
}

  if ($pageName === 'change-password') {

        $fakers = [];
        $annees = [];
        return view('pages.' . $pageName, [
            'top_menu' => $top_menu,
            'side_menu' => $side_menu,
            'simple_menu' => $simple_menu,
            'first_page_name' => $activeMenu['first_page_name'],
            'second_page_name' => $activeMenu['second_page_name'],
            'third_page_name' => $activeMenu['third_page_name'],
            'page_name' => $pageName,
            'theme' => $theme,
            'layout' => 'side-menu',
            'fakers' => $fakers,
            'isFirstTime' => session('isFirstTime', false), // Si nécessaire
        ]);
    }


   if ($pageName === 'depot-individuel') {
    $courriers = \App\Models\CourierSanteIndiv::orderBy('datereception', 'desc')->paginate(10);

        $fakers = [];
        $annees = [];
         return view('pages.' . $pageName, [
       'top_menu' => $top_menu,
            'side_menu' => $side_menu,
            'simple_menu' => $simple_menu,
            'first_page_name' => $activeMenu['first_page_name'],
            'second_page_name' => $activeMenu['second_page_name'],
            'third_page_name' => $activeMenu['third_page_name'],
            'page_name' => $pageName,
            'theme' => $theme,
            'layout' => $layout,
         'courriers' => $courriers,  // <-- Transmission de la variable
        'fakers' => $fakers,
    ]);
    }





    if ($pageName === 'gestion-profils') {
   $profils = Profil::paginate(10);

        $fakers = [];
        $annees = [];
    return view('pages.' . $pageName, [
       'top_menu' => $top_menu,
            'side_menu' => $side_menu,
            'simple_menu' => $simple_menu,
            'first_page_name' => $activeMenu['first_page_name'],
            'second_page_name' => $activeMenu['second_page_name'],
            'third_page_name' => $activeMenu['third_page_name'],
            'page_name' => $pageName,
            'theme' => $theme,
            'layout' => $layout,
        'profils' => $profils,   // <-- Transmission de la variable
        'fakers' => $fakers,
    ]);

}

if ($pageName === 'gestion-factures') {
       $user = auth()->user();
       $profilCode = $user->profil->code_profil ?? null;  // Utilise le code_profil au lieu de l'id

       // Récupérer le paramètre de recherche depuis la requête GET
       $search = request()->get('search', '');

       $fakers = [];
       $annees = [];

       // Début de la requête commune avec jointure pour type
       $query = LigneSuivi::whereNotNull('Date_Enregistrement')
           ->leftJoin('partenaires', 'Ligne_Suivi.Code_Partenaire', '=', 'partenaires.id') // Jointure pour accéder au type
           ->with(['prestataire', 'souscripteur']); // Charge les relations prestataire et souscripteur

       // 🔀 Filtrage selon le code_profil (adapté pour Code_Partenaire et type)
       if (in_array($profilCode, ['RSI', 'RRSI'])) {
           // Régleur Sinistre Individuel / Responsable : Nom_Assure non null et partenaire de type 'souscripteur' ou null
           $query->whereNotNull('Nom_Assure')
                 ->where(function ($q) {
                     $q->whereNull('Ligne_Suivi.Code_Partenaire')
                       ->orWhere('partenaires.type', 'souscripteur');
                 });
       } elseif (in_array($profilCode, ['RSTP', 'RRSTP'])) {
           // Régleur Sinistre Tiers Payant / Responsable : partenaire de type 'prestataire'
           $query->whereNotNull('Ligne_Suivi.Code_Partenaire')
                 ->where('partenaires.type', 'prestataire');
       } elseif (in_array($profilCode, ['RSIN', 'ADMIN'])) {
           // Régleur Sinistre ou Admin → voir les deux types
           $query->where(function ($q) {
               $q->where(function ($sub) {
                   $sub->whereNotNull('Nom_Assure')
                       ->where(function ($inner) {
                           $inner->whereNull('Ligne_Suivi.Code_Partenaire')
                                 ->orWhere('partenaires.type', 'souscripteur');
                       });
               })->orWhere(function ($sub) {
                   $sub->whereNotNull('Ligne_Suivi.Code_Partenaire')
                       ->where('partenaires.type', 'prestataire');
               });
           });
       }

       if (!empty($search)) {
           $query->where(function ($q) use ($search) {
               $q->where('Nom_Assure', 'LIKE', '%' . $search . '%')
                 ->orWhere('Reference_Facture', 'LIKE', '%' . $search . '%')
                 ->orWhere('Numero_Reception', 'LIKE', '%' . $search . '%')
                 ->orWhereHas('partenaire', function ($subQuery) use ($search) {
                     $subQuery->where('nom', 'LIKE', '%' . $search . '%'); // Recherche dans partenaire.nom (quel que soit le type)
                 });
           });
       }

       $query->orderBy('Date_Enregistrement', 'desc');  // Modifié : Tri par date de création (descendant)

       // Récupération des factures (sans filtrage sur 'rejete')
       $factures = $query->paginate(10);

       // 🔥 LOGS POUR DÉBOGUER
       \Log::info('=== DÉBOGAGE GESTION-FACTURES ===', [
           'profilCode' => $profilCode,
           'factures_count' => $factures->count(),
           'query_sql' => $query->toSql(),
           'query_bindings' => $query->getBindings(),
       ]);

       if ($factures->count() > 0) {
           $firstFacture = $factures->first();
           \Log::info('Première facture chargée', [
               'Id_Ligne' => $firstFacture->Id_Ligne,
               'Code_Partenaire' => $firstFacture->Code_Partenaire,
               'Nom_Assure' => $firstFacture->Nom_Assure,
               'Reference_Facture' => $firstFacture->Reference_Facture,
               'prestataire_loaded' => $firstFacture->relationLoaded('prestataire'),
               'prestataire_data' => $firstFacture->prestataire ? $firstFacture->prestataire->toArray() : null,
               'souscripteur_loaded' => $firstFacture->relationLoaded('souscripteur'),
               'souscripteur_data' => $firstFacture->souscripteur ? $firstFacture->souscripteur->toArray() : null,
           ]);
       }

       $souscripteurs = Partenaire::souscripteurs()->orderBy('nom')->get(); // Assure-toi que cette méthode existe ou remplace par where('type', 'souscripteur')
       $prestataires = Partenaire::prestataires()->orderBy('nom')->get(); // Assure-toi que cette méthode existe ou remplace par where('type', 'prestataire')

       $moisList = DB::table('parametres')
           ->where('typaram', 'MoisFacture')
           ->orderByDesc('codtyparam')
           ->select('codtyparam as Id_mois', 'libelleparam as libelle_mois')
           ->get();

       $annees = DB::table('parametres')
           ->where('typaram', 'AnneFacture')
           ->orderByDesc('codtyparam')
           ->select('codtyparam as Id_annee', 'libelleparam as libelle_annee')
           ->get();

       return view('pages.' . $pageName, [
           'top_menu' => $top_menu,
           'side_menu' => $side_menu,
           'simple_menu' => $simple_menu,
           'first_page_name' => $activeMenu['first_page_name'],
           'second_page_name' => $activeMenu['second_page_name'],
           'third_page_name' => $activeMenu['third_page_name'],
           'page_name' => $pageName,
           'theme' => $theme,
           'layout' => $layout,
           'factures' => $factures,
           'souscripteurs' => $souscripteurs,
           'prestataires' => $prestataires,
           'moisList' => $moisList,
           'annees' => $annees,
           'fakers' => $fakers,
           'profilCode' => $profilCode,  // Changé de 'profil_id' à 'profilCode' pour cohérence
       ]);
   }



   if ($pageName === 'listing-reporting') {
       $factures = Facture::query()
        // ... vos conditions/filtres ici ...
        ->paginate(10)
        ->appends(request()->query());  

            $fakers = [];
            $annees = [];
    return view('pages.' . $pageName, [
       'top_menu' => $top_menu,
            'side_menu' => $side_menu,
            'simple_menu' => $simple_menu,
            'first_page_name' => $activeMenu['first_page_name'],
            'second_page_name' => $activeMenu['second_page_name'],
            'third_page_name' => $activeMenu['third_page_name'],
            'page_name' => $pageName,
            'theme' => $theme,
            'layout' => $layout,
        'factures' => $factures,
        'fakers' => $fakers,
    ]);
}


if ($pageName === 'correction-facture') {
    $user = auth()->user();
    $profilCode = $user->profil->code_profil ?? null;

    // 🔥 MODIFIÉ : Autoriser l'admin en plus des responsables
    if (!in_array($profilCode, ['RRSI', 'RRSTP', 'ADMIN'])) {
        abort(403, 'Accès refusé. Cette page est réservée aux responsables et admins.');
    }

    $fakers = [];
    $annees = [];

    // Requête selon le profil (basée sur le code PHP original)
    $query = LigneSuivi::whereNotNull('Date_Enregistrement')
        ->leftJoin('partenaires', 'Ligne_Suivi.Code_Partenaire', '=', 'partenaires.id')
        ->with(['prestataire', 'souscripteur']);

    if ($profilCode === 'RRSI') {
        // Factures individuelles : Code_Partenaire non null, type souscripteur, montant rejeté < 0
        $query->whereNotNull('Ligne_Suivi.Code_Partenaire')
              ->where('partenaires.type', 'souscripteur')
              ->whereRaw('(ISNULL(Montant_Ligne, 0) - ISNULL(Montant_Reglement, 0)) < 0');
    } elseif ($profilCode === 'RRSTP') {
        // Factures Tiers-Payant : Code_Partenaire non null, type prestataire, statut != 4, rejete = 0, montant rejeté < 0
        $query->whereNotNull('Ligne_Suivi.Code_Partenaire')
              ->where('partenaires.type', 'prestataire')
              ->whereNotIn('Statut_Ligne', [4])
              ->where('rejete', 0)
              ->whereRaw('(ISNULL(Montant_Ligne, 0) - ISNULL(Montant_Reglement, 0)) < 0');
    } elseif ($profilCode === 'ADMIN') {
        // 🔥 AJOUTÉ : Admin voit tout : Code_Partenaire non null, montant rejeté < 0 (sans filtre par type)
        $query->whereNotNull('Ligne_Suivi.Code_Partenaire')
              ->whereRaw('(ISNULL(Montant_Ligne, 0) - ISNULL(Montant_Reglement, 0)) < 0');
    }

    $query->orderBy('Date_Enregistrement', 'desc');
    $factures = $query->get();
    // 🔥 LOGS POUR DÉBOGUER
    \Log::info('=== DÉBOGAGE CORRECTION-FACTURE ===', [
        'profilCode' => $profilCode,
        'factures_count' => $factures->count(),
        'query_sql' => $query->toSql(),
        'query_bindings' => $query->getBindings(),
    ]);

    if ($factures->count() > 0) {
        $firstFacture = $factures->first();
        \Log::info('Première facture correction', [
            'Id_Ligne' => $firstFacture->Id_Ligne,
            'Code_Partenaire' => $firstFacture->Code_Partenaire,
            'Nom_Assure' => $firstFacture->Nom_Assure,
            'Montant_Ligne' => $firstFacture->Montant_Ligne,
            'Montant_Reglement' => $firstFacture->Montant_Reglement,
            'rejete' => $firstFacture->rejete,
            'Statut_Ligne' => $firstFacture->Statut_Ligne,
        ]);
    }

    return view('pages.' . $pageName, [
        'top_menu' => $top_menu,
        'side_menu' => $side_menu,
        'simple_menu' => $simple_menu,
        'first_page_name' => $activeMenu['first_page_name'],
        'second_page_name' => $activeMenu['second_page_name'],
        'third_page_name' => $activeMenu['third_page_name'],
        'page_name' => $pageName,
        'theme' => $theme,
        'layout' => $layout,
        'factures' => $factures,
        'profilCode' => $profilCode,
        'fakers' => $fakers,
    ]);
}

if ($pageName === 'annulation-facture') {
    $user = auth()->user();
    $profilCode = $user->profil->code_profil ?? null;

    // 🔥 MODIFIÉ : Autoriser l'admin
    if (!in_array($profilCode, ['RRSI', 'RRSTP', 'ADMIN'])) {
        abort(403, 'Accès refusé. Cette page est réservée aux responsables et admins.');
    }

    $fakers = [];
    $annees = [];

    // Requête selon le profil (basée sur le code PHP original : factures non clôturées, non annulées)
    $query = LigneSuivi::whereNotNull('Date_Enregistrement')
        ->leftJoin('partenaires', 'Ligne_Suivi.Code_Partenaire', '=', 'partenaires.id')
        ->with(['prestataire', 'souscripteur']);

    if ($profilCode === 'RRSI') {
        // Factures individuelles : Code_Partenaire non null, type souscripteur, annuler=0, statut not in (4,8), rejete=0
        $query->whereNotNull('Ligne_Suivi.Code_Partenaire')
              ->where('partenaires.type', 'souscripteur')
              ->whereNotIn('Statut_Ligne', [4, 8])
              ->where('rejete', 0);
    } elseif ($profilCode === 'RRSTP') {
        // Factures Tiers-Payant : Code_Partenaire null, annuler=0, statut not in (4,8), rejete=0
        $query->whereNotNull('Ligne_Suivi.Code_Partenaire')
             ->where('partenaires.type', 'prestataire')
              ->whereNotIn('Statut_Ligne', [4, 8])
              ->where('rejete', 0);
    } elseif ($profilCode === 'ADMIN') {
        // 🔥 AJOUTÉ : Admin voit tout : annuler=0, statut not in (4,8), rejete=0 (sans filtre par type)
        $query
              ->whereNotIn('Statut_Ligne', [4, 8])
              ->where('rejete', 0);
    }

    $query->orderBy('Date_Enregistrement', 'desc');


        $factures = $query->paginate(10);


    // 🔥 LOGS POUR DÉBOGUER
    \Log::info('=== DÉBOGAGE ANNULATION-FACTURE ===', [
        'profilCode' => $profilCode,
        'factures_count' => $factures->count(),
        'query_sql' => $query->toSql(),
        'query_bindings' => $query->getBindings(),
    ]);

    if ($factures->count() > 0) {
        $firstFacture = $factures->first();
        \Log::info('Première facture annulation', [
            'Id_Ligne' => $firstFacture->Id_Ligne,
            'Code_Partenaire' => $firstFacture->Code_Partenaire,
            'Nom_Assure' => $firstFacture->Nom_Assure,
            'annuler' => $firstFacture->annuler,
            'rejete' => $firstFacture->rejete,
            'Statut_Ligne' => $firstFacture->Statut_Ligne,
        ]);
    }

    return view('pages.' . $pageName, [
        'top_menu' => $top_menu,
        'side_menu' => $side_menu,
        'simple_menu' => $simple_menu,
        'first_page_name' => $activeMenu['first_page_name'],
        'second_page_name' => $activeMenu['second_page_name'],
        'third_page_name' => $activeMenu['third_page_name'],
        'page_name' => $pageName,
        'theme' => $theme,
        'layout' => $layout,
        'factures' => $factures,
        'profilCode' => $profilCode,
        'fakers' => $fakers,
    ]);
}

if ($pageName === 'situation-prestataire') {
    $annees = DB::connection('sqlsrv')
        ->table('Ligne_Suivi')
        ->whereNotNull('Annee_Facture')
        ->distinct()
        ->orderByDesc('Annee_Facture')
        ->pluck('Annee_Facture')
        ->take(10);

    // Récupérer les prestataires (partenaires de type 'prestataire')
    $prestataires = DB::connection('sqlsrv')
        ->table('partenaires')
        ->where('type', 'prestataire')
        ->select('id', 'nom', 'code_type_prestataire', 'coutierG')
        ->orderBy('nom')
        ->get();

    return view('pages.' . $pageName, [
        'top_menu' => $top_menu,
        'side_menu' => $side_menu,
        'simple_menu' => $simple_menu,
        'first_page_name' => $activeMenu['first_page_name'],
        'second_page_name' => $activeMenu['second_page_name'],
        'third_page_name' => $activeMenu['third_page_name'],
        'page_name' => $pageName,
        'theme' => $theme,
        'layout' => $layout,
        'annees' => $annees,
        'prestataires' => $prestataires,
        'fakers' => $fakers,
    ]);
}



// 🔥 CONDITION NOUVELLE : Liste des Courriers Santé en Instance (non-traités ou partiels)
if ($pageName === 'dashboard') {
    $annee = request()->get('annee', date('Y'));

    $annees = DB::connection('sqlsrv')
        ->table('Ligne_Suivi')
        ->whereNotNull('Annee_Facture')
        ->distinct()
        ->orderByDesc('Annee_Facture')
        ->pluck('Annee_Facture')
        ->take(10);

    try {
        $columns = DB::connection('sqlsrv')->select("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME = 'partenaires'
        ");
        Log::info('Colonnes de la table partenaires:', array_column($columns, 'COLUMN_NAME'));

        $types = DB::connection('sqlsrv')->select("
            SELECT type, COUNT(*) as count
            FROM partenaires
            GROUP BY type
        ");
        Log::info('Types de partenaires:', (array)$types);

        $lignesAvecPartenaire = DB::connection('sqlsrv')->selectOne("
            SELECT
                COUNT(*) as total,
                COUNT(CASE WHEN Code_Partenaire IS NOT NULL THEN 1 END) as avec_partenaire,
                COUNT(CASE WHEN Code_Partenaire IS NULL THEN 1 END) as sans_partenaire
            FROM Ligne_Suivi
        ");
        Log::info('Lignes de suivi:', (array)$lignesAvecPartenaire);
    } catch (\Exception $e) {
        Log::error('Erreur diagnostic: ' . $e->getMessage());
    }

    $query = "
        SELECT
            SUM(CASE WHEN Numero_demande IS NULL THEN Montant_Ligne ELSE 0 END) AS total_inst,
            COUNT(CASE WHEN Numero_demande IS NULL THEN 1 END) AS nbre_inst,
            SUM(CASE WHEN Numero_demande IS NOT NULL THEN Montant_Reglement ELSE 0 END) AS total_demande,
            SUM(Montant_Ligne) AS total_all,
            SUM(CASE WHEN Numero_Cheque IS NOT NULL THEN Montant_Reglement ELSE 0 END) AS total_regle
        FROM Ligne_Suivi
        WHERE rejete = 0 AND ISNULL(annuler, 0) = 0 AND Annee_Facture = ? AND statut_ligne NOT IN (8, 4)
    ";
    $result = DB::connection('sqlsrv')->select($query, [$annee])[0] ?? (object)[];

    $totalInst = $result->total_inst ?? 0;
    $nbreInst = $result->nbre_inst ?? 0;
    $totalDemande = $result->total_demande ?? 0;
    $totalAll = $result->total_all ?? 0;
    $totalRegle = $result->total_regle ?? 0;
    $tauxReglement = $totalAll > 0 ? round(($totalDemande / $totalAll) * 100, 2) : 0;
    $tauxRegle = $totalDemande > 0 ? round(($totalRegle / $totalDemande) * 100, 2) : 0;

    $stats = [
        'nonTraites' => ['montant' => $totalInst, 'nombre' => $nbreInst],
        'demandes' => ['montant' => $totalDemande, 'totalFacture' => $totalAll, 'taux' => $tauxReglement],
        'regles' => ['montant' => $totalRegle, 'montantDemande' => $totalDemande, 'taux' => $tauxRegle],
    ];

    $categories = [
        'Pharmacie' => "ls.Code_Partenaire IS NOT NULL AND p.type = 'prestataire' AND CAST(p.code_type_prestataire AS VARCHAR) = '0' AND ls.is_evac = 0",
        'Parapharmacie' => "ls.Code_Partenaire IS NOT NULL AND p.type = 'prestataire' AND CAST(p.code_type_prestataire AS VARCHAR) != '0' AND p.code_type_prestataire IS NOT NULL AND ISNULL(p.coutierG, 0) = 0 AND ls.is_evac = 0",
        'Individuels' => "ls.Code_Partenaire IS NOT NULL AND p.type = 'souscripteur' AND ls.is_evac = 0",
        'Evacuation' => "ls.Code_Partenaire IS NOT NULL AND p.type = 'souscripteur' AND ls.is_evac = 1",
        'Appels de fonds' => "ls.Code_Partenaire IS NOT NULL AND p.type = 'prestataire' AND p.coutierG = 1 AND ls.is_evac = 0",
    ];

    $pointMensuel = [];
    foreach ($categories as $categorie => $condition) {
        try {
            $query = "
                SELECT Mois_Facture,
                      SUM(Montant_Ligne) AS montant
                FROM Ligne_Suivi ls
                INNER JOIN partenaires p ON ls.Code_Partenaire = p.id
                WHERE rejete = 0 AND ISNULL(annuler, 0) = 0 AND Annee_Facture = ? AND statut_ligne NOT IN (8, 4) AND {$condition}
                GROUP BY Mois_Facture
            ";
            $results = DB::connection('sqlsrv')->select($query, [$annee]);
            $moisData = array_fill(1, 12, 0);
            foreach ($results as $row) {
                $moisData[$row->Mois_Facture] = $row->montant;
            }
            $pointMensuel[$categorie] = $moisData;

            // LOG AJOUTÉ : Vérifier les résultats pour chaque catégorie
            Log::info("Point Mensuel pour {$categorie} (année {$annee}):", [
                'query' => $query,
                'bindings' => [$annee],
                'results_count' => count($results),
                'moisData' => $moisData,
                'sample_row' => $results ? (array) $results[0] : null
            ]);
        } catch (\Exception $e) {
            Log::error("Erreur pour {$categorie}: " . $e->getMessage());
            $pointMensuel[$categorie] = array_fill(1, 12, 0);
        }
    }

    $total = array_fill(1, 12, 0);
    foreach ($pointMensuel as $moisData) {
        foreach ($moisData as $mois => $valeur) {
            $total[$mois] += $valeur;
        }
    }
    $pointMensuel['Total'] = $total;

    $repartitionMensuelle = [];
    foreach ($categories as $categorie => $condition) {
        try {
            $query = "
                SELECT Mois_Facture, COUNT(*) AS nombre
                FROM Ligne_Suivi ls
                INNER JOIN partenaires p ON ls.Code_Partenaire = p.id
                WHERE rejete = 0 AND ISNULL(annuler, 0) = 0 AND Annee_Facture = ? AND {$condition}
                GROUP BY Mois_Facture
            ";
            $results = DB::connection('sqlsrv')->select($query, [$annee]);
            $moisData = array_fill(1, 12, 0);
            foreach ($results as $row) {
                $moisData[$row->Mois_Facture] = $row->nombre;
            }
            $repartitionMensuelle[$categorie] = $moisData;

            // LOG AJOUTÉ : Vérifier les résultats pour chaque catégorie
            Log::info("Répartition Mensuelle pour {$categorie} (année {$annee}):", [
                'query' => $query,
                'bindings' => [$annee],
                'results_count' => count($results),
                'moisData' => $moisData,
                'sample_row' => $results ? (array) $results[0] : null
            ]);
        } catch (\Exception $e) {
            Log::error("Erreur répartition pour {$categorie}: " . $e->getMessage());
            $repartitionMensuelle[$categorie] = array_fill(1, 12, 0);
        }
    }

    $total = array_fill(1, 12, 0);
    foreach ($repartitionMensuelle as $moisData) {
        foreach ($moisData as $mois => $valeur) {
            $total[$mois] += $valeur;
        }
    }
    $repartitionMensuelle['Total'] = $total;

    // LOG AJOUTÉ : Comparaison finale
    Log::info("Résumé Dashboard pour année {$annee}:", [
        'stats' => $stats,
        'pointMensuel_total_non_zero' => array_keys(array_filter($pointMensuel['Total'], fn($v) => $v > 0)),
        'repartitionMensuelle_total_non_zero' => array_keys(array_filter($repartitionMensuelle['Total'], fn($v) => $v > 0)),
        'pointMensuel_Individuels' => $pointMensuel['Individuels'],
        'repartitionMensuelle_Individuels' => $repartitionMensuelle['Individuels']
    ]);
         Log::info("Logs AJAX pour année {$annee}:", [
         'stats' => $stats,
         'pointMensuel_total_non_zero' => array_keys(array_filter($pointMensuel['Total'], fn($v) => $v > 0)),
         'repartitionMensuelle_total_non_zero' => array_keys(array_filter($repartitionMensuelle['Total'], fn($v) => $v > 0)),
         'pointMensuel_Individuels' => $pointMensuel['Individuels'],
         'repartitionMensuelle_Individuels' => $repartitionMensuelle['Individuels']
     ]);


    $data = [
        'stats' => $stats,
        'pointMensuel' => $pointMensuel,
        'repartitionMensuelle' => $repartitionMensuelle,
    ];

    return view('pages.' . $pageName, [
        'top_menu' => $top_menu,
        'side_menu' => $side_menu,
        'simple_menu' => $simple_menu,
        'first_page_name' => $activeMenu['first_page_name'],
        'second_page_name' => $activeMenu['second_page_name'],
        'third_page_name' => $activeMenu['third_page_name'],
        'page_name' => $pageName,
        'theme' => $theme,
        'layout' => $layout,
        'annee' => $annee,
        'annees' => $annees,
        'data' => $data,
        'fakers' => $fakers,
    ]);
}




if ($pageName === 'transmission-facture') {
    $user = auth()->user();
    $codeProfil = $user->profil->code_profil ?? null;
    // Utilisation des codes de profil
    $isIndividuel = in_array($codeProfil, ['RSI']);
    $isTiersPayant = in_array($codeProfil, ['RSTP']);
    $isAdmin = $codeProfil === 'ADMIN';

    // Récupérer uniquement les factures avec statut 0 (validées) ou 5 (transmises)
    $query = LigneSuivi::query()
        ->leftJoin('partenaires', 'Ligne_Suivi.Code_partenaire', '=', 'partenaires.id')
        ->whereNotNull('Ligne_Suivi.Date_Enregistrement')
        ->whereIn('Ligne_Suivi.Statut_Ligne', [0, 5]) // Seulement statut 0 et 5
        ->with(['partenaire']);

    // Filtres selon le profil
    if ($isIndividuel && !$isTiersPayant) {
        // Assuré uniquement
        $query->where(function ($q) {
            $q->whereNotNull('Ligne_Suivi.Nom_Assure')
              ->where(function ($sub) {
                  $sub->whereNull('Ligne_Suivi.Code_partenaire')
                      ->orWhere('partenaires.type', 'souscripteur');
              });
        });
    } elseif ($isTiersPayant && !$isIndividuel) {
        // Prestataire uniquement
        $query->whereNotNull('Ligne_Suivi.Code_partenaire')
              ->where('partenaires.type', 'prestataire');
    } elseif ($isIndividuel && $isTiersPayant) {
        // Les deux
        $query->where(function ($q) {
            $q->where(function ($sub) {
                $sub->whereNotNull('Ligne_Suivi.Nom_Assure')
                    ->where(function ($inner) {
                        $inner->whereNull('Ligne_Suivi.Code_partenaire')
                              ->orWhere('partenaires.type', 'souscripteur');
                    });
            })->orWhere(function ($sub) {
                $sub->whereNotNull('Ligne_Suivi.Code_partenaire')
                    ->where('partenaires.type', 'prestataire');
            });
        });
    }

    $factures = $query->orderBy('Ligne_Suivi.Date_Enregistrement', 'desc')->get();

    return view('pages.' . $pageName, [
        'top_menu' => $top_menu,
        'side_menu' => $side_menu,
        'simple_menu' => $simple_menu,
        'first_page_name' => $activeMenu['first_page_name'],
        'second_page_name' => $activeMenu['second_page_name'],
        'third_page_name' => $activeMenu['third_page_name'],
        'page_name' => $pageName,
        'theme' => $theme,
        'layout' => $layout,
        'factures' => $factures,
        'isAdmin' => $isAdmin,
        'isIndividuel' => $isIndividuel,
        'fakers' => $fakers,
    ]);
}




    if ($pageName === 'courrier-instance') {
    $courriers = \App\Models\Courier::orderBy('DateRecep', 'desc')->paginate(10);

       $fakers = [];
       $annees = [];
       return view('pages.' . $pageName, [
           'top_menu' => $top_menu,
           'side_menu' => $side_menu,
           'simple_menu' => $simple_menu,
           'first_page_name' => $activeMenu['first_page_name'],
           'second_page_name' => $activeMenu['second_page_name'],
           'third_page_name' => $activeMenu['third_page_name'],
           'page_name' => $pageName,
           'theme' => $theme,
           'layout' => $layout,
           'courriers' => $courriers,  // <-- Variable ajoutée
           'fakers' => $fakers,
       ]);
   }






// 🔥 CONDITION NOUVELLE : Liste des Courriers Santé en Instance (non-traités ou partiels)
if ($pageName === 'detail-reseau') {
    \Log::info('=== FONCTION APPELÉE (detail-reseau) ===', [
        'url' => request()->fullUrl(),
        'params' => request()->all(),
        'pageName' => $pageName
    ]);

    try {
        $request = request();
        $annee = $request->get('annee', Carbon::now()->year);
        $annee = (int) $annee;
        $reseauSelect = $request->get('reseau', 'pharmacies');
        $reseau = $reseauSelect;

        \Log::info('DEBUG detailReseau - Params reçus', ['reseau' => $reseau, 'annee' => $annee]);

        $reseauOriginal = $reseau;
        $reseau = strtolower($reseau);
        if ($reseau === 'evacuation') $reseau = 'evacuations';
        if ($reseau === 'individuel') $reseau = 'individuels';
        if ($reseauOriginal !== $reseau) {
            \Log::warning('DEBUG - Réseau normalisé', ['original' => $reseauOriginal, 'normalized' => $reseau]);
        }

        $reseauxConfig = [
            'pharmacies' => [
                'categorie' => 2,
                'type' => 'prestataire',
                'exclusions' => [],
                'inclusionsOnly' => [],
                'conditions' => ['ls.Code_Partenaire' => 'IS NOT NULL', 'p.type' => '= prestataire'],
                'useRejete' => true,
                'titre' => 'Pharmacies',
                'icone' => 'fa-pills',
            ],
            'courtiers' => [
                'categorie' => 1,
                'type' => 'prestataire',
                'exclusions' => [],
                'inclusionsOnly' => ['SAVOYE', 'ASCOMA'],
                'conditions' => ['ls.Code_Partenaire' => 'IS NOT NULL', 'p.type' => '= prestataire'],
                'useRejete' => true,
                'titre' => 'Courtiers',
                'icone' => 'fa-handshake',
            ],
            'parapharmacie' => [
                'categorie' => 1,
                'type' => 'prestataire',
                'exclusions' => ['SAVOYE', 'ASCOMA'],
                'inclusionsOnly' => [],
                'conditions' => ['ls.Code_Partenaire' => 'IS NOT NULL', 'p.type' => '= prestataire'],
                'useRejete' => true,
                'titre' => 'Parapharmacies',
                'icone' => 'fa-shopping-bag',
            ],
            'evacuations' => [
                'categorie' => null,
                'type' => 'souscripteur',
                'exclusions' => [],
                'inclusionsOnly' => [],
                'conditions' => ['ls.Code_Partenaire' => 'IS NOT NULL', 'ls.is_evac' => '= 1'],
                'useRejete' => false,
                'titre' => 'Évacuations Sanitaires',
                'icone' => 'fa-ambulance',
            ],
            'individuels' => [
                'categorie' => null,
                'type' => 'souscripteur',
                'exclusions' => [],
                'inclusionsOnly' => [],
                'conditions' => ['ls.Code_Partenaire' => 'IS NOT NULL', 'ls.is_evac' => '= 0', 'ls.rejete' => '= 0'],
                'useRejete' => true,
                'titre' => 'Dossiers Individuels',
                'icone' => 'fa-user',
            ],
        ];

        if (!array_key_exists($reseau, $reseauxConfig)) {
            $reseau = 'pharmacies';
            \Log::warning('DEBUG - Fallback réseau', ['original' => $reseauOriginal, 'fallback' => $reseau]);
        }

        $config = $reseauxConfig[$reseau];
        $categorie = $config['categorie'];
        $type = $config['type'];
        $exclusions = $config['exclusions'];
        $inclusionsOnly = $config['inclusionsOnly'];
        $conditions = $config['conditions'];
        $useRejete = $config['useRejete'];
        $titreReseau = $config['titre'];
        $iconeReseau = $config['icone'];

        \Log::info('DEBUG detailReseau - Config appliquée', [
            'reseau' => $reseau, 'conditions' => $conditions, 'useRejete' => $useRejete, 'categorie' => $categorie
        ]);

        $moisAnnee = [1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'];

        $annees = DB::table('Ligne_Suivi')->whereNotNull('Annee_Facture')->distinct()->orderByDesc('Annee_Facture')->take(6)->pluck('Annee_Facture')->toArray();

        $commonWhere = function ($query) use ($annee, $categorie, $type, $exclusions, $inclusionsOnly, $conditions, $reseau) {
            $query->where('ls.Annee_Facture', $annee);

            if (!in_array($reseau, ['evacuations', 'individuels'])) {
                $query->whereNotNull('ls.Code_Partenaire');
            }

            foreach ($conditions as $colonne => $operateur) {
                if ($operateur === 'IS NULL') $query->whereNull($colonne);
                elseif ($operateur === 'IS NOT NULL') $query->whereNotNull($colonne);
                elseif (strpos($operateur, '=') === 0) $query->where($colonne, trim(str_replace('=', '', $operateur)));
            }

            if ($categorie !== null || $type !== null || !empty($exclusions) || !empty($inclusionsOnly)) {
                if ($type === 'prestataire') $query->whereNotNull('p.id');
                if (!empty($inclusionsOnly)) {
                    $query->where(function($q) use ($inclusionsOnly) {
                        foreach ($inclusionsOnly as $nom) $q->orWhere('p.nom', 'LIKE', '%' . $nom . '%');
                    });
                }
                if (!empty($exclusions)) {
                    $query->whereNotIn('p.nom', $exclusions);
                }
                if ($type !== null) $query->where('p.type', $type);
                if ($categorie !== null) $query->where('tp.Id_Categorie', $categorie);
            }
        };

        $baseQuery = function () use ($categorie, $type, $conditions) {
            $query = DB::connection('sqlsrv')->table('Ligne_Suivi as ls');
            $needsJoin = ($categorie !== null || $type !== null) || (!isset($conditions['ls.Code_Partenaire']) || $conditions['ls.Code_Partenaire'] !== 'IS NULL');
            if ($needsJoin) {
                $query->leftJoin('partenaires as p', 'ls.Code_Partenaire', '=', 'p.id')
                      ->leftJoin('type_prestataires as tp', 'p.code_type_prestataire', '=', 'tp.code_type_prestataire');
            }
            return $query;
        };

        $applyCommonWhere = function ($query) use ($commonWhere) {
            if (is_callable($commonWhere)) {
                $commonWhere($query);
            }
            return $query;
        };

        \Log::info('DEBUG - applyCommonWhere est callable', ['is_callable' => is_callable($applyCommonWhere)]);

        $debugQueryBase = $baseQuery();
        $debugQueryBase->selectRaw('COUNT(*) as total_count');
        $debugQuery = $applyCommonWhere($debugQueryBase);
        $debugCount = $debugQuery->first();
        \Log::info('DEBUG detailReseau - Count total après filtres', [
            'reseau' => $reseau, 'annee' => $annee, 'count' => $debugCount->total_count ?? 0,
            'sql' => $debugQuery->toSql(), 'bindings' => $debugQuery->getBindings()
        ]);

        $nonTraitesBase = $baseQuery();
        $nonTraitesBase->selectRaw('COUNT(ls.Id_Ligne) as nbre_inst, ISNULL(SUM(ls.Montant_Ligne), 0) as total_inst')
                      ->whereNull('ls.Numero_demande');
        $nonTraitesQuery = $applyCommonWhere($nonTraitesBase);
        $nonTraites = $nonTraitesQuery->first() ?? (object)['nbre_inst' => 0, 'total_inst' => 0];
        \Log::info('DEBUG - Non-traités', ['nbre' => $nonTraites->nbre_inst, 'total' => $nonTraites->total_inst]);

        $demandeBase = $baseQuery();
        $demandeBase->selectRaw('
            COUNT(ls.Id_Ligne) as nbre_traite,
            ISNULL(SUM(ls.Montant_Reglement), 0) as total_demande,
            ISNULL(SUM(ls.Montant_Ligne), 0) as total_all,
            CASE WHEN ISNULL(SUM(ls.Montant_Ligne), 0) = 0 THEN 0 ELSE ROUND((ISNULL(SUM(ls.Montant_Reglement), 0) / NULLIF(ISNULL(SUM(ls.Montant_Ligne), 0), 0)) * 100, 2) END as taux_reglement
        ')
                      ->whereNotNull('ls.Numero_demande');
        $demandeQuery = $applyCommonWhere($demandeBase);
        $demande = $demandeQuery->first() ?? (object)['nbre_traite' => 0, 'total_demande' => 0, 'total_all' => 0, 'taux_reglement' => 0];
        \Log::info('DEBUG - Demande', ['nbre' => $demande->nbre_traite, 'total_all' => $demande->total_all, 'taux' => $demande->taux_reglement]);

        $regleBase = $baseQuery();
        $regleBase->selectRaw('COUNT(ls.Id_Ligne) as nbre_regle, ISNULL(SUM(ls.Montant_Reglement), 0) as total_regle')
                  ->whereNotNull('ls.Numero_Cheque');
        $regleQuery = $applyCommonWhere($regleBase);
        $regle = $regleQuery->first() ?? (object)['nbre_regle' => 0, 'total_regle' => 0];
        \Log::info('DEBUG - Réglé', ['nbre' => $regle->nbre_regle, 'total' => $regle->total_regle]);

        $tauxRegle = ($demande->total_all ?? 0) > 0 ? round(($regle->total_regle / $demande->total_all) * 100, 2) : 0;

        $instanceBase = $baseQuery()->whereNull('ls.Numero_Demande');
        $instanceQuery = $applyCommonWhere($instanceBase);
        $instance = $instanceQuery->count('ls.Id_Ligne');
        \Log::info('DEBUG - Instance', ['count' => $instance]);

        $traitees = $demande->nbre_traite ?? 0;

        $tresorBase = $baseQuery()->whereNotNull('ls.Numero_Demande')->whereNotNull('ls.Date_Transmission');
        $tresorQuery = $applyCommonWhere($tresorBase);
        $tresor = $tresorQuery->count('ls.Id_Ligne');
        \Log::info('DEBUG - Trésorerie', ['count' => $tresor]);

        $reglees = $regle->nbre_regle ?? 0;

        $soldeesBase = $baseQuery()->whereNotNull('ls.Date_Cloture');
        $soldeesQuery = $applyCommonWhere($soldeesBase);
        $soldees = $soldeesQuery->count('ls.Id_Ligne');
        \Log::info('DEBUG - Soldées', ['count' => $soldees]);

        $rejetsBase = $baseQuery();
        $rejetsBase->selectRaw('COUNT(ls.Id_Ligne) as nbre_lg, ISNULL(SUM(ls.Montant_Ligne), 0) as total_rejet')
                  ->where('ls.rejete', 1);
        $rejetsQuery = $applyCommonWhere($rejetsBase);
        $rejets = $rejetsQuery->first() ?? (object)['nbre_lg' => 0, 'total_rejet' => 0];
        \Log::info('DEBUG - Rejets', ['nbre' => $rejets->nbre_lg, 'total' => $rejets->total_rejet]);

        $totalFacture = $demande->total_all ?? 0;
        $totalRegle = $regle->total_regle ?? 0;
        $totalGlobal = $totalFacture + ($nonTraites->total_inst ?? 0) + $totalRegle;

        $mensuelBase = $baseQuery()
            ->selectRaw('
                ls.Mois_Facture,
                ISNULL(SUM(' . ($useRejete ? 'CASE WHEN ls.rejete = 0 THEN ls.Montant_Ligne ELSE 0 END' : 'ls.Montant_Ligne') . '), 0) as total_facture,
                ISNULL(SUM(' . ($useRejete ? 'CASE WHEN ls.rejete = 0 THEN ls.Montant_Reglement ELSE 0 END' : 'ls.Montant_Reglement') . '), 0) as total_regle,
                ISNULL(SUM(CASE WHEN ls.Numero_demande IS NOT NULL ' . ($useRejete ? 'AND ls.rejete = 0 ' : '') . 'THEN (ls.Montant_Ligne - ls.Montant_Reglement) ELSE 0 END), 0) as total_ecart
            ')
            ->groupBy('ls.Mois_Facture');
        $mensuelQuery = $applyCommonWhere($mensuelBase);
        $mensuelResults = $mensuelQuery->get();
        \Log::info('DEBUG detailReseau - Mensuel Query', [
            'reseau' => $reseau,
    'sql' => $mensuelQuery->toSql(),
    'bindings' => $mensuelQuery->getBindings(),
    'results_count' => $mensuelResults->count(),
    'sample_row' => $mensuelResults->first() ? (array) $mensuelResults->first() : null
        ]);

        $tabMoisFacture = array_fill(1, 12, 0);
        $tabMoisRegle = array_fill(1, 12, 0);
        $tabMoisEcart = array_fill(1, 12, 0);
        $tabSoldes = array_fill(1, 12, 0);

        foreach ($mensuelResults as $row) {
            $i = (int) $row->Mois_Facture;
            if ($i >= 1 && $i <= 12) {
                $tabMoisFacture[$i] = (float) $row->total_facture;
                $tabMoisRegle[$i] = (float) $row->total_regle;
                $tabMoisEcart[$i] = (float) $row->total_ecart;
                $tabSoldes[$i] = $tabMoisFacture[$i] - $tabMoisRegle[$i];
            }
        }

              \Log::info('DEBUG detailReseau - Résumé final', [
            'reseau' => $reseau,
            'annee' => $annee,
            'totalGlobal' => $totalGlobal,
            'nonTraites_nbre' => $nonTraites->nbre_inst ?? 0,
            'nonTraites_total' => $nonTraites->total_inst ?? 0,
            'demande_nbre' => $demande->nbre_traite ?? 0,
            'demande_total_all' => $demande->total_all ?? 0,
            'demande_taux' => $demande->taux_reglement ?? 0,
            'regle_nbre' => $regle->nbre_regle ?? 0,
            'regle_total' => $regle->total_regle ?? 0,
            'tauxRegle' => $tauxRegle,
            'instance' => $instance,
            'traitees' => $traitees,
            'tresor' => $tresor,
            'reglees' => $reglees,
            'soldees' => $soldees,
            'rejets_nbre' => $rejets->nbre_lg ?? 0,
            'rejets_total' => $rejets->total_rejet ?? 0,
            'mensuel_mois_non_zero' => array_keys(array_filter($tabMoisFacture, function($v) { return $v > 0; })),  // Remplacement de fn() par function()
            'useRejete' => $useRejete
        ]);

        // Retour vue (toutes variables : layout + données réseau)
        return view('pages.detail-reseau', [
            'top_menu' => $top_menu,
            'side_menu' => $side_menu,
            'simple_menu' => $simple_menu,
            'first_page_name' => $activeMenu['first_page_name'],
            'second_page_name' => $activeMenu['second_page_name'],
            'third_page_name' => $activeMenu['third_page_name'],
            'page_name' => $pageName,
            'theme' => $theme,
            'layout' => $layout,
            // Données Réseau (LigneSuivi)
            'annee' => $annee,
            'annees' => $annees,
            'reseau' => $reseau,
            'reseauSelect' => $reseauSelect,
            'titreReseau' => $titreReseau,
            'iconeReseau' => $iconeReseau,
            // Ligne 1
            'nonTraites' => $nonTraites,
            'demande' => $demande,
            'regle' => $regle,
            'tauxRegle' => $tauxRegle,
            // Ligne 2
            'instance' => $instance,
            'traitees' => $traitees,
            'tresor' => $tresor,
            'reglees' => $reglees,
            'soldees' => $soldees,
            'rejets' => $rejets,
            // Ligne 3
            'totalFacture' => $totalFacture,
            'totalRegle' => $totalRegle,
            'totalGlobal' => $totalGlobal,
            'moisAnnee' => $moisAnnee,
            'tabMoisFacture' => $tabMoisFacture,
            'tabMoisRegle' => $tabMoisRegle,
            'tabMoisEcart' => $tabMoisEcart,
            'tabSoldes' => $tabSoldes,
            // Autres
            'fakers' => $fakers,
        ]);

    } catch (\Exception $e) {
        // Log erreur détaillée
        \Log::error('ERREUR detailReseau dans PageController - Exception capturée', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'reseau' => $reseau ?? 'N/A',
            'annee' => $annee ?? 'N/A',
            'pageName' => $pageName
        ]);

        // Fallback : Vue vide sans crash (avec alert erreur)
        $moisAnnee = [1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'];
        $side_menu = $this->sideMenu() ?? [];
        $activeMenu = $this->activeMenu($layout, $pageName) ?? [
            'first_page_name' => $pageName,
            'second_page_name' => '',
            'third_page_name' => ''
        ];
        $top_menu = $this->topMenu() ?? [];
        $simple_menu = $this->simpleMenu() ?? [];
        $fakers = [];
        $annees = [];

        return view('pages.detail-reseau', [
            'top_menu' => $top_menu,
            'side_menu' => $side_menu,
            'simple_menu' => $simple_menu,
            'first_page_name' => $activeMenu['first_page_name'],
            'second_page_name' => $activeMenu['second_page_name'],
            'third_page_name' => $activeMenu['third_page_name'],
            'page_name' => $pageName,
            'theme' => $theme,
            'layout' => $layout,
            // Données par défaut (0) + erreur
            'annee' => $annee ?? Carbon::now()->year,
            'annees' => $annees,
            'reseau' => 'pharmacies',
            'titreReseau' => 'Erreur Chargement',
            'iconeReseau' => 'fa-exclamation-triangle',
            'nonTraites' => (object)['nbre_inst' => 0, 'total_inst' => 0],
            'demande' => (object)['nbre_traite' => 0, 'total_demande' => 0, 'total_all' => 0, 'taux_reglement' => 0],
            'regle' => (object)['nbre_regle' => 0, 'total_regle' => 0],
            'tauxRegle' => 0,
            'instance' => 0,
            'traitees' => 0,
            'tresor' => 0,
            'reglees' => 0,
            'soldees' => 0,
            'rejets' => (object)['nbre_lg' => 0, 'total_rejet' => 0],
            'totalFacture' => 0,
            'totalRegle' => 0,
            'totalGlobal' => 0,
            'moisAnnee' => $moisAnnee,
            'tabMoisFacture' => array_fill(1, 12, 0),
            'tabMoisRegle' => array_fill(1, 12, 0),
            'tabMoisEcart' => array_fill(1, 12, 0),
            'tabSoldes' => array_fill(1, 12, 0),
            'fakers' => $fakers,
        ])->with('error', 'Erreur lors du chargement des détails réseau : ' . $e->getMessage());
    }
}



    return view('pages.' . $pageName, [
       'top_menu' => $top_menu,
            'side_menu' => $side_menu,
            'simple_menu' => $simple_menu,
            'first_page_name' => $activeMenu['first_page_name'],
            'second_page_name' => $activeMenu['second_page_name'],
            'third_page_name' => $activeMenu['third_page_name'],
            'page_name' => $pageName,
            'theme' => $theme,
            'layout' => $layout,
             'fakers' => $fakers,
    ]);

}




   public function getPrestataires(Request $request)
{
    $prestataires = DB::connection('sqlsrv')
        ->table('partenaires')
        ->where('type', 'prestataire')
        ->select('id as code', 'nom as libelle')
        ->orderBy('nom')
        ->get();
    return response()->json($prestataires);
}

    /**
     * Charger les données du tableau (adaptation de votre logique PHP originale)
     */
 /**
 * Charger les données du tableau (adaptation de votre logique PHP originale)
 */
public function getSituationData(Request $request)
{
    $reseau = $request->get('reseau', 'tt');
    $statutr = $request->get('statutr', 'tt');
    $prestataire = $request->get('prestataire'); // Optionnel, pour filtrer par prestataire spécifique

    // Construire la requête SQL de base (simplifiée et adaptée à Laravel)
    $query = "
        SELECT
            ls.Code_Partenaire AS Code_Prestataire,  -- <-- Correction : utiliser Code_Partenaire et l'aliaser si nécessaire
            p.nom AS Libelle_Prestataire,
            SUM(CASE WHEN ls.Mois_Facture = 1 THEN ls.Montant_Ligne ELSE 0 END) AS JANVIER,
            SUM(CASE WHEN ls.Mois_Facture = 2 THEN ls.Montant_Ligne ELSE 0 END) AS FEVRIER,
            SUM(CASE WHEN ls.Mois_Facture = 3 THEN ls.Montant_Ligne ELSE 0 END) AS MARS,
            SUM(CASE WHEN ls.Mois_Facture = 4 THEN ls.Montant_Ligne ELSE 0 END) AS AVRIL,
            SUM(CASE WHEN ls.Mois_Facture = 5 THEN ls.Montant_Ligne ELSE 0 END) AS MAI,
            SUM(CASE WHEN ls.Mois_Facture = 6 THEN ls.Montant_Ligne ELSE 0 END) AS JUIN,
            SUM(CASE WHEN ls.Mois_Facture = 7 THEN ls.Montant_Ligne ELSE 0 END) AS JUILLET,
            SUM(CASE WHEN ls.Mois_Facture = 8 THEN ls.Montant_Ligne ELSE 0 END) AS AOUT,
            SUM(CASE WHEN ls.Mois_Facture = 9 THEN ls.Montant_Ligne ELSE 0 END) AS SEPTEMBRE,
            SUM(CASE WHEN ls.Mois_Facture = 10 THEN ls.Montant_Ligne ELSE 0 END) AS OCTOBRE,
            SUM(CASE WHEN ls.Mois_Facture = 11 THEN ls.Montant_Ligne ELSE 0 END) AS NOVEMBRE,
            SUM(CASE WHEN ls.Mois_Facture = 12 THEN ls.Montant_Ligne ELSE 0 END) AS DECEMBRE,
            COUNT(CASE WHEN ls.Mois_Facture = 1 THEN 1 END) AS JANVIER_NB_FACTURES,
            COUNT(CASE WHEN ls.Mois_Facture = 2 THEN 1 END) AS FEVRIER_NB_FACTURES,
            COUNT(CASE WHEN ls.Mois_Facture = 3 THEN 1 END) AS MARS_NB_FACTURES,
            COUNT(CASE WHEN ls.Mois_Facture = 4 THEN 1 END) AS AVRIL_NB_FACTURES,
            COUNT(CASE WHEN ls.Mois_Facture = 5 THEN 1 END) AS MAI_NB_FACTURES,
            COUNT(CASE WHEN ls.Mois_Facture = 6 THEN 1 END) AS JUIN_NB_FACTURES,
            COUNT(CASE WHEN ls.Mois_Facture = 7 THEN 1 END) AS JUILLET_NB_FACTURES,
            COUNT(CASE WHEN ls.Mois_Facture = 8 THEN 1 END) AS AOUT_NB_FACTURES,
            COUNT(CASE WHEN ls.Mois_Facture = 9 THEN 1 END) AS SEPTEMBRE_NB_FACTURES,
            COUNT(CASE WHEN ls.Mois_Facture = 10 THEN 1 END) AS OCTOBRE_NB_FACTURES,
            COUNT(CASE WHEN ls.Mois_Facture = 11 THEN 1 END) AS NOVEMBRE_NB_FACTURES,
            COUNT(CASE WHEN ls.Mois_Facture = 12 THEN 1 END) AS DECEMBRE_NB_FACTURES,
            SUM(ls.Montant_Ligne) AS TOTAL_MONTANT,
            ls.Annee_Facture
        FROM Ligne_Suivi ls
        INNER JOIN partenaires p ON ls.Code_Partenaire = p.id
        WHERE ls.Code_Partenaire IS NOT NULL  -- <-- Correction : utiliser Code_Partenaire
        AND ls.statut_ligne NOT IN (8)
        AND ls.rejete = 0
        AND ISNULL(ls.annuler, 0) = 0
    ";

    // Ajouter les conditions de filtrage
    $bindings = [];
    if ($reseau !== 'tt') {
        switch ($reseau) {
            case 'phar':
                $query .= " AND p.type = 'prestataire' AND p.code_type_prestataire = '0' AND ls.is_evac = 0";
                break;
            case 'para':
                $query .= " AND p.type = 'prestataire' AND p.code_type_prestataire != '0' AND ISNULL(p.coutierG, 0) = 0 AND ls.is_evac = 0";
                break;
            case 'ind':
                $query .= " AND p.type = 'souscripteur' AND ls.is_evac = 0";
                break;
            case 'evac':
                $query .= " AND p.type = 'souscripteur' AND ls.is_evac = 1";
                break;
            case 'apfd':
                $query .= " AND p.type = 'prestataire' AND p.coutierG = 1 AND ls.is_evac = 0";
                break;
        }
    }

    if ($statutr !== 'tt') {
        $query .= " AND ls.Annee_Facture = ?";
        $bindings[] = $statutr;
    }

    if ($prestataire) {
        $query .= " AND ls.Code_Partenaire = ?";  // <-- Correction : utiliser Code_Partenaire
        $bindings[] = $prestataire;
    }

    $query .= " GROUP BY ls.Code_Partenaire, p.nom, ls.Annee_Facture ORDER BY p.nom";  // <-- Correction : utiliser Code_Partenaire

    $results = DB::connection('sqlsrv')->select($query, $bindings);

    // Générer le HTML du tableau (comme dans votre code original)
    $html = '<div class="p-6">';
    $html .= '<h3 class="text-xl font-semibold mb-4">Liste des factures reçues</h3>';
    if (empty($results)) {
        $html .= '<p class="text-gray-500">Aucune donnée disponible.</p>';
    } else {
        $html .= '<div class="overflow-x-auto">';
        $html .= '<table id="dataTable" class="min-w-full divide-y divide-gray-200">';
        $html .= '<thead class="bg-gray-50">';
        $html .= '<tr>';
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prestataire</th>';
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Janvier</th>';
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Février</th>';
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mars</th>';
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avril</th>';
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mai</th>';
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Juin</th>';
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Juillet</th>';
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Août</th>';
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Septembre</th>';
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Octobre</th>';
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Novembre</th>';
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Décembre</th>';
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Montant</th>';
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Année</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody class="bg-white divide-y divide-gray-200">';

        foreach ($results as $row) {
            $html .= '<tr>';
            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">' . htmlspecialchars($row->Libelle_Prestataire) . '</td>';
            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . number_format($row->JANVIER, 0, ',', ' ') . '</td>';
            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . number_format($row->FEVRIER, 0, ',', ' ') . '</td>';
            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . number_format($row->MARS, 0, ',', ' ') . '</td>';
            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . number_format($row->AVRIL, 0, ',', ' ') . '</td>';
            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . number_format($row->MAI, 0, ',', ' ') . '</td>';
            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . number_format($row->JUIN, 0, ',', ' ') . '</td>';
            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . number_format($row->JUILLET, 0, ',', ' ') . '</td>';
            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . number_format($row->AOUT, 0, ',', ' ') . '</td>';
            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . number_format($row->SEPTEMBRE, 0, ',', ' ') . '</td>';
            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . number_format($row->OCTOBRE, 0, ',', ' ') . '</td>';
            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . number_format($row->NOVEMBRE, 0, ',', ' ') . '</td>';
            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . number_format($row->DECEMBRE, 0, ',', ' ') . '</td>';
            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">' . number_format($row->TOTAL_MONTANT, 0, ',', ' ') . '</td>';
            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . $row->Annee_Facture . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
    }
    $html .= '</div>';

    return $html;
}




    /**
     * Determine active menu & submenu.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
public function activeMenu($layout, $pageName)
{
    return [
        'first_page_name' => $pageName,
        'second_page_name' => '',
        'third_page_name' => ''
    ];
}

    /**
     * List of side menu items.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response

     * List of side menu items (basé sur vos menus métier).
     */
    public function sideMenu()
    {
        return [
            'tableau-de-bord' => [
                'icon' => 'home',
                'layout' => 'side-menu',
                'page_name' => 'dashboard',
                'title' => 'Tableau de bord'
            ],
            'detail-par-reseau' => [
                'icon' => 'activity',
                'layout' => 'side-menu',
                'page_name' => 'detail-reseau',
                'title' => 'Détail par réseau'
            ],
            'gestion-factures' => [
                'icon' => 'file-text',
                'layout' => 'side-menu',
                'page_name' => 'gestion-factures',
                'title' => 'Gestion des factures',
                'sub_menu' => [
                    'enregistrer-depot-individuel' => [
                        'icon' => 'edit',
                        'layout' => 'side-menu',
                        'page_name' => 'depot-individuel',
                        'title' => 'Enregistrer un dépôt individuel'
                    ],
                    'transmission-facture' => [
                        'icon' => 'send',
                        'layout' => 'side-menu',
                        'page_name' => 'transmission-facture',
                        'title' => 'Transmission de facture'
                    ],
                    'saisie-reglement' => [
                        'icon' => 'dollar-sign',
                        'layout' => 'side-menu',
                        'page_name' => 'saisie-reglement',
                        'title' => 'Saisie règlement'
                    ],
                ]
            ],
            'courrier-instance' => [
                'icon' => 'inbox',
                'layout' => 'side-menu',
                'page_name' => 'courrier-instance',
                'title' => 'Courrier en instance'
            ],
            'gestion-appels-fonds' => [
                'icon' => 'dollar-sign',
                'layout' => 'side-menu',
                'page_name' => 'appels-fonds',
                'title' => 'Gestion des appels de fonds'
            ],
            'devider',
            'listing-reporting' => [
                'icon' => 'bar-chart',
                'page_name' => 'listing-reporting',
                'title' => 'Listing et reporting',
                'sub_menu' => [
                    'listing-facture' => [
                        'icon' => 'list',
                        'layout' => 'side-menu',
                        'page_name' => 'listing-reporting',
                        'title' => 'Listing facture'
                    ],
                    'demande-ixperta' => [
                        'icon' => 'check-circle',
                        'layout' => 'side-menu',
                        'page_name' => 'demande-ixperta',
                        'title' => 'Demande validée dans Ixperta'
                    ],
                    'listing-reglement' => [
                        'icon' => 'clipboard',
                        'layout' => 'side-menu',
                        'page_name' => 'listing-reglements',
                        'title' => 'Listing règlement'
                    ]
                ]
            ],
            'traitement-speciaux' => [
                'icon' => 'tool',
                'layout' => 'side-menu',
                'page_name' => '',
                'title' => 'Traitement spéciaux',
                'sub_menu'=> [
                     'correction-facture' => [
                        'icon' => 'refresh-cw',
                        'layout' => 'side-menu',
                        'page_name' => 'factures-correction',
                        'title' => 'Correction de facture'
                    ],
                    'annulation-facture' => [
                        'icon' => 'x-circle',
                        'layout' => 'side-menu',
                        'page_name' => 'factures-annulation',
                        'title' => 'Annulation de facture'
                    ]
                ]
            ],
            'devider',
            'admin' => [
                'icon' => 'settings',
                'page_name' => 'admin',
                'title' => 'Admin',
                'sub_menu' => [
                    'gestion-utilisateurs' => [
                        'icon' => 'users',
                        'layout' => 'side-menu',
                        'page_name' => 'gestion-utilisateurs',
                        'title' => 'Gestion des utilisateurs'
                    ],
                    'gestion-profils' => [
                        'icon' => 'shield',
                        'layout' => 'side-menu',
                        'page_name' => 'gestion-profils',
                        'title' => 'Gestion des profils'
                    ],
                    'delais-traitement' => [
                        'icon' => 'clock',
                        'layout' => 'side-menu',
                        'page_name' => 'delais-traitement',
                        'title' => 'Délais de traitement'
                    ]
                ]
            ]
        ];
    }

    /**
     * List of simple menu items (basé sur vos menus métier, adapté pour simple-menu).
     */
    public function simpleMenu()
    {
         return [
            'tableau-de-bord' => [
                'icon' => 'home',
                'layout' => 'side-menu',
                'page_name' => 'dashboard',
                'title' => 'Tableau de bord'
            ],
            'detail-par-reseau' => [
                'icon' => 'activity',
                'layout' => 'simple-menu',
                'page_name' => 'detail-reseau',
                'title' => 'Détail par réseau'
            ],
            'gestion-factures' => [
                'icon' => 'file-text',
                'layout' => 'simple-menu',
                'page_name' => 'gestion-factures',
                'title' => 'Gestion des factures',
                'sub_menu' => [
                    'enregistrer-depot-individuel' => [
                        'icon' => 'edit',
                        'layout' => 'simple-menu',
                        'page_name' => 'depot-individuel',
                        'title' => 'Enregistrer un dépôt individuel'
                    ],
                    'transmission-facture' => [
                        'icon' => 'send',
                        'layout' => 'simple-menu',
                        'page_name' => 'transmission-facture',
                        'title' => 'Transmission de facture'
                    ],
                    'saisie-reglement' => [
                        'icon' => 'dollar-sign',
                        'layout' => 'simple-menu',
                        'page_name' => 'saisie-reglement',
                        'title' => 'Saisie règlement'
                    ],
                ]
            ],
            'courrier-instance' => [
                'icon' => 'inbox',
                'layout' => 'simple-menu',
                'page_name' => 'courrier-instance',
                'title' => 'Courrier en instance'
            ],
            'gestion-appels-fonds' => [
                'icon' => 'dollar-sign',
                'layout' => 'simple-menu',
                'page_name' => 'appels-fonds',
                'title' => 'Gestion des appels de fonds'
            ],
            'devider',
            'listing-reporting' => [
                'icon' => 'bar-chart',
                'page_name' => 'listing-reporting',
                'title' => 'Listing et reporting',
                'sub_menu' => [
                    'listing-facture' => [
                        'icon' => 'list',
                        'layout' => 'simple-menu',
                        'page_name' => 'listing-reporting',
                        'title' => 'Listing facture'
                    ],
                    'demande-ixperta' => [
                        'icon' => 'check-circle',
                        'layout' => 'simple-menu',
                        'page_name' => 'demande-ixperta',
                        'title' => 'Demande validée dans Ixperta'
                    ],
                    'listing-reglement' => [
                        'icon' => 'clipboard',
                        'layout' => 'simple-menu',
                        'page_name' => 'listing-reglements',
                        'title' => 'Listing règlement'
                    ]
                ]
            ],
            'traitement-speciaux' => [
                'icon' => 'tool',
                'layout' => 'simple-menu',
                'page_name' => 'traitement-speciaux',
                'title' => 'Traitement spéciaux',
                'sub_menu'=> [
                     'correction-facture' => [
                        'icon' => 'refresh-cw',
                        'layout' => 'simple-menu',
                        'page_name' => 'factures-correction',
                        'title' => 'Correction de facture'
                    ],
                    'annulation-facture' => [
                        'icon' => 'x-circle',
                        'layout' => 'simple-menu',
                        'page_name' => 'factures-annulation',
                        'title' => 'Annulation de facture'
                    ]
                ]
            ],
            'devider',
            'admin' => [
                'icon' => 'settings',
                'page_name' => 'admin',
                'title' => 'Admin',
                'sub_menu' => [
                    'gestion-utilisateurs' => [
                        'icon' => 'users',
                        'layout' => 'simple-menu',
                        'page_name' => 'gestion-utilisateurs',
                        'title' => 'Gestion des utilisateurs'
                    ],
                    'gestion-profils' => [
                        'icon' => 'shield',
                        'layout' => 'simple-menu',
                        'page_name' => 'gestion-profils',
                        'title' => 'Gestion des profils'
                    ],
                    'delais-traitement' => [
                        'icon' => 'clock',
                        'layout' => 'simple-menu',
                        'page_name' => 'delais-traitement',
                        'title' => 'Délais de traitement'
                    ]
                ]
            ]
        ];
    }

    /**
     * List of top menu items (basé sur vos menus métier, adapté pour top-menu).
     */
    public function topMenu()
    {
        return [
            'tableau-de-bord' => [
                'icon' => 'home',
                'layout' => 'top-menu',
                'page_name' => 'dashboard',
                'title' => 'Tableau de bord'
            ],
            'detail-par-reseau' => [
                'icon' => 'activity',
                'layout' => 'top-menu',
                'page_name' => 'detail-reseau',
                'title' => 'Détail par réseau'
            ],
            'gestion-factures' => [
                'icon' => 'file-text',
                'layout' => 'top-menu',
                'page_name' => 'gestion-factures',
                'title' => 'Gestion des factures',
                'sub_menu' => [
                    'enregistrer-depot-individuel' => [
                        'icon' => 'edit',
                        'layout' => 'top-menu',
                        'page_name' => 'depot-individuel',
                        'title' => 'Enregistrer un dépôt individuel'
                    ],
                    'transmission-facture' => [
                        'icon' => 'send',
                        'layout' => 'top-menu',
                        'page_name' => 'transmission-facture',
                        'title' => 'Transmission de facture'
                    ],
                    'saisie-reglement' => [
                        'icon' => 'dollar-sign',
                        'layout' => 'top-menu',
                        'page_name' => 'saisie-reglement',
                        'title' => 'Saisie règlement'
                    ],
                ]
            ],
            'courrier-instance' => [
                'icon' => 'inbox',
                'layout' => 'top-menu',
                'page_name' => 'courrier-instance',
                'title' => 'Courrier en instance'
            ],
            'gestion-appels-fonds' => [
                'icon' => 'dollar-sign',
                'layout' => 'top-menu',
                'page_name' => 'appels-fonds',
                'title' => 'Gestion des appels de fonds'
            ],
            'devider',
            'listing-reporting' => [
                'icon' => 'bar-chart',
                'page_name' => 'listing-reporting',
                'title' => 'Listing et reporting',
                'sub_menu' => [
                    'listing-facture' => [
                        'icon' => 'list',
                        'layout' => 'top-menu',
                        'page_name' => 'listing-reporting',
                        'title' => 'Listing facture'
                    ],
                    'demande-ixperta' => [
                        'icon' => 'check-circle',
                        'layout' => 'top-menu',
                        'page_name' => 'demande-ixperta',
                        'title' => 'Demande validée dans Ixperta'
                    ],
                    'listing-reglement' => [
                        'icon' => 'clipboard',
                        'layout' => 'top-menu',
                        'page_name' => 'listing-reglements',
                        'title' => 'Listing règlement'
                    ]
                ]
            ],
            'traitement-speciaux' => [
                'icon' => 'tool',
                'layout' => 'top-menu',
                'page_name' => 'traitement-speciaux',
                'title' => 'Traitement spéciaux',
                'sub_menu'=> [
                     'correction-facture' => [
                        'icon' => 'refresh-cw',
                        'layout' => 'top-menu',
                        'page_name' => 'factures-correction',
                        'title' => 'Correction de facture'
                    ],
                    'annulation-facture' => [
                        'icon' => 'x-circle',
                        'layout' => 'top-menu',
                        'page_name' => 'factures-annulation',
                        'title' => 'Annulation de facture'
                    ]
                ]
            ],
            'devider',
            'admin' => [
                'icon' => 'settings',
                'page_name' => 'admin',
                'title' => 'Admin',
                'sub_menu' => [
                    'gestion-utilisateurs' => [
                        'icon' => 'users',
                        'layout' => 'top-menu',
                        'page_name' => 'gestion-utilisateurs',
                        'title' => 'Gestion des utilisateurs'
                    ],
                    'gestion-profils' => [
                        'icon' => 'shield',
                        'layout' => 'top-menu',
                        'page_name' => 'gestion-profils',
                        'title' => 'Gestion des profils'
                    ],
                    'delais-traitement' => [
                        'icon' => 'clock',
                        'layout' => 'top-menu',
                        'page_name' => 'delais-traitement',
                        'title' => 'Délais de traitement'
                    ]
                ]
            ]
        ];
    }

















    private function getDashboardData($annee)
{

    $annees = DB::connection('sqlsrv')
        ->table('Ligne_Suivi')
        ->whereNotNull('Annee_Facture')
        ->distinct()
        ->orderByDesc('Annee_Facture')
        ->pluck('Annee_Facture')
        ->take(10);

    try {
        $columns = DB::connection('sqlsrv')->select("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME = 'partenaires'
        ");
        Log::info('Colonnes de la table partenaires:', array_column($columns, 'COLUMN_NAME'));

        $types = DB::connection('sqlsrv')->select("
            SELECT type, COUNT(*) as count
            FROM partenaires
            GROUP BY type
        ");
        Log::info('Types de partenaires:', (array)$types);

        $lignesAvecPartenaire = DB::connection('sqlsrv')->selectOne("
            SELECT
                COUNT(*) as total,
                COUNT(CASE WHEN Code_Partenaire IS NOT NULL THEN 1 END) as avec_partenaire,
                COUNT(CASE WHEN Code_Partenaire IS NULL THEN 1 END) as sans_partenaire
            FROM Ligne_Suivi
        ");
        Log::info('Lignes de suivi:', (array)$lignesAvecPartenaire);
    } catch (\Exception $e) {
        Log::error('Erreur diagnostic: ' . $e->getMessage());
    }

    $query = "
        SELECT
            SUM(CASE WHEN Numero_demande IS NULL THEN Montant_Ligne ELSE 0 END) AS total_inst,
            COUNT(CASE WHEN Numero_demande IS NULL THEN 1 END) AS nbre_inst,
            SUM(CASE WHEN Numero_demande IS NOT NULL THEN Montant_Reglement ELSE 0 END) AS total_demande,
            SUM(Montant_Ligne) AS total_all,
            SUM(CASE WHEN Numero_Cheque IS NOT NULL THEN Montant_Reglement ELSE 0 END) AS total_regle
        FROM Ligne_Suivi
        WHERE rejete = 0 AND ISNULL(annuler, 0) = 0 AND Annee_Facture = ? AND statut_ligne NOT IN (8, 4)
    ";
    $result = DB::connection('sqlsrv')->select($query, [$annee])[0] ?? (object)[];

    $totalInst = $result->total_inst ?? 0;
    $nbreInst = $result->nbre_inst ?? 0;
    $totalDemande = $result->total_demande ?? 0;
    $totalAll = $result->total_all ?? 0;
    $totalRegle = $result->total_regle ?? 0;
    $tauxReglement = $totalAll > 0 ? round(($totalDemande / $totalAll) * 100, 2) : 0;
    $tauxRegle = $totalDemande > 0 ? round(($totalRegle / $totalDemande) * 100, 2) : 0;

    $stats = [
        'nonTraites' => ['montant' => $totalInst, 'nombre' => $nbreInst],
        'demandes' => ['montant' => $totalDemande, 'totalFacture' => $totalAll, 'taux' => $tauxReglement],
        'regles' => ['montant' => $totalRegle, 'montantDemande' => $totalDemande, 'taux' => $tauxRegle],
    ];

    $categories = [
        'Pharmacie' => "ls.Code_Partenaire IS NOT NULL AND p.type = 'prestataire' AND CAST(p.code_type_prestataire AS VARCHAR) = '0' AND ls.is_evac = 0",
        'Parapharmacie' => "ls.Code_Partenaire IS NOT NULL AND p.type = 'prestataire' AND CAST(p.code_type_prestataire AS VARCHAR) != '0' AND p.code_type_prestataire IS NOT NULL AND ISNULL(p.coutierG, 0) = 0 AND ls.is_evac = 0",
        'Individuels' => "ls.Code_Partenaire IS NOT NULL AND p.type = 'souscripteur' AND ls.is_evac = 0",
        'Evacuation' => "ls.Code_Partenaire IS NOT NULL AND p.type = 'souscripteur' AND ls.is_evac = 1",
        'Appels de fonds' => "ls.Code_Partenaire IS NOT NULL AND p.type = 'prestataire' AND p.coutierG = 1 AND ls.is_evac = 0",
    ];

    $pointMensuel = [];
    foreach ($categories as $categorie => $condition) {
        try {
            $query = "
                SELECT Mois_Facture,
                       SUM(Montant_Ligne) AS montant
                FROM Ligne_Suivi ls
                INNER JOIN partenaires p ON ls.Code_Partenaire = p.id
                WHERE rejete = 0 AND ISNULL(annuler, 0) = 0 AND Annee_Facture = ? AND statut_ligne NOT IN (8, 4) AND {$condition}
                GROUP BY Mois_Facture
            ";
            $results = DB::connection('sqlsrv')->select($query, [$annee]);
            $moisData = array_fill(1, 12, 0);
            foreach ($results as $row) {
                $moisData[$row->Mois_Facture] = $row->montant;
            }
            $pointMensuel[$categorie] = $moisData;

            // LOG AJOUTÉ : Vérifier les résultats pour chaque catégorie
            Log::info("Point Mensuel pour {$categorie} (année {$annee}):", [
                'query' => $query,
                'bindings' => [$annee],
                'results_count' => count($results),
                'moisData' => $moisData,
                'sample_row' => $results ? (array) $results[0] : null
            ]);
        } catch (\Exception $e) {
            Log::error("Erreur pour {$categorie}: " . $e->getMessage());
            $pointMensuel[$categorie] = array_fill(1, 12, 0);
        }
    }

    $total = array_fill(1, 12, 0);
    foreach ($pointMensuel as $moisData) {
        foreach ($moisData as $mois => $valeur) {
            $total[$mois] += $valeur;
        }
    }
    $pointMensuel['Total'] = $total;

    $repartitionMensuelle = [];
    foreach ($categories as $categorie => $condition) {
        try {
            $query = "
                SELECT Mois_Facture, COUNT(*) AS nombre
                FROM Ligne_Suivi ls
                INNER JOIN partenaires p ON ls.Code_Partenaire = p.id
                WHERE rejete = 0 AND ISNULL(annuler, 0) = 0 AND Annee_Facture = ? AND {$condition}
                GROUP BY Mois_Facture
            ";
            $results = DB::connection('sqlsrv')->select($query, [$annee]);
            $moisData = array_fill(1, 12, 0);
            foreach ($results as $row) {
                $moisData[$row->Mois_Facture] = $row->nombre;
            }
            $repartitionMensuelle[$categorie] = $moisData;

            // LOG AJOUTÉ : Vérifier les résultats pour chaque catégorie
            Log::info("Répartition Mensuelle pour {$categorie} (année {$annee}):", [
                'query' => $query,
                'bindings' => [$annee],
                'results_count' => count($results),
                'moisData' => $moisData,
                'sample_row' => $results ? (array) $results[0] : null
            ]);
        } catch (\Exception $e) {
            Log::error("Erreur répartition pour {$categorie}: " . $e->getMessage());
            $repartitionMensuelle[$categorie] = array_fill(1, 12, 0);
        }
    }

    $total = array_fill(1, 12, 0);
    foreach ($repartitionMensuelle as $moisData) {
        foreach ($moisData as $mois => $valeur) {
            $total[$mois] += $valeur;
        }
    }
    $repartitionMensuelle['Total'] = $total;

    // LOG AJOUTÉ : Comparaison finale
    Log::info("Résumé Dashboard pour année {$annee}:", [
        'stats' => $stats,
        'pointMensuel_total_non_zero' => array_keys(array_filter($pointMensuel['Total'], fn($v) => $v > 0)),
        'repartitionMensuelle_total_non_zero' => array_keys(array_filter($repartitionMensuelle['Total'], fn($v) => $v > 0)),
        'pointMensuel_Individuels' => $pointMensuel['Individuels'],
        'repartitionMensuelle_Individuels' => $repartitionMensuelle['Individuels']
    ]);
         Log::info("Logs AJAX pour année {$annee}:", [
         'stats' => $stats,
         'pointMensuel_total_non_zero' => array_keys(array_filter($pointMensuel['Total'], fn($v) => $v > 0)),
         'repartitionMensuelle_total_non_zero' => array_keys(array_filter($repartitionMensuelle['Total'], fn($v) => $v > 0)),
         'pointMensuel_Individuels' => $pointMensuel['Individuels'],
         'repartitionMensuelle_Individuels' => $repartitionMensuelle['Individuels']
     ]);


    $data = [
        'stats' => $stats,
        'pointMensuel' => $pointMensuel,
        'repartitionMensuelle' => $repartitionMensuelle,
    ];


    return [
        'stats' => $stats,
        'pointMensuel' => $pointMensuel,
        'repartitionMensuelle' => $repartitionMensuelle,
    ];
}





public function getDashboardDataAjax(Request $request)
{
    $annee = $request->get('annee', date('Y'));
    $data = $this->getDashboardData($annee);
    return response()->json($data);
}
}
