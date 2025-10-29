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

  $side_menu = $this->sideMenu() ?? [];
        $activeMenu = $this->activeMenu($layout, $pageName) ?? [
            'first_page_name' => $pageName,
            'second_page_name' => '',
            'third_page_name' => ''
        ];
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



   if ($pageName === 'depot-individuel') {
    $courriers = \App\Models\CourierSanteIndiv::orderBy('datereception', 'desc')->paginate(10);
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
    $profil_id = $user->profil->id ?? null;

    // Récupérer le paramètre de recherche depuis la requête GET
    $search = request()->get('search', '');

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

    // Début de la requête commune avec jointure pour type
    $query = LigneSuivi::whereNotNull('Date_Enregistrement')
        ->leftJoin('partenaires', 'Ligne_Suivi.Code_partenaire', '=', 'partenaires.id') // Jointure pour accéder au type
        ->with(['partenaire']); // Utilise la nouvelle relation unifiée

    // 🔀 Filtrage selon le profil (adapté pour Code_partenaire et type)
    if ($profil_id == 4) {
        // Régleur Sinistre Individuel : Nom_Assure non null et partenaire de type 'souscripteur' ou null
        $query->whereNotNull('Nom_Assure')
              ->where(function ($q) {
                  $q->whereNull('Ligne_Suivi.Code_partenaire')
                    ->orWhere('partenaires.type', 'souscripteur');
              });
    } elseif ($profil_id == 8) {
        // Régleur Sinistre Tiers Payant : partenaire de type 'prestataire'
        $query->whereNotNull('Ligne_Suivi.Code_partenaire')
              ->where('partenaires.type', 'prestataire');
    } elseif (in_array($profil_id, [3, 5])) {
        // Régleur Sinistre ou Admin → voir les deux types
        $query->where(function ($q) {
            $q->where(function ($sub) {
                $sub->whereNotNull('Nom_Assure')
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

    $query->orderBy('Date_Debut', 'desc');

    // Récupération des factures (sans filtrage sur 'rejete')
    $factures = $query->paginate(10);

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
        'profil_id' => $profil_id, // utile dans la vue si besoin
    ]);
}


   if ($pageName === 'listing-reporting') {
        $factures = collect([]); // Collection vide par défaut
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
// 🔥 CONDITION NOUVELLE : Liste des Courriers Santé en Instance (non-traités ou partiels)
if ($pageName === 'detail-reseau') {
    \Log::info('=== FONCTION APPELÉE (detail-reseau) ===', [
        'url' => request()->fullUrl(),
        'params' => request()->all(),
        'pageName' => $pageName
    ]);

    try {
        $request = request();  // Global request() pour params GET
        $annee = $request->get('annee', Carbon::now()->year);
        $annee = (int) $annee;
        $reseauSelect = $request->get('reseau', 'pharmacies');  // ← AJOUT : Valeur brute pour le select
        $reseau = $reseauSelect;  // Copie pour normalisation

        // LOG 1: Params
        \Log::info('DEBUG detailReseau - Params reçus', ['reseau' => $reseau, 'annee' => $annee]);

        // Normalize réseau
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
                'conditions' => ['ls.Code_partenaire' => 'IS NOT NULL', 'p.type' => '= prestataire'], // Corrigé : utilise Code_partenaire et p.type
                'useRejete' => true,
                'titre' => 'Pharmacies',
                'icone' => 'fa-pills',
            ],
            'courtiers' => [
                'categorie' => 1,
                'type' => 'prestataire',
                'exclusions' => [],
                'inclusionsOnly' => ['SAVOYE', 'ASCOMA'],
                'conditions' => ['ls.Code_partenaire' => 'IS NOT NULL', 'p.type' => '= prestataire'], // Corrigé
                'useRejete' => true,
                'titre' => 'Courtiers (SAVOYE/ASCOMA)',
                'icone' => 'fa-handshake',
            ],
            'parapharmacie' => [
                'categorie' => 1,
                'type' => 'prestataire',
                'exclusions' => ['SAVOYE', 'ASCOMA'],
                'inclusionsOnly' => [],
                'conditions' => ['ls.Code_partenaire' => 'IS NOT NULL', 'p.type' => '= prestataire'], // Corrigé
                'useRejete' => true,
                'titre' => 'Parapharmacies',
                'icone' => 'fa-shopping-bag',
            ],
            'evacuations' => [
                'categorie' => null,
                'type' => null,
                'exclusions' => [],
                'inclusionsOnly' => [],
                'conditions' => ['ls.Code_partenaire' => 'IS NULL', 'ls.is_evac' => '= 1'], // Corrigé : Code_partenaire au lieu de Code_Prestataire
                'useRejete' => false,
                'titre' => 'Évacuations Sanitaires',
                'icone' => 'fa-ambulance',
            ],
            'individuels' => [
                'categorie' => null,
                'type' => null,
                'exclusions' => [],
                'inclusionsOnly' => [],
                'conditions' => ['ls.Code_partenaire' => 'IS NULL', 'ls.is_evac' => '= 0', 'ls.rejete' => '= 0'], // Corrigé
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

        // LOG 2: Config
        \Log::info('DEBUG detailReseau - Config appliquée', [
            'reseau' => $reseau, 'conditions' => $conditions, 'useRejete' => $useRejete, 'categorie' => $categorie
        ]);

        $moisAnnee = [1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'];

        $annees = DB::table('Ligne_Suivi')->whereNotNull('Annee_Facture')->distinct()->orderByDesc('Annee_Facture')->take(6)->pluck('Annee_Facture')->toArray();

        // CommonWhere (avec fix skip whereNull pour évacuations/individuels)
        $commonWhere = function ($query) use ($annee, $categorie, $type, $exclusions, $inclusionsOnly, $conditions, $reseau) {
            $query->where('ls.Annee_Facture', $annee);

            // FIX : Skip whereNull pour évacuations/individuels (autorise Code_partenaire non null pour souscripteurs)
            if (!in_array($reseau, ['evacuations', 'individuels'])) {
                $query->whereNull('ls.Code_partenaire'); // Corrigé : Code_partenaire au lieu de Code_Souscripteur (mais logique ajustée)
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
            $needsJoin = ($categorie !== null || $type !== null) || (!isset($conditions['ls.Code_partenaire']) || $conditions['ls.Code_partenaire'] !== 'IS NULL'); // Corrigé : Code_partenaire
            if ($needsJoin) {
                $query->leftJoin('partenaires as p', 'ls.Code_partenaire', '=', 'p.id') // Corrigé : Code_partenaire
                      ->leftJoin('type_prestataires as tp', 'p.code_type_prestataire', '=', 'tp.code_type_prestataire');
            }
            return $query;
        };

        // FIX : Définir la closure applyCommonWhere correctement (appel comme fonction, pas méthode)
        $applyCommonWhere = function ($query) use ($commonWhere) {
            if (is_callable($commonWhere)) {
                $commonWhere($query);
            }
            return $query;
        };

        // Vérification : Log si la closure est callable
        \Log::info('DEBUG - applyCommonWhere est callable', ['is_callable' => is_callable($applyCommonWhere)]);

        // LOG 3: Count total base (debug clé) - CORRIGÉ : Appel closure (pas méthode)
        $debugQueryBase = $baseQuery();
        $debugQueryBase->selectRaw('COUNT(*) as total_count');
        $debugQuery = $applyCommonWhere($debugQueryBase);  // ← CORRECTION : Appel closure, pas $debugQueryBase->applyCommonWhere()
        $debugCount = $debugQuery->first();
        \Log::info('DEBUG detailReseau - Count total après filtres', [
            'reseau' => $reseau, 'annee' => $annee, 'count' => $debugCount->total_count ?? 0,
            'sql' => $debugQuery->toSql(), 'bindings' => $debugQuery->getBindings()
        ]);

        // LIGNE 1 : Non-traités - CORRIGÉ
        $nonTraitesBase = $baseQuery();
        $nonTraitesBase->selectRaw('COUNT(ls.Id_Ligne) as nbre_inst, ISNULL(SUM(ls.Montant_Ligne), 0) as total_inst')
                       ->whereNull('ls.Numero_demande');
        $nonTraitesQuery = $applyCommonWhere($nonTraitesBase);  // ← CORRECTION : Appel closure
        $nonTraites = $nonTraitesQuery->first() ?? (object)['nbre_inst' => 0, 'total_inst' => 0];
        \Log::info('DEBUG - Non-traités', ['nbre' => $nonTraites->nbre_inst, 'total' => $nonTraites->total_inst]);

        // Demande/Facturé - CORRIGÉ
        $demandeBase = $baseQuery();
        $demandeBase->selectRaw('
            COUNT(ls.Id_Ligne) as nbre_traite,
            ISNULL(SUM(ls.Montant_Reglement), 0) as total_demande,
            ISNULL(SUM(ls.Montant_Ligne), 0) as total_all,
            CASE WHEN ISNULL(SUM(ls.Montant_Ligne), 0) = 0 THEN 0 ELSE ROUND((ISNULL(SUM(ls.Montant_Reglement), 0) / NULLIF(ISNULL(SUM(ls.Montant_Ligne), 0), 0)) * 100, 2) END as taux_reglement
        ')
                  ->whereNotNull('ls.Numero_demande');
        $demandeQuery = $applyCommonWhere($demandeBase);  // ← CORRECTION
        $demande = $demandeQuery->first() ?? (object)['nbre_traite' => 0, 'total_demande' => 0, 'total_all' => 0, 'taux_reglement' => 0];
        \Log::info('DEBUG - Demande', ['nbre' => $demande->nbre_traite, 'total_all' => $demande->total_all, 'taux' => $demande->taux_reglement]);

        // Réglé - CORRIGÉ
        $regleBase = $baseQuery();
        $regleBase->selectRaw('COUNT(ls.Id_Ligne) as nbre_regle, ISNULL(SUM(ls.Montant_Reglement), 0) as total_regle')
                  ->whereNotNull('ls.Numero_Cheque');
        $regleQuery = $applyCommonWhere($regleBase);  // ← CORRECTION
        $regle = $regleQuery->first() ?? (object)['nbre_regle' => 0, 'total_regle' => 0];
        \Log::info('DEBUG - Réglé', ['nbre' => $regle->nbre_regle, 'total' => $regle->total_regle]);

        // FIX : Calcul tauxRegle corrigé (basé sur total_regle vs total_all)
        $tauxRegle = ($demande->total_all ?? 0) > 0 ? round(($regle->total_regle / $demande->total_all) * 100, 2) : 0;

        // LIGNE 2 : Instance - CORRIGÉ
        $instanceBase = $baseQuery()->whereNull('ls.Numero_Demande');
        $instanceQuery = $applyCommonWhere($instanceBase);  // ← CORRECTION
        $instance = $instanceQuery->count('ls.Id_Ligne');
        \Log::info('DEBUG - Instance', ['count' => $instance]);

        $traitees = $demande->nbre_traite ?? 0;

        // Trésorerie - CORRIGÉ
        $tresorBase = $baseQuery()->whereNotNull('ls.Numero_Demande')->whereNotNull('ls.Date_Transmission');
        $tresorQuery = $applyCommonWhere($tresorBase);  // ← CORRECTION
        $tresor = $tresorQuery->count('ls.Id_Ligne');
        \Log::info('DEBUG - Trésorerie', ['count' => $tresor]);

        $reglees = $regle->nbre_regle ?? 0;

        // Soldées - CORRIGÉ
        $soldeesBase = $baseQuery()->whereNotNull('ls.Date_Cloture');
        $soldeesQuery = $applyCommonWhere($soldeesBase);  // ← CORRECTION
        $soldees = $soldeesQuery->count('ls.Id_Ligne');
        \Log::info('DEBUG - Soldées', ['count' => $soldees]);

        // Rejets - CORRIGÉ
        $rejetsBase = $baseQuery();
        $rejetsBase->selectRaw('COUNT(ls.Id_Ligne) as nbre_lg, ISNULL(SUM(ls.Montant_Ligne), 0) as total_rejet')
                   ->where('ls.rejete', 1);
        $rejetsQuery = $applyCommonWhere($rejetsBase);  // ← CORRECTION
        $rejets = $rejetsQuery->first() ?? (object)['nbre_lg' => 0, 'total_rejet' => 0];
        \Log::info('DEBUG - Rejets', ['nbre' => $rejets->nbre_lg, 'total' => $rejets->total_rejet]);

        $totalFacture = $demande->total_all ?? 0;
        $totalRegle = $regle->total_regle ?? 0;
        $totalGlobal = $totalFacture + ($nonTraites->total_inst ?? 0) + $totalRegle;

        // LIGNE 3 : Mensuel optimisé (1 query GROUP BY) - CORRIGÉ
        $mensuelBase = $baseQuery()
            ->selectRaw('
                ls.Mois_Facture,
                ISNULL(SUM(' . ($useRejete ? 'CASE WHEN ls.rejete = 0 THEN ls.Montant_Ligne ELSE 0 END' : 'ls.Montant_Ligne') . '), 0) as total_facture,
                ISNULL(SUM(' . ($useRejete ? 'CASE WHEN ls.rejete = 0 THEN ls.Montant_Reglement ELSE 0 END' : 'ls.Montant_Reglement') . '), 0) as total_regle,
                ISNULL(SUM(CASE WHEN ls.Numero_demande IS NOT NULL ' . ($useRejete ? 'AND ls.rejete = 0 ' : '') . 'THEN (ls.Montant_Ligne - ls.Montant_Reglement) ELSE 0 END), 0) as total_ecart
            ')
            ->groupBy('ls.Mois_Facture');
        $mensuelQuery = $applyCommonWhere($mensuelBase);  // ← CORRECTION : Appel closure
        $mensuelResults = $mensuelQuery->get();
        \Log::info('DEBUG detailReseau - Mensuel Query', [
            'reseau' => $reseau,
            'sql' => $mensuelQuery->toSql(),
            'bindings' => $mensuelQuery->getBindings(),
            'results_count' => $mensuelResults->count(),
            'sample_row' => $mensuelResults->first() ? (array) $mensuelResults->first() : null  // ← CORRECTION : Cast en array au lieu de toArray()
        ]);

        // Remplissage tabs (avec fallback 0)

        $tabMoisFacture = array_fill(1, 12, 0);
        $tabMoisRegle = array_fill(1, 12, 0);
        $tabMoisEcart = array_fill(1, 12, 0);
        $tabSoldes = array_fill(1, 12, 0);

        foreach ($mensuelResults as $row) {
            $i = (int) $row->Mois_Facture;
            if ($i >= 1 && $i <= 12) {
                $tabMoisFacture[$i] = (float) $row->total_facture;  // Cast float pour cohérence
                $tabMoisRegle[$i] = (float) $row->total_regle;
                $tabMoisEcart[$i] = (float) $row->total_ecart;
                $tabSoldes[$i] = $tabMoisFacture[$i] - $tabMoisRegle[$i];
            }
        }

        // LOG SUITE : Tabs mensuels (ex. Octobre et Septembre) - Pour debug valeurs comme 19000 en Mois=9
        \Log::info('DEBUG detailReseau - Tabs mensuels (ex. Octobre et Septembre)', [
            'facture_oct' => $tabMoisFacture[10] ?? 0,
            'regle_oct' => $tabMoisRegle[10] ?? 0,
            'ecart_oct' => $tabMoisEcart[10] ?? 0,
            'solde_oct' => $tabSoldes[10] ?? 0,
            'facture_sep' => $tabMoisFacture[9] ?? 0,  // Ex. : Devrait tracer 19000 si données présentes
            'regle_sep' => $tabMoisRegle[9] ?? 0,
            'solde_sep' => $tabSoldes[9] ?? 0
        ]);

        // LOG FINAL : Résumé complet pour debug
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
            'mensuel_mois_non_zero' => array_keys(array_filter($tabMoisFacture, fn($v) => $v > 0)),  // Ex. : [9] pour Septembre/19000
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

if ($pageName === 'dashboard') {
    // Récupérer l'année depuis la requête (par défaut année en cours)
    $annee = request()->get('annee', date('Y'));

    // Récupérer les années disponibles
    $annees = DB::connection('sqlsrv')
        ->table('Ligne_Suivi')
        ->whereNotNull('Annee_Facture')
        ->distinct()
        ->orderByDesc('Annee_Facture')
        ->pluck('Annee_Facture')
        ->take(10);

    // Charger les données initiales
    $data = $this->getDashboardData($annee);

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
    $profil_id = $user->profil->id ?? null;
    $isAdmin = ($profil_id == 5); // Admin : Affiche TOUTES les validées/transmises
    $isIndividuel = !$isAdmin && in_array($profil_id, [7]); // Individuel pur seulement pour profil 7

    Log::info('Transmission-facture Debug: Profil et Filtres', [
        'profil_id' => $profil_id,
        'isAdmin' => $isAdmin,
        'isIndividuel' => $isIndividuel,
        'user_id' => $user->id
    ]);

    $factures = collect(); // Initialisation

    if ($isAdmin) {
        // ADMIN : TOUTES les factures stLigne IN [3,5] (validées + transmises en attente)
        $query = DB::connection('sqlsrv')->table('Ligne_Suivi as ls')
            ->leftJoin('partenaires as p', 'ls.Code_partenaire', '=', 'p.id') // Corrigé : Code_partenaire
            ->select([
                'ls.Id_Ligne as id',
                DB::raw('COALESCE(p.nom, ls.Nom_Assure, \'N/A\') as prest'), // Corrigé : utilise p.nom directement, fallback Nom_Assure
                'ls.Numero_Reception',
                DB::raw('COALESCE(ls.Reference_Facture, ls.Nom_Assure, \'N/A\') as ref'), // Fallback ref
                DB::raw('CONVERT(varchar, ls.Date_Debut, 103) as deb'),
                DB::raw('CONVERT(varchar, ls.Date_Fin, 103) as fin'),
                DB::raw('CAST(ls.Montant_Ligne AS INT) as mtligne'),
                DB::raw('CONVERT(varchar, ls.Date_Enregistrement, 103) as dtEnreg'),
                DB::raw('CONVERT(varchar, ls.datetransMedecin, 103) as dtTransMed'),
                DB::raw('CONVERT(varchar, ls.dateRetourMedecin, 103) as dtRetourMed'),
                DB::raw('CONVERT(varchar, ls.Date_Transmission, 103) as dtTrans'),
                DB::raw('CONVERT(varchar, ls.Date_Enregistrement, 103) as datetransestim'),
                DB::raw('CONVERT(varchar, DATEADD(day, 1, ls.Date_Enregistrement), 103) AS dateretestim'),
                'ls.Statut_Ligne as stLigne',
                DB::raw('CASE WHEN ls.dateRetourMedecin IS NOT NULL THEN 1 ELSE 0 END as retour_recu'),
                'ls.Code_partenaire', // Corrigé : Code_partenaire
                DB::raw('CASE
                    WHEN p.type = \'prestataire\' THEN \'Tiers-Payant\'
                    WHEN p.type = \'souscripteur\' THEN \'Individuel\'
                    ELSE \'Autre\' END as type_label'), // Corrigé : utilise p.type
                DB::raw('CASE ls.Statut_Ligne
                    WHEN 3 THEN \'Validée - Prête à Transmettre\'
                    WHEN 5 THEN \'Transmise - En Attente Retour\'
                    ELSE \'Autre\' END as statut_label')
            ])
            ->where('ls.rejete', 0)
            ->whereRaw('ISNULL(ls.annuler, 0) = 0')
            ->whereIn('ls.Statut_Ligne', [3, 5]) // ✅ 3 (validées/prêtes) + 5 (transmises/en attente retour)
            ->whereNotNull('ls.Date_Transmission') // ET Date_Transmission set
            // Pas de filtre sur Code_* ou is_evac : Affiche TOUT pour admin
            ->orderBy('ls.Statut_Ligne', 'asc') // 3 en premier
            ->orderBy('ls.dateRetourMedecin', 'asc'); // En attente en premier

        $factures = collect($query->get()); // ✅ Collect pour faciliter pluck/each

        // Log SQL généré pour debug
        Log::info('Transmission-facture: ADMIN SQL Generated', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        // ✅ CORRECTION : Conversion stdClass vers array pour logging (cast manuel)
        $sample = $factures->first();
        $sampleArray = $sample ? (array) $sample : null; // Cast manuel en array

        Log::info('Transmission-facture: ADMIN [3,5] Results', [
            'count' => $factures->count(),
            'sample' => $sampleArray ?? 'Aucun', // ✅ Cast (array) au lieu de toArray() sur stdClass
            'ids_found' => $factures->pluck('id')->toArray(), // ✅ pluck OK sur stdClass
            'statuts_found' => $factures->pluck('stLigne')->unique()->toArray(), // ✅ [3,5]
            'types_found' => $factures->pluck('type_label')->unique()->toArray() // ✅ Idem
        ]);

        // Fallbacks pour admin (mixtes) - Accès propriétés direct (OK sur stdClass)
        $factures->each(function ($facture) {
            $facture->prest = isset($facture->prest) ? $facture->prest : 'N/A';
            $facture->ref = isset($facture->ref) ? $facture->ref : 'N/A';
            $facture->dtTransMed = isset($facture->dtTransMed) ? $facture->dtTransMed : 'Non transmis';
            $facture->dtRetourMed = isset($facture->dtRetourMed) ? $facture->dtRetourMed : 'En attente';
        });

    } elseif ($isIndividuel) {
        // Branche individuels purs (profil 7) : Seulement individuelles stLigne IN [3,5]
        $factures = LigneSuivi::select([
            'Id_Ligne as id',
            'Nom_Assure as prest',
            'Numero_Reception',
            DB::raw('(SELECT nom FROM partenaires WHERE id = ligne_suivi.Code_partenaire AND type = \'souscripteur\') as ref'), // Corrigé : Code_partenaire et type
            DB::raw('CONVERT(varchar, Date_Debut, 103) as deb'),
            DB::raw('CONVERT(varchar, Date_Fin, 103) as fin'),
            DB::raw('CAST(Montant_Ligne AS INT) as mtligne'),
            DB::raw('CONVERT(varchar, Date_Enregistrement, 103) as dtEnreg'),
            DB::raw('CONVERT(varchar, datetransMedecin, 103) as dtTransMed'),
            DB::raw('CONVERT(varchar, dateRetourMedecin, 103) as dtRetourMed'),
            DB::raw('CONVERT(varchar, Date_Transmission, 103) as dtTrans'),
            DB::raw('CONVERT(varchar, Date_Enregistrement, 103) as datetransestim'),
            DB::raw('CONVERT(varchar, DATEADD(day, 1, Date_Enregistrement), 103) AS dateretestim'),
            'Statut_Ligne as stLigne',
            'Code_partenaire', // Corrigé : Code_partenaire
            DB::raw('CASE WHEN dateRetourMedecin IS NOT NULL THEN 1 ELSE 0 END as retour_recu'),
            DB::raw('CASE Statut_Ligne WHEN 3 THEN \'Validée - Prête à Transmettre\' WHEN 5 THEN \'Transmise - En Attente Retour\' ELSE \'Autre\' END as statut_label')
        ])
        ->leftJoin('partenaires as p', 'ligne_suivi.Code_partenaire', '=', 'p.id') // Corrigé : Code_partenaire
        ->where('rejete', 0)
        ->whereIn('Statut_Ligne', [3, 5]) // ✅ 3 + 5
        ->whereNotNull('Date_Transmission') // ET Date_Transmission set
        ->whereNotNull('Code_partenaire') // Corrigé : Code_partenaire
        ->where('p.type', 'souscripteur') // Corrigé : utilise p.type
        ->where('is_evac', 0)
        ->orderBy('Statut_Ligne', 'asc') // 3 en premier
        ->orderBy('dateRetourMedecin', 'asc')
        ->get();

        Log::info('Transmission-facture: Individuels [3,5] Results', [
            'count' => $factures->count(),
            'sample' => $factures->first()?->toArray() ?? 'Aucun', // ✅ Eloquent : toArray() OK
            'statuts_found' => $factures->pluck('stLigne')->unique()->toArray() // [3,5]
        ]);

        $factures->each(function ($facture) {
            $facture->ref = $facture->ref ?? 'N/A';
            $facture->prest = $facture->prest ?? $facture->Nom_Assure ?? 'N/A';
            $facture->dtTransMed = $facture->dtTransMed ?? 'Non transmis';
            $facture->dtRetourMed = $facture->dtRetourMed ?? 'En attente';
            $facture->type_label = 'Individuel';
        });

    } else {
        // Branche tiers-payant (autres profils) : Seulement tiers-payant stLigne IN [3,5]
        $factures = LigneSuivi::select([
            'Id_Ligne as id',
            DB::raw('(SELECT nom FROM partenaires WHERE id = ligne_suivi.Code_partenaire AND type = \'prestataire\') as prest'), // Corrigé : Code_partenaire et type
            'Numero_Reception',
            'Reference_Facture as ref',
            DB::raw('CONVERT(varchar, Date_Debut, 103) as deb'),
            DB::raw('CONVERT(varchar, Date_Fin, 103) as fin'),
            DB::raw('CAST(Montant_Ligne AS INT) as mtligne'),
            DB::raw('CONVERT(varchar, Date_Enregistrement, 103) as dtEnreg'),
            DB::raw('CONVERT(varchar, datetransMedecin, 103) as dtTransMed'),
            DB::raw('CONVERT(varchar, dateRetourMedecin, 103) as dtRetourMed'),
            DB::raw('CONVERT(varchar, Date_Transmission, 103) as dtTrans'),
            DB::raw('\'\' as datetransestim'),
            DB::raw('\'\' AS dateretestim'),
            'Statut_Ligne as stLigne',
            DB::raw('CASE WHEN dateRetourMedecin IS NOT NULL THEN 1 ELSE 0 END as retour_recu'),
            DB::raw('CASE Statut_Ligne WHEN 3 THEN \'Validée - Prête à Transmettre\' WHEN 5 THEN \'Transmise - En Attente Retour\' ELSE \'Autre\' END as statut_label')
        ])
        ->leftJoin('partenaires as p', 'ligne_suivi.Code_partenaire', '=', 'p.id') // Corrigé : Code_partenaire
        ->where('rejete', 0)
        ->whereRaw('ISNULL(annuler, 0) = 0')
        ->whereIn('Statut_Ligne', [3, 5]) // ✅ 3 + 5
        ->whereNotNull('Date_Transmission') // ET Date_Transmission set
        ->whereNotNull('Code_partenaire') // Corrigé : Code_partenaire
        ->where('p.type', 'prestataire') // Corrigé : utilise p.type
        ->whereRaw('(SELECT coutierG FROM partenaires WHERE id = Code_partenaire) IS NULL OR coutierG = 0') // Corrigé : Code_partenaire
        ->where('is_evac', 0)
        ->orderBy('Statut_Ligne', 'asc') // 3 en premier
        ->orderBy('dateRetourMedecin', 'asc')
        ->get();

        Log::info('Transmission-facture: Tiers-Payant [3,5] Results', [
            'count' => $factures->count(),
            'sample' => $factures->first()?->toArray() ?? 'Aucun', // ✅ Eloquent : toArray() OK
            'statuts_found' => $factures->pluck('stLigne')->unique()->toArray() // [3,5]
        ]);

        $factures->each(function ($facture) {
            $facture->prest = $facture->prest ?? 'N/A';
            $facture->ref = $facture->ref ?? $facture->Reference_Facture ?? 'N/A';
            $facture->dtTransMed = $facture->dtTransMed ?? 'Non transmis';
            $facture->dtRetourMed = $facture->dtRetourMed ?? 'En attente';
            $facture->type_label = 'Tiers-Payant';
        });
    }

    $title = $isAdmin
        ? 'Transmission et Retour des Factures Validées/Transmises (ADMIN)'
        : ($isIndividuel
            ? 'Transmission et Retour des Factures Individuelles Validées/Transmises'
            : 'Transmission et Retour des Factures Tiers-Payant Validées/Transmises');

    Log::info('Transmission-facture: Final [3,5]', [
        'isAdmin' => $isAdmin,
        'isIndividuel' => $isIndividuel,
        'factures_count' => $factures->count(),
        'title' => $title,
        'statuts_found' => $factures->pluck('stLigne')->unique()->toArray(), // ✅ [3,5]
        'sample_ids' => $factures->pluck('id')->take(3)->toArray(), // ✅ IDs sample pour debug
        'types_found' => $factures->pluck('type_label')->unique()->toArray() // ✅ Types (Individuel/Tiers-Payant/Autre)
    ]);

    // Variables communes pour layout (inchangé)
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
        'factures' => $factures,     // ✅ Factures avec stLigne [3,5]
        'isIndividuel' => $isIndividuel,
        'isAdmin' => $isAdmin,
        'title' => $title,
        'fakers' => $fakers,
        'profil_id' => $profil_id,
    ]);
}


    if ($pageName === 'courrier-instance') {
    $courriers = \App\Models\Courier::orderBy('DateRecep', 'desc')->paginate(10);
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
                'page_name' => 'traitement-speciaux',
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
    // 1. Cartes statistiques
    $stats = $this->getStats($annee);

    // 2. Tableau "FACTURES SANTE (POINT MENSUEL)"
    $pointMensuel = $this->getPointMensuel($annee);

    // 3. Tableau "REPARTITION MENSUELLE"
    $repartitionMensuelle = $this->getRepartitionMensuelle($annee);

    return [
        'stats' => $stats,
        'pointMensuel' => $pointMensuel,
        'repartitionMensuelle' => $repartitionMensuelle,
    ];
}

private function getStats($annee)
{
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

    return [
        'nonTraites' => ['montant' => $totalInst, 'nombre' => $nbreInst],
        'demandes' => ['montant' => $totalDemande, 'totalFacture' => $totalAll, 'taux' => $tauxReglement],
        'regles' => ['montant' => $totalRegle, 'montantDemande' => $totalDemande, 'taux' => $tauxRegle],
    ];
}

private function getPointMensuel($annee)
{
    $categories = [
        'Pharmacie' => "tp.code_type_prestataire = '0' AND ls.Code_partenaire IS NULL AND ls.is_evac = 0 AND p.type = 'prestataire'", // Corrigé : Code_partenaire et p.type
        'Parapharmacie' => "tp.code_type_prestataire = '1' AND ls.Code_partenaire IS NULL AND ls.is_evac = 0 AND (p.coutierG IS NULL OR p.coutierG = 0) AND p.type = 'prestataire'", // Corrigé
        'Individuels' => "ls.Code_partenaire IS NULL AND ls.is_evac = 0", // Corrigé : Code_partenaire
        'Evacuation' => "ls.Code_partenaire IS NULL AND ls.is_evac = 1", // Corrigé
        'Appels de fonds' => "tp.code_type_prestataire = '1' AND ls.Code_partenaire IS NULL AND ls.is_evac = 0 AND p.coutierG = 1 AND p.type = 'prestataire'", // Corrigé
    ];

    $data = [];
    foreach ($categories as $categorie => $condition) {
        $query = "
            SELECT Mois_Facture,
                   SUM(CASE WHEN Numero_demande IS NULL THEN Montant_Ligne - ISNULL(Montant_Reglement, 0) ELSE 0 END) AS montant
            FROM Ligne_Suivi ls
            LEFT JOIN partenaires p ON ls.Code_partenaire = p.id  -- Corrigé : Code_partenaire
            LEFT JOIN type_prestataires tp ON p.code_type_prestataire = tp.code_type_prestataire
            WHERE rejete = 0 AND ISNULL(annuler, 0) = 0 AND Annee_Facture = ? AND statut_ligne NOT IN (8, 4) AND {$condition}
            GROUP BY Mois_Facture
        ";
        $results = DB::connection('sqlsrv')->select($query, [$annee]);
        $moisData = array_fill(1, 12, 0);
        foreach ($results as $row) {
            $moisData[$row->Mois_Facture] = $row->montant;
        }
        $data[$categorie] = $moisData;
    }

    // Ligne Total
    $total = array_fill(1, 12, 0);
    foreach ($data as $moisData) {
        foreach ($moisData as $mois => $valeur) {
            $total[$mois] += $valeur;
        }
    }
    $data['Total'] = $total;

    return $data;
}

private function getRepartitionMensuelle($annee)
{
    $categories = [
        'Pharmacie' => "tp.code_type_prestataire = '0' AND ls.Code_partenaire IS NULL AND ls.is_evac = 0 AND p.type = 'prestataire'", // Corrigé
        'Parapharmacie' => "tp.code_type_prestataire = '1' AND ls.Code_partenaire IS NULL AND ls.is_evac = 0 AND (p.coutierG IS NULL OR p.coutierG = 0) AND p.type = 'prestataire'", // Corrigé
        'Individuels' => "ls.Code_partenaire IS NULL AND ls.is_evac = 0", // Corrigé
        'Evacuation' => "ls.Code_partenaire IS NULL AND ls.is_evac = 1", // Corrigé
        'Appels de fonds' => "tp.code_type_prestataire = '1' AND ls.Code_partenaire IS NULL AND ls.is_evac = 0 AND p.coutierG = 1 AND p.type = 'prestataire'", // Corrigé
    ];

    $data = [];
    foreach ($categories as $categorie => $condition) {
        $query = "
            SELECT Mois_Facture, COUNT(*) AS nombre
            FROM Ligne_Suivi ls
            LEFT JOIN partenaires p ON ls.Code_partenaire = p.id  -- Corrigé : Code_partenaire
            LEFT JOIN type_prestataires tp ON p.code_type_prestataire = tp.code_type_prestataire
            WHERE rejete = 0 AND ISNULL(annuler, 0) = 0 AND Annee_Facture = ? AND {$condition}
            GROUP BY Mois_Facture
        ";
        $results = DB::connection('sqlsrv')->select($query, [$annee]);
        $moisData = array_fill(1, 12, 0);
        foreach ($results as $row) {
            $moisData[$row->Mois_Facture] = $row->nombre;
        }
        $data[$categorie] = $moisData;
    }

    // Ligne Total
    $total = array_fill(1, 12, 0);
    foreach ($data as $moisData) {
        foreach ($moisData as $mois => $valeur) {
            $total[$mois] += $valeur;
        }
    }
    $data['Total'] = $total;

    return $data;
}



public function getDashboardDataAjax(Request $request)
{
    $annee = $request->get('annee', date('Y'));
    $data = $this->getDashboardData($annee);
    return response()->json($data);
}
}
