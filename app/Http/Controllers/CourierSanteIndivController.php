<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CourierSanteIndiv;
use App\Models\LigneSuivi;
use App\Models\Partenaire;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use TCPDF;
     use Illuminate\Support\Facades\Schema;
     use Illuminate\Support\Facades\Route;



class CourierSanteIndivController extends Controller
{
    // Affiche la liste des courriers
    public function index()
    {
        $courriers = CourierSanteIndiv::orderBy('datereception', 'desc')->get();
        return view('pages.depot-individuel', compact('courriers')); // Chemin corrigé
    }

    // Affiche le formulaire de création
    public function create()
    {
        return view('courriers.create');
    }

public function store(Request $request)
{
    $request->validate([
        'edition' => 'required|date',
        'nomd' => 'required|string|max:255',
        'prenomd' => 'required|string|max:255',
        'struct' => 'required|string|max:255',
        'nbetatd' => 'required|integer|min:1',
        'nomcompte' => 'required|string|max:255',
        'motif' => 'required|string',
        'enreg' => 'required|date_format:d/m/Y',
        'nometarec' => 'required|integer|min:1',
        'nomrec' => 'required|string|max:255',
        'prenomrec' => 'required|string|max:255',
    ]);

    try {
        // Formatage des dates (inchangé)
        $dateDepot = Carbon::parse($request->edition)->format('Y-m-d H:i:s');
        $dateEnreg = Carbon::createFromFormat('d/m/Y', $request->enreg)->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $dateSysteme = Carbon::now()->format('Y-m-d H:i:s');

        // Code temporaire (inchangé)
        $anneeActuelle = Carbon::now()->year;
        $site = "NSIA";
        $codeTemporaire = 'TEMP_' . uniqid() . '_' . $anneeActuelle . '_' . $site;

        // Préparation des valeurs (inchangé)
        $pdo = DB::getPdo();
        $values = [
            $pdo->quote($codeTemporaire),
            $pdo->quote(trim($request->nomd)),
            $pdo->quote(trim($request->prenomd)),
            $pdo->quote(trim($request->struct)),
            $pdo->quote(trim($request->motif)),
            "CONVERT(datetime, " . $pdo->quote($dateDepot) . ", 120)",
            $pdo->quote(trim($request->nomcompte)),
            (int) $request->nbetatd,
            (int) $request->nometarec,
            "CONVERT(datetime, " . $pdo->quote($dateSysteme) . ", 120)",
            "CONVERT(datetime, " . $pdo->quote($dateEnreg) . ", 120)",
            $pdo->quote(trim($request->nomrec . ' ' . $request->prenomrec)),
            $pdo->quote(Auth::user()->name ?? 'Utilisateur'),
        ];

        // Log des valeurs
        Log::info('Valeurs échappées pour insertion manuelle (avec CONVERT)', $values);

        // Counts avant INSERT
        $countBefore = DB::table('courier_sante_indivs')->count();
        Log::info('Nombre de lignes avant INSERT', ['count' => $countBefore]);

        // Construction de la requête INSERT avec OUTPUT (pour ID direct)
        $insertSql = "INSERT INTO courier_sante_indivs
                      (CodeCour, NomDeposant, PrenomDeposant, structure, motif, DateDepot,
                       Comptede, nbreetatdepot, nbrerecu, datesysteme, datereception,
                       Receptioniste, utilisateurSaisie)
                      OUTPUT INSERTED.NumCour
                      VALUES (" . implode(', ', $values) . ")";

        // Log de la requête INSERT
        Log::info('Requête INSERT avec OUTPUT générée', ['sql' => $insertSql]);

        // Exécution et récupération ID via OUTPUT
        $result = DB::selectOne($insertSql);
        $numCourrier = $result ? $result->NumCour : null;

        // Fallback si OUTPUT null
        if (!$numCourrier) {
            $numCourrier = DB::selectOne("SELECT @@IDENTITY as id")->id;
        }

        // Counts après et vérification
        $countAfter = DB::table('courier_sante_indivs')->count();
        $added = $countAfter - $countBefore;
        Log::info('Nombre de lignes après INSERT', ['count' => $countAfter, 'added' => $added, 'id_recupere' => $numCourrier]);

        if (!$numCourrier || $numCourrier <= 0 || $added !== 1) {
            throw new \Exception('Échec de l\'insertion : ID non récupéré (' . ($numCourrier ?? 'null') . ') ou lignes ajoutées inattendues (' . $added . ')');
        }

        // Code final
        $codeCourrier = $numCourrier . '/CRSANT/' . $anneeActuelle . '/' . $site;

        // UPDATE du code (exécuté maintenant)
        $updateSql = "UPDATE courier_sante_indivs SET CodeCour = " . $pdo->quote($codeCourrier) . " WHERE NumCour = " . (int) $numCourrier;
        Log::info('Requête UPDATE générée et exécutée', ['update_sql' => $updateSql]);
        DB::unprepared($updateSql);

        // Vérif post-UPDATE
        $updatedRow = DB::table('courier_sante_indivs')->where('NumCour', $numCourrier)->first();
        Log::info('Ligne après UPDATE', ['code_cour_final' => $updatedRow->CodeCour ?? 'Non trouvé']);

        Log::info('Courrier créé avec succès (avec OUTPUT ID)', [
            'NumCour' => $numCourrier,
            'CodeCour' => $codeCourrier,
            'lignes_ajoutees' => $added,
        ]);

        return redirect()->back()->with('success', 'Courrier enregistré avec succès. Code: ' . $codeCourrier);

    } catch (\Exception $e) {
        Log::error('Erreur création courrier (avec OUTPUT)', [
            'message_erreur' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'donnees_request' => $request->all(),
            'dates_formatees' => [
                'dateDepot' => $dateDepot ?? 'Non définie',
                'dateEnreg' => $dateEnreg ?? 'Non définie',
                'dateSysteme' => $dateSysteme ?? 'Non définie',
            ],
            'valeurs_echappees' => $values ?? 'Non préparées',
            'insert_sql' => $insertSql ?? 'Non générée',
            'count_before_after' => [$countBefore ?? 'N/A', $countAfter ?? 'N/A', 'added' => $added ?? 'N/A'],
        ]);

        return redirect()->back()
            ->withErrors(['error' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage()])
            ->withInput();
    }
}






public function printFiche($numCour)
    {
        try {
            $courrier = CourierSanteIndiv::findOrFail($numCour);

            // Création du PDF avec TCPDF (orientation portrait, unités mm, format A4)
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->SetCreator('NSIA Assurances');
            $pdf->SetAuthor('Système de Gestion Courriers');
            $pdf->SetTitle('Fiche de Dépôt - ' . $courrier->CodeCour);
            $pdf->SetSubject('Fiche de dépôt courrier santé individuel');
            $pdf->SetMargins(15, 15, 15); // Marges
            $pdf->SetAutoPageBreak(true, 15);
            $pdf->AddPage();

            // Police par défaut (TCPDF inclut DejaVu ; si besoin custom, voir ci-dessous)
            $pdf->SetFont('dejavusans', '', 14); // 'dejavusans' est intégré à TCPDF pour UTF-8

            $h = 7;
            $retrait = str_repeat(' ', 6); // Équivalent à "      " (6 espaces)
            $retrait3 = str_repeat(' ', 29);

            // Entête
            $pdf->Cell(0, 10, 'Fiche de dépôt', 0, 1, 'C');
            $pdf->Ln(5);

            // Données du dépôt (gestion des nuls)
            $nomPrenomDeposant = strtoupper(($courrier->NomDeposant ?? '') . ' ' . ($courrier->PrenomDeposant ?? ''));
            $dateDepot = $courrier->DateDepot ? $courrier->DateDepot->format('d/m/Y') : 'N/A';
            $dateReception = $courrier->datereception ? $courrier->datereception->format('d/m/Y') : 'N/A';

            $pdf->Cell(0, $h, $retrait . 'Ref.Courrier : ' . $courrier->CodeCour, 0, 1, 'L');
            $pdf->Cell(0, $h, $retrait . 'Date Dépôt : ' . $dateDepot, 0, 1, 'L');
            $pdf->Cell(0, $h, $retrait . "Nom et prénom du déposant : " . $nomPrenomDeposant, 0, 1, 'L');
            $pdf->Cell(0, $h, $retrait . "Structure : " . strtoupper($courrier->structure ?? ''), 0, 1, 'L');
            $pdf->Cell(0, $h, $retrait . "Nombre d'état(s) : " . ($courrier->nbreetatdepot ?? 0), 0, 1, 'L');
            $pdf->Cell(0, $h, $retrait . "Pour le compte de : " . ($courrier->Comptede ?? ''), 0, 1, 'L');
            $pdf->Cell(0, $h, $retrait . "Motif : " . ($courrier->motif ?? ''), 0, 1, 'L');
            $pdf->Ln(8);

            // Section Réceptionniste
            $pdf->SetFont('dejavusans', 'B', 13);
            $pdf->Cell(0, 10, 'RECEPTIONNISTE', 0, 1, 'C');
            $pdf->Ln(8);

            $pdf->SetFont('dejavusans', '', 12);
            $pdf->Cell(0, $h, $retrait . "Date : " . $dateReception, 0, 1, 'L');
            $pdf->Cell(0, $h, $retrait . "Nom et prénom : " . strtoupper($courrier->Receptioniste ?? ''), 0, 1, 'L');
            $pdf->Cell(0, $h, $retrait . "Nombre d'état(s) Reçu(s): " . ($courrier->nbrerecu ?? 0), 0, 1, 'L');
            $pdf->Ln(8);

            // Signature (ligne unique)
            $pdf->Cell(0, $h, $retrait3 . "Signature du déposant " . $retrait3 . " Pour la NSIA Assurances", 0, 1, 'L');
            $pdf->Ln(40);

            // Pied de page (TCPDF gère automatiquement les pages ; {nb} pour total)
            $pdf->SetFont('dejavusans', 'I', 8);
            $pdf->SetY(-30);
            $pdf->Cell(0, 10, 'Edité le : ' . now()->format('d/m/Y'), 0, 1, 'C');
            $pdf->Cell(0, 10, 'Page ' . $pdf->getPage() . '/{nb}', 0, 0, 'C');

            // Retourner le PDF
            $filename = 'fiche_depot_' . $numCour . '.pdf';
            return response($pdf->Output($filename, 'S'), 200) // 'S' pour string
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"');

        } catch (\Exception $e) {
            Log::error('Erreur génération PDF TCPDF : ' . $e->getMessage(), [
                'numCour' => $numCour,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Erreur PDF : ' . $e->getMessage()], 500);
        }
    }


 public function saisieFactureModal($numCour)
    {
        try {
            // Récupérer le courrier par son NumCour
            $courrier = CourierSanteIndiv::findOrFail($numCour);

            // Calcul "Nombre Restant" (adapté ; ajustez 'CodeCour' si colonne est 'codecour')
            $existingFactures = DB::table('ligne_suivi')
                ->where('numero_reception', $courrier->NumCour)
                ->where('CodeCour', $courrier->CodeCour) // Ajusté pour PascalCase (SQL Server)
                ->selectRaw('
                    COUNT(*) as nr,
                    COALESCE(SUM(nbfacture), 0) as total_factures
                ')
                ->first();

            $nombreExistant = $existingFactures->nr ?? 0;
            $totalFactures = $existingFactures->total_factures ?? 0;
            $nombreRestant = max(0, $totalFactures - $nombreExistant);

            // Récupérer les options pour les dropdowns (fallback si scopes inexistants)
            try {
                $souscripteurs = Partenaire::souscripteurs()->orderBy('nom')->get();
            } catch (\Exception $e) {
                // Fallback : requête DB directe (ajustez 'type' = 'souscripteur' si différent)
                Log::warning('Scope souscripteurs() échoué, fallback DB', ['error' => $e->getMessage()]);
                $souscripteurs = DB::table('partenaires')->where('type', 'souscripteur')->orderBy('nom')->get(['id', 'nom']);
            }

            try {
                $prestataires = Partenaire::prestataires()->orderBy('nom')->get();
            } catch (\Exception $e) {
                // Fallback : requête DB directe (ajustez 'type' = 'prestataire' si différent)
                Log::warning('Scope prestataires() échoué, fallback DB', ['error' => $e->getMessage()]);
                $prestataires = DB::table('partenaires')->where('type', 'prestataire')->orderBy('nom')->get(['id', 'nom']);
            }

            $moisList = DB::table('parametres')
                ->where('typaram', 'MoisFacture')
                ->orderByDesc('codtyparam')
                ->select('codtyparam as Id_mois', 'libelleparam as libelle_mois')
                ->get();

            // Années : année courante + 5 dernières années distinctes de ligne_suivi
            $anneesQuery = DB::table('ligne_suivi')
                ->whereNotNull('annee_facture')
                ->where('annee_facture', '<>', Carbon::now()->year)
                ->distinct()
                ->take(5)
                ->orderByDesc('annee_facture')
                ->pluck('annee_facture')
                ->toArray();

            $annees = array_merge([Carbon::now()->year], $anneesQuery);

            // Logique de profil utilisateur (avec fallback si pas de relation 'profil')
            $user = auth()->user();
            $profil_id = null;
            try {
                $profil_id = $user->profil->id ?? null;
            } catch (\Exception $e) {
                Log::warning('Relation profil échouée, fallback null', ['error' => $e->getMessage()]);
            }

            $isIndividuel = in_array($profil_id, [3, 4, 5]);
            $isTiersPayant = in_array($profil_id, [3, 5, 8]);
            $isEvacMode = ($profil_id == 7);

            // Log pour debug
            Log::info('Chargement modal saisie facture', [
                'numCour' => $numCour,
                'codeCour' => $courrier->CodeCour,
                'nombreRestant' => $nombreRestant,
                'profil_id' => $profil_id,
                'isIndividuel' => $isIndividuel,
                'isTiersPayant' => $isTiersPayant,
                'isEvacMode' => $isEvacMode
            ]);

            // Retourner le partial
            return view('pages.courriers.partials.saisie-facture-form', compact(
                'courrier',
                'souscripteurs',
                'prestataires',
                'moisList',
                'annees',
                'nombreRestant',
                'isIndividuel',
                'isTiersPayant',
                'isEvacMode'
            ));

        } catch (\Exception $e) {
            Log::error('Erreur chargement modal saisie facture', [
                'numCour' => $numCour,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Erreur lors du chargement du formulaire : ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Stores the facture linked to a courrier (adapts LigneSuiviController::store logic).
     * On success, returns JSON to trigger reload.
     */


public function storeLigneSuivi(Request $request)
{
    // Log 1: Entrée de la méthode - Données entrantes et contexte utilisateur
    Log::info('=== DÉBUT storeLigneSuivi ===', [
        'url' => $request->fullUrl(),
        'method' => $request->method(),
        'is_ajax' => $request->ajax() || $request->wantsJson(),
        'user_id' => auth()->id(),
        'user_name' => auth()->user()->name ?? 'Non authentifié',
        'donnees_request' => $request->all(),
        'headers' => [
            'X-Requested-With' => $request->header('X-Requested-With'),
            'Content-Type' => $request->header('Content-Type'),
            'Accept' => $request->header('Accept')
        ]
    ]);

    // Vérification auth
    if (!auth()->check()) {
        Log::error('Accès non authentifié à storeLigneSuivi');
        if ($request->ajax()) {
            return response()->json(['success' => false, 'error' => 'Non authentifié.'], 401);
        }
        return redirect('/login')->with('error', 'Authentification requise.');
    }

    // Logique de profil
    $user = auth()->user();
    $profil_id = $user->profil->id ?? null;
    $isIndividuel = in_array($profil_id, [3, 4, 5]);
    $isTiersPayant = in_array($profil_id, [3, 5, 8]);
    $isEvacMode = ($profil_id == 7);

    Log::info('Configuration profil pour validation', [
        'profil_id' => $profil_id,
        'isIndividuel' => $isIndividuel,
        'isTiersPayant' => $isTiersPayant,
        'isEvacMode' => $isEvacMode
    ]);

    // Règles de validation
    $rules = [
        'reference_facture' => 'nullable|string|max:50',
        'mois' => 'nullable|integer|min:1|max:12',
        'annee' => 'nullable|integer|min:1900|max:' . (Carbon::now()->year + 1),
        'date_debut' => 'nullable|date|before_or_equal:date_fin',
        'date_fin' => 'nullable|date|after_or_equal:date_debut',
        'montant' => 'nullable|numeric|min:0',
        'nb_factures' => 'nullable|integer|min:1',
        'CodeCour' => 'nullable|string|max:30',
        'numCour' => 'nullable|integer',
    ];

    Log::info('Règles de validation appliquées', ['rules' => $rules]);

    if ($isIndividuel) {
        $rules['assure'] = 'nullable|string|max:255';
        $rules['idSouscripteur'] = 'nullable|exists:partenaires,id';
        Log::info('Ajout règles pour mode individuel', ['champs_ajoutes' => ['assure', 'idSouscripteur']]);
    }
    if ($isTiersPayant && !$isIndividuel) {
        $rules['Code_partenaire'] = 'nullable|string|max:5|exists:partenaires,id'; // Corrigé : Code_partenaire
        Log::info('Ajout règles required pour mode tiers-payant pur', ['champs_ajoutes' => ['Code_partenaire']]);
    } elseif ($isTiersPayant) {
        $rules['Code_partenaire'] = 'nullable|string|max:5|exists:partenaires,id'; // Corrigé : Code_partenaire
        Log::info('Ajout règle nullable pour Code_partenaire (mode mixte)', ['champs_ajoutes' => ['Code_partenaire']]);
    }
    if ($isEvacMode) {
        $rules['is_evac'] = 'nullable|boolean';
        Log::info('Ajout règle pour mode évacuation', ['champ_ajoute' => 'is_evac']);
    }

    Log::info('Règles de validation finales', ['final_rules' => $rules]);

    // Validation
    try {
        $validated = $request->validate($rules);
        Log::info('Validation réussie', ['validated_data' => $validated]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::warning('ÉCHEC validation saisie facture courrier', [
            'CodeCour' => $request->CodeCour ?? 'N/A',
            'profil_id' => $profil_id ?? 'N/A',
            'errors' => $e->errors(),
            'donnees_request' => $request->all()
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'error' => 'Erreurs de validation.',
                'errors' => $e->errors()
            ], 422);
        }
        return back()->withErrors($e->errors())->withInput();
    }

    try {
        // Formatage dates AVEC FORMAT SAFE POUR SQL SERVER (YYYYMMDD HH:MM:SS) - Évite erreurs de locale
        $dateDebut = Carbon::parse($request->date_debut)->startOfDay()->format('Ymd H:i:s');  // Ex: '20250918 00:00:00'
        $dateFin = Carbon::parse($request->date_fin)->startOfDay()->format('Ymd H:i:s');      // Ex: '20250922 00:00:00'
        $dateEnregistrement = Carbon::now()->format('Ymd H:i:s');                             // Ex: '20250925 10:42:17'

        Log::info('Préparation des données pour insertion DB (IDENTITY auto, format date safe)', [
            'max_id_actuel' => DB::table('Ligne_Suivi')->max('Id_Ligne') ?? 0,
            'date_debut_formatee' => $dateDebut,
            'date_fin_formatee' => $dateFin,
            'date_enreg' => $dateEnregistrement,
            'annee_cast_string' => str_pad((string) $request->annee, 4, '0', STR_PAD_LEFT)
        ]);

        // Données communes (adaptées au schéma, avec dates safe)
        $data = [
            'Reference_Facture' => $request->reference_facture,
            'Mois_Facture' => (int) $request->mois,  // Assuré int non-null
            'Annee_Facture' => str_pad((string) $request->annee, 4, '0', STR_PAD_LEFT),  // String pour nchar(8)
            'Date_Debut' => $dateDebut,  // Format safe
            'Date_Fin' => $dateFin,      // Format safe
            'Montant_Ligne' => (float) $request->montant,
            'Date_Enregistrement' => $dateEnregistrement,  // Format safe
            'Redacteur' => trim($user->name ?? 'Système'),  // Trim pour éviter espaces
            'nbfacture' => (int) $request->nb_factures,  // Non-null int
            'Numero_Reception' => (int) $request->numCour,
            'Statut_Ligne' => 0,  // Non-null int
            'CodeCour' => $request->CodeCour,
            'rejete' => '0',  // String pour nchar(2), non-null
            'code_etape' => 0,  // Non-null int
        ];

        Log::info('Données communes pour insertion (adaptées au schéma, dates safe)', ['data' => $data]);

        // Vérification rapide des non-nullables critiques (debug optionnel)
        if (is_null($data['Mois_Facture']) || $data['Mois_Facture'] <= 0) {
            throw new \Exception('Mois_Facture invalide (NULL ou <=0) : ' . ($data['Mois_Facture'] ?? 'NULL'));
        }

        // Champs spécifiques
        if ($isIndividuel) {
            $data['Nom_Assure'] = $request->assure;
            $data['Code_partenaire'] = (int) $request->idSouscripteur; // Corrigé : Code_partenaire
            $data['is_evac'] = $isEvacMode && $request->has('is_evac') ? 1 : 0;  // Non-null int
            Log::info('Mode individuel : Champs spécifiques ajoutés', [
                'Nom_Assure' => $data['Nom_Assure'],
                'Code_partenaire' => $data['Code_partenaire'], // Corrigé
                'is_evac' => $data['is_evac']
            ]);
        } elseif ($isTiersPayant) {
            $data['Nom_Assure'] = null;
            $data['Code_partenaire'] = $request->Code_partenaire; // Corrigé : Code_partenaire
            $data['is_evac'] = 0;  // Non-null int
            Log::info('Mode tiers-payant : Champs spécifiques ajoutés', [
                'Code_partenaire' => $data['Code_partenaire'], // Corrigé
                'is_evac' => $data['is_evac']
            ]);
        } else {
            $data['Nom_Assure'] = $request->assure ?? null;
            $data['Code_partenaire'] = $request->idSouscripteur ? (int) $request->idSouscripteur : ($request->Code_partenaire ?? null); // Corrigé : Code_partenaire
            $data['is_evac'] = $isEvacMode && $request->has('is_evac') ? 1 : 0;  // Non-null int
            Log::info('Mode par défaut : Champs spécifiques ajoutés', [
                'Nom_Assure' => $data['Nom_Assure'],
                'Code_partenaire' => $data['Code_partenaire'], // Corrigé
                'is_evac' => $data['is_evac']
            ]);
        }

        Log::info('Données finales pour insertion DB (IDENTITY auto, schéma adapté, dates safe)', ['final_data' => $data]);

        // Activation temporaire du query log pour tracer SQL (désactivez en prod pour perf)
        DB::enableQueryLog();
        $insertResult = DB::table('Ligne_Suivi')->insert($data);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        if (!$insertResult) {
            throw new \Exception('Échec de l\'insertion : 0 ligne affectée.');
        }

        Log::info('Requête SQL générée par Query Builder (avec quoting et dates safe)', [
            'sql' => $queries[0]['query'] ?? 'Non tracée',
            'bindings' => $queries[0]['bindings'] ?? 'Non tracés',
            'affected_rows' => 1,
            'note' => 'Format dates safe évite les erreurs de conversion nvarchar→datetime'
        ]);

        // Récupérer ID auto-généré (max après insert)
        $insertedId = DB::table('Ligne_Suivi')->max('Id_Ligne');

        // Log 8: Succès insertion
        Log::info('Insertion DB réussie (IDENTITY auto)', [
            'inserted_id' => $insertedId,
            'table' => 'Ligne_Suivi',
            'affected_rows' => 1,
            'CodeCour' => $request->CodeCour,
            'numCour' => $request->numCour,
            'profil_id' => $profil_id,
            'montant' => $data['Montant_Ligne'],
            'isIndividuel' => $isIndividuel,
            'isTiersPayant' => $isTiersPayant
        ]);

        // Vérification post-insertion
        $insertedRow = DB::table('Ligne_Suivi')->where('Id_Ligne', $insertedId)->first();
        Log::info('Vérification post-insertion : Ligne créée', [
            'inserted_row' => $insertedRow ? [
                'Id_Ligne' => $insertedRow->Id_Ligne,
                'Reference_Facture' => $insertedRow->Reference_Facture,
                'Nom_Assure' => $insertedRow->Nom_Assure,
                'Code_partenaire' => $insertedRow->Code_partenaire, // Corrigé
                'Annee_Facture' => $insertedRow->Annee_Facture,
                'Numero_Reception' => $insertedRow->Numero_Reception,
                'CodeCour' => $insertedRow->CodeCour,
                'Date_Debut' => $insertedRow->Date_Debut  // Devrait être au format SQL Server
            ] : 'Non trouvée (erreur)',
            'success' => $insertedRow ? 'Oui' : 'Non'
        ]);

        // Retour
        if ($request->ajax() || $request->wantsJson()) {
            $response = response()->json([
                'success' => true,
                'message' => 'Facture enregistrée avec succès pour le courrier ' . $request->CodeCour . '. (ID: ' . $insertedId . ')',
                'inserted_id' => $insertedId
            ]);
            Log::info('Retour JSON pour AJAX', ['status' => 200]);
        } else {
            $response = redirect()->back()->with('success', 'Facture enregistrée avec succès pour le courrier ' . $request->CodeCour . '. (ID: ' . $insertedId . ')');
            Log::info('Retour redirect pour non-AJAX', ['url' => $response->getTargetUrl()]);
        }

        Log::info('=== FIN storeLigneSuivi (SUCCÈS) ===', ['response_type' => $request->ajax() ? 'JSON' : 'Redirect']);

        return $response;

    } catch (\Exception $e) {
        Log::error('ERREUR enregistrement facture liée au courrier', [
            'CodeCour' => $request->CodeCour ?? 'N/A',
            'numCour' => $request->numCour ?? 'N/A',
            'profil_id' => $profil_id ?? 'N/A',
            'message' => $e->getMessage(),
            'code_erreur' => $e->getCode(),
            'trace' => $e->getTraceAsString(),
            'donnees_request' => $request->all(),
            'data_tente_insertion' => $data ?? 'Non préparée',
            'dates_tentees' => [  // Debug dates
                'date_debut' => $dateDebut ?? 'Non préparée',
                'date_fin' => $dateFin ?? 'Non préparée',
                'date_enreg' => $dateEnregistrement ?? 'Non préparée'
            ],
            'sql' => isset($queries) ? ($queries[0]['query'] ?? 'Non tracée') : 'Non générée',
            'bindings' => isset($query) ? $query->getBindings() : 'Non générés'
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de l\'enregistrement : ' . $e->getMessage()
            ], 500);
        }
        return back()->with('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage())->withInput();
    }
}








public function indexCourriersInstance()
{
    try {
        // Vérification authentification
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Authentification requise.');
        }

        $user = Auth::user();
        Log::info('Chargement liste courriers en instance', [
            'user_id' => $user->id,
            'profil_id' => $user->profil?->id ?? null
        ]);

        // Requête partie 1 : courriers non traités (pas de ligne_suivi)
        $nonTraitesQuery = DB::connection('sqlsrv')->table('courrier as c')
            ->join('Table_TypeCour as t', 'c.codeType', '=', 't.CodeType')
            ->leftJoin(DB::raw('(select codtyparam as CodeNat, libelleparam as LibNat from lesparametres where typaram=\'NatureCourrier\') as n'), 'c.CodeNat', '=', 'n.CodeNat')
            ->selectRaw('
                distinct c.numcour,
                c.refcour,
                c.objet,
                c.expediteur,
                CONVERT(varchar, c.dateRecep, 103) as date_reception,
                CONVERT(varchar, c.DateEnreg, 103) as date_enregistrement,
                CONVERT(varchar, c.codecour) as codecour,
                CONVERT(varchar, DATEADD(day, 60, c.daterecep), 103) AS DateClotureEstime,
                c.annee,
                CASE WHEN DATEADD(day, 60, c.daterecep) < GETDATE() THEN DATEDIFF(day, GETDATE(), DATEADD(day, 60, c.daterecep)) ELSE NULL END as statut
            ')
            ->where('t.codetype', 1)
            ->where('n.CodeNat', 3)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('ligne_suivi as suivi')
                      ->whereRaw('suivi.numero_reception = c.numcour');
            });

        // Requête partie 2 : courriers partiellement traités (nr <> nbfacture)
        $partielsQuery = DB::connection('sqlsrv')->table('courrier as c')
            ->join('Table_TypeCour as t', 'c.codeType', '=', 't.CodeType')
            ->leftJoin(DB::raw('(select codtyparam as CodeNat, libelleparam as LibNat from lesparametres where typaram=\'NatureCourrier\') as n'), 'c.CodeNat', '=', 'n.CodeNat')
            ->selectRaw('
                distinct c.numcour,
                c.refcour,
                c.objet,
                c.expediteur,
                CONVERT(varchar, c.dateRecep, 103) as date_reception,
                CONVERT(varchar, c.DateEnreg, 103) as date_enregistrement,
                CONVERT(varchar, c.codecour) as codecour,
                CONVERT(varchar, DATEADD(day, 60, c.daterecep), 103) AS DateClotureEstime,
                c.annee,
                CASE WHEN DATEADD(day, 60, c.daterecep) < GETDATE() THEN DATEDIFF(day, GETDATE(), DATEADD(day, 60, c.daterecep)) ELSE NULL END as statut
            ')
            ->where('t.codetype', 1)
            ->where('n.CodeNat', 3)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->fromSub(function ($sub) {
                          $sub->from('ligne_suivi')
                              ->selectRaw('COUNT(*) as nr, numero_reception, nbfacture')
                              ->groupBy('numero_reception', 'nbfacture')
                              ->havingRaw('nr <> nbfacture');
                      }, 'm')
                      ->whereRaw('m.numero_reception = c.numcour');
            });

        // Union des deux requêtes et tri par date d’enregistrement ascendant
        $courriersQuery = $nonTraitesQuery->union($partielsQuery)
            ->orderBy('date_enregistrement', 'asc');

        $courriers = $courriersQuery->get();

        // Calcul du texte de retard (nbr)
        $courriers = $courriers->map(function ($cour) {
            $statut = $cour->statut;
            if ($statut && $statut < 0) {
                $cour->nbr = 'Ce courrier a ' . abs((int)$statut) . ' jours de retard';
            } else {
                $cour->nbr = ''; // Ou 'À jour'
            }
            return $cour;
        });

        // Retourne la vue avec la variable $courriers
        return view('pages.courrier-instance', compact('courriers'));

    } catch (\Exception $e) {
        Log::error('Erreur chargement courriers en instance', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return redirect()->back()->with('error', 'Erreur lors du chargement des courriers en instance : ' . $e->getMessage());
    }
}



}
