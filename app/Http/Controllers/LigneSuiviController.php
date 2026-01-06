<?php

namespace App\Http\Controllers;

use App\Models\LigneSuivi;
use App\Models\Partenaire;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;
use App\Exports\BordereauTresoExport;
use Maatwebsite\Excel\Facades\Excel;

//use Illuminate\Support\Facades\DB;


class LigneSuiviController extends Controller
{

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $user = auth()->user();
        $profil_id = $user->profil->id ?? null;

        $isIndividuel = in_array($profil_id, [3, 4, 5]);
        $isTiersPayant = in_array($profil_id, [3, 5, 8]);

        // Base de la requête avec jointure pour type
        $query = LigneSuivi::query()
            ->leftJoin('partenaires', 'Ligne_Suivi.Code_partenaire', '=', 'partenaires.id') // Jointure pour type
            ->whereNotNull('Ligne_Suivi.Date_Enregistrement')
            ->with(['partenaire']); // Utilise la nouvelle relation

        // Filtres selon le type de profil
        if ($isIndividuel && !$isTiersPayant) {
            // Assuré uniquement : Code_partenaire avec type 'souscripteur' ou null pour individuels purs
            $query->where(function ($q) {
                $q->whereNotNull('Ligne_Suivi.Nom_Assure')
                  ->where(function ($sub) {
                      $sub->whereNull('Ligne_Suivi.Code_partenaire')
                          ->orWhere('partenaires.type', 'souscripteur');
                  });
            });
        } elseif ($isTiersPayant && !$isIndividuel) {
            // Prestataire uniquement : Code_partenaire avec type 'prestataire'
            $query->whereNotNull('Ligne_Suivi.Code_partenaire')
                  ->where('partenaires.type', 'prestataire');
        } elseif ($isIndividuel && $isTiersPayant) {
            // Les deux cas
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

        $factures = $query->get();

        $souscripteurs = Partenaire::where('type', 'souscripteur')->orderBy('nom')->get();
        $prestataires = Partenaire::where('type', 'prestataire')->orderBy('nom')->get();

        $moisList = DB::table('parametres')
            ->where('typaram', 'MoisFacture')
            ->orderByDesc('codtyparam')
            ->select('codtyparam as Id_mois', 'libelleparam as libelle_mois')
            ->get();

        $annees = DB::table('ligne_suivi')
            ->whereNotNull('Annee_Facture')
            ->where('Annee_Facture', '<>', Carbon::now()->year)
            ->distinct()
            ->take(5)
            ->orderByDesc('Annee_Facture')
            ->pluck('Annee_Facture');

        return view('gestion-factures', compact('factures', 'souscripteurs', 'prestataires', 'moisList', 'annees', 'profil_id'));
    }

    public function store(Request $request)
{
    // Déterminer le type de facture selon la source ou les champs présents
    $isIndividuelle = $request->has('assure') || $request->input('source') !== 'tiersPayant';

    if ($isIndividuelle) {
        // Validation pour factures individuelles
        $request->validate([
            'assure' => 'required|string|max:255',
            'idSouscripteur' => 'required|exists:partenaires,id',
            'numero_reception' => 'nullable|string|max:50',
            'reference_facture' => 'required|string|max:50',
            'mois' => 'required|integer|min:1|max:12',
            'annee' => 'required|string',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'montant' => 'required|numeric|min:0',
        ]);
    } else {
        // Validation pour factures tiers-payant
        $request->validate([
            'Code_partenaire' => 'required|exists:partenaires,id',
            'Numero_Reception' => 'required|string|max:50',
            'Reference_Facture' => 'required|string|max:50',
            'Mois_Facture' => 'required|integer|min:1|max:12',
            'Annee_Facture' => 'required|string',
            'Date_Debut' => 'required|date',
            'Date_Fin' => 'required|date|after_or_equal:Date_Debut',
            'Montant_Ligne' => 'required|numeric|min:0',
        ]);
    }
        DB::beginTransaction(); // ✅ Démarrer une transaction explicite
    try {
        $factureId = null; // ✅ Variable pour stocker l'ID de la facture créée
        $user = auth()->user();
        $dateEnregistrement = Carbon::now();
        if ($isIndividuelle) {
            // Traitement facture individuelle
            //$dateDebut = Carbon::createFromFormat('Y-m-d', $request->date_debut)->startOfDay();
             $dateDebut = Carbon::parse($request->date_debut);
              $dateFin = Carbon::parse($request->date_fin);
            //$dateFin = Carbon::createFromFormat('Y-m-d', $request->date_fin)->startOfDay();
            $dateEnregistrement = Carbon::now();


            // ✅ Utiliser insertGetId pour récupérer l'ID
            $factureId = DB::table('Ligne_Suivi')->insertGetId([
                'Nom_Assure' => $request->assure,
                'Code_partenaire' => (int) $request->idSouscripteur,
                'Numero_Reception' => $request->numero_reception,
                'Reference_Facture' => $request->reference_facture,
                'Date_Debut' => $dateDebut,
                'Date_Fin' => $dateFin,
                'Montant_Ligne' => (float) $request->montant,
                'Date_Enregistrement' => $dateEnregistrement,
                'Mois_Facture' => (int) $request->mois,
                'Annee_Facture' => $request->annee,
                'is_evac' => $request->has('is_evac') ? 1 : 0,
                'Statut_Ligne' => 0,
                'rejete' => 0,
                 'redacteur' => $user->name // ✅ Utiliser l'ID au lieu du nom
            ]);

            $message = 'Facture individuelle enregistrée avec succès.';

        } else {
            // Traitement facture tiers-payant
            //   $dateDebut = Carbon::parse($request->date_debut);
            //   $dateFin = Carbon::parse($request->date_fin);
            $dateDebut = Carbon::parse($request->Date_Debut);
            $dateFin   = Carbon::parse($request->Date_Fin);

           // $dateDebut = Carbon::createFromFormat('Y-m-d', $request->Date_Debut)->startOfDay();
            //$dateFin = Carbon::createFromFormat('Y-m-d', $request->Date_Fin)->startOfDay();
            $dateEnregistrement = Carbon::now();

            // ✅ Utiliser insertGetId pour récupérer l'ID
            $factureId = DB::table('Ligne_Suivi')->insertGetId([
                'Nom_Assure' => null,
                'Code_partenaire' => (int) $request->Code_partenaire,
                'Numero_Reception' => $request->Numero_Reception,
                'Reference_Facture' => $request->Reference_Facture,
                'Date_Debut' => $dateDebut,
                'Date_Fin' => $dateFin,
                'Montant_Ligne' => (float) $request->Montant_Ligne,
                'Date_Enregistrement' => $dateEnregistrement,
                'Mois_Facture' => (int) $request->Mois_Facture,
                'Annee_Facture' => $request->Annee_Facture,
                'is_evac' => 0,
                'Statut_Ligne' => 0,
                'rejete' => 0,
                'Redacteur' => $user->name
            ]);

            $message = 'Facture tiers-payant enregistrée avec succès.';
        }

         if ($factureId) {
            try {
                $facture = LigneSuivi::find($factureId);

                if ($facture) {
                    \Log::info('Création notification pour nouvelle facture', [
                        'facture_id' => $factureId,
                        'reference' => $facture->Reference_Facture,
                        'user_id' => auth()->id()
                    ]);

                    $this->notificationService->notifierChangementStatut($facture, null, 0);

                    // ✅ Vérifier immédiatement si créée
                    $notifCount = \DB::table('notifications')
                        ->where('facture_id', $factureId)
                        ->count();

                    \Log::info('Notifications créées (avant commit)', [
                        'count' => $notifCount,
                        'facture_id' => $factureId
                    ]);
                }
            } catch (\Exception $notifException) {
                \Log::error('Erreur notification', [
                    'error' => $notifException->getMessage(),
                    'trace' => $notifException->getTraceAsString()
                ]);
            }
        }
        DB::commit(); // ✅ Commit explicite

        // ✅ Vérifier après commit
        if ($factureId) {
            $notifCountAfter = \DB::table('notifications')
                ->where('facture_id', $factureId)
                ->count();

            \Log::info('Notifications après commit', [
                'count' => $notifCountAfter,
                'facture_id' => $factureId
            ]);
        }



      return redirect()->back()
            ->with('success', $message);

    } catch (\Exception $e) {
        DB::rollBack(); // ✅ Rollback en cas d'erreur

        \Log::error('Erreur lors de la création de la facture', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return redirect()->back()
            ->withInput()
            ->with('error', 'Erreur: ' . $e->getMessage());
    }
}
    // Méthode pour afficher le modal d'édition
public function editModal(Request $request)
{
    $id = $request->id;
    $ligne = LigneSuivi::without('redacteur')
        ->with(['partenaire'])
        ->findOrFail($id);

    // Déterminer le type actuel basé sur la jointure partenaire
    // Si pas de partenaire (cas rare), on peut par défaut dire 'individuelle' si Nom_Assure existe
    $typePartenaireActuel = $ligne->partenaire ? $ligne->partenaire->type : ($ligne->Nom_Assure ? 'souscripteur' : 'prestataire');

    $souscripteurs = Partenaire::where('type', 'souscripteur')->orderBy('nom')->get();
    $prestataires = Partenaire::where('type', 'prestataire')->orderBy('nom')->get();

    return view('pages.edit_modal', compact('ligne', 'souscripteurs', 'prestataires', 'typePartenaireActuel'));
}
// Méthode pour mettre à jour la facture
public function update(Request $request, $id)
{
    try {
        // 1. Validation
        $validatedData = $request->validate([
            'Reference_Facture' => 'required|string|max:255',
            'Mois_Facture' => 'required|integer|min:1|max:12',
            'Date_Debut' => 'required|date',
            'Date_Fin' => 'required|date|after_or_equal:Date_Debut',
            'Montant_Ligne' => 'required|numeric|min:0',
            'Annee_Facture' => 'nullable|integer|min:2020|max:' . (date('Y') + 1),
            'Nom_Assure' => 'nullable|string|max:255',
            'Code_partenaire' => 'nullable|exists:partenaires,id',
            'Date_Reception' => 'nullable|date',
            'Date_Enregistrement' => 'nullable|date',
            'datetransMedecin' => 'nullable|date',
            'Numero_Reception' => 'nullable|string|max:50',
            'is_evac' => 'nullable|boolean',
        ]);

        // 2. Charger la ligne
        $ligne = LigneSuivi::findOrFail($id);
         $ligne = LigneSuivi::findOrFail($id);

        // Forcer la mise à jour de Code_partenaire
        $ligne->Code_partenaire = $request->input('Code_partenaire', $ligne->Code_partenaire);
        $ligne->save();

        Log::info('Mise à jour Code_partenaire', ['ancien' => $ligne->getOriginal('Code_partenaire'), 'nouveau' => $ligne->Code_partenaire]);
        // 3. Données à mettre à jour
        $updateData = [
            'Reference_Facture' => $validatedData['Reference_Facture'],
            'Mois_Facture' => $validatedData['Mois_Facture'],
            'Date_Debut' => Carbon::parse($validatedData['Date_Debut']),
            'Date_Fin' => Carbon::parse($validatedData['Date_Fin']),
            'Montant_Ligne' => $validatedData['Montant_Ligne'],
            'Annee_Facture' => $validatedData['Annee_Facture'] ?? date('Y'),
            'Numero_Reception' => $validatedData['Numero_Reception'] ?? null,
            'is_evac' => $request->has('is_evac') ? 1 : 0,
        ];

        if (isset($validatedData['Nom_Assure'])) {
            $updateData['Nom_Assure'] = $validatedData['Nom_Assure'];
        }

        if (isset($validatedData['Code_partenaire'])) {
            $updateData['Code_partenaire'] = $validatedData['Code_partenaire'];
        }

        foreach (['Date_Reception', 'Date_Enregistrement', 'datetransMedecin'] as $dateField) {
            if (!empty($validatedData[$dateField] ?? null)) {
                $updateData[$dateField] = $validatedData[$dateField];
            }
        }

        // 4. Update
        $ligne->update($updateData);

        // 5. Réponse
        return response()->json([
            'success' => true,
            'message' => 'Facture modifiée avec succès',
            'data' => [
                'id' => $ligne->Id_Ligne,
                'reference' => $ligne->Reference_Facture,
                'montant' => number_format($ligne->Montant_Ligne, 0, ',', ' ') . ' FCFA',
            ]
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreurs de validation',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        \Log::error('Erreur modification facture', [
            'ligne_id' => $id,
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Une erreur est survenue lors de la modification'
        ], 500);
    }
}


/**
 * Vérifier si l'utilisateur peut éditer la facture
 */
private function canEditFacture($ligne, $user)
{
    // Rôles autorisés (adapte selon tes besoins)
    $allowedRoles = ['admin'];  // Supérieurs (adapte selon tes rôles)

    // Vérifier le rôle de l'utilisateur (utilise la colonne 'profil' comme dans ta session)
    $userRole = $user->profil ?? $user->role;  // Adaptez si la colonne s'appelle 'role'

    // Supérieur peut toujours modifier
    if (in_array($userRole, $allowedRoles)) {
        return true;
    }

    // Si la facture est transmise (ex. Statut_Ligne > 0), l'auteur ne peut plus modifier
    if ($ligne->Statut_Ligne == 5) {  // Adaptez la condition selon ta logique de transmission
        return false;  // Seuls les supérieurs peuvent modifier
    }

    // Si non transmise, le rédacteur original peut éditer
    if (is_numeric($ligne->Redacteur) && $ligne->Redacteur == $user->id) {
        return true;
    }

    return false;
}




    // Route AJAX pour charger le modal
    public function loadEditModal(Request $request)
    {
        if ($request->ajax()) {
            return $this->editModal($request);
        }

        return redirect()->back()->with('error', 'Accès non autorisé');
    }



public function traiter(Request $request, $id)
{
    $request->validate([
        'numero_demande' => 'required|string',
        'date_demande'   => 'required|date',
        'montant_regle'  => 'required|numeric|min:1',
    ]);

    $ligne = LigneSuivi::findOrFail($id);

    if ($ligne->Statut_Ligne != 6) {
        return back()->with('error', 'La facture doit avoir reçu un retour du médecin.');
    }

    DB::update(
        "UPDATE Ligne_Suivi
         SET Numero_demande    = ?,
             Date_Demande      = ?,
             Montant_Reglement = ?,
             Statut_Ligne      = ?
         WHERE Id_Ligne = ?",
        [
            $request->numero_demande,              // nvarchar ✔
            $request->date_demande ,  // yyyy-mm-dd ✔
            $request->montant_regle,
            1,
            $id
        ]
    );

    $this->notificationService
         ->notifierChangementStatut($ligne, 6, 1);

    return back()->with('success', 'Facture traitée avec succès');
}


    /**
     * ÉTAPE 4 : Transmission à la trésorerie (1 → 2)
     * Condition : Doit être traitée (Statut_Ligne = 1)
     */


public function transmettreALaTreso(Request $request)
{
    $request->validate([
        'factures' => 'required|string',
        'date_transmission' => 'required|date',
    ]);

    try {
        $ids = explode(',', $request->factures);
        //$dateTransmission = $request->date_transmission;
         $dateTransmission = Carbon::parse($request->date_transmission);
        $user = auth()->user();

        DB::beginTransaction();

        $updated = LigneSuivi::whereIn('Id_Ligne', $ids)
            ->where('Statut_Ligne', 1)
            ->where('rejete', '!=', 1)
            ->update([
                'Date_Transmission' => $dateTransmission,  // Maintenant une chaîne formatée
                'usertransmi' => $user->name,
                'Statut_Ligne' => 2, // Transmise à la trésorerie
            ]);

        DB::commit();

        Log::info('Transmission à la trésorerie', [
            'user_id' => $user->id,
            'factures' => $ids,
            'updated' => $updated
        ]);

        // Générer l'Excel avec les factures transmises
        $dateDebut = Carbon::parse($request->date_transmission)->format('d/m/Y');  // Assurer un formatage cohérent
        $dateFin = $dateDebut;  // Ou ajuster si vous avez une vraie date de fin

        return Excel::download(new BordereauTresoExport($ids, $dateDebut, $dateFin), 'bordereau_factures.xlsx');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Erreur transmission tréso', ['error' => $e->getMessage()]);
        return redirect()->back()->with('error', 'Erreur : ' . $e->getMessage());
    }
}

    /**
     * ÉTAPE 5 : Régler la facture (2 → 3)
     * Condition : Doit être transmise à la tréso (Statut_Ligne = 2)
     */
    public function regler(Request $request, $id)
    {
        $request->validate([
            'numero_cheque' => 'required|string|max:50',
        ]);

        try {
            $ligne = LigneSuivi::findOrFail($id);



            $ligne->Numero_Cheque = $request->numero_cheque;
            $ligne->userSaisieReg = auth()->user()->name;
            $ligne->datesaisiereg = Carbon::now();
            $ligne->Statut_Ligne = 3;
            $ligne->save();

            Log::info('Facture réglée', [
                'user_id' => auth()->id(),
                'facture_id' => $id,
                'numero_cheque' => $request->numero_cheque
            ]);

            return redirect()->back()->with('success', 'Règlement enregistré avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur règlement', [
                'facture_id' => $id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * ÉTAPE 6 : Clôturer la facture (3 → 4)
     * Condition : Doit être réglée (Statut_Ligne = 3)
     */
    public function cloturer(Request $request, $id)
    {
        try {
            $ligne = LigneSuivi::findOrFail($id);



            $ligne->Statut_Ligne = 4; // Clôturée
              $dateClotureFacture = Carbon::now(); // Date actuelle
        $ligne->Date_Cloture = $dateClotureFacture;
            $ligne->userClotureFacture = auth()->user()->name;
            $ligne->save();

            Log::info('Facture clôturée', [
                'user_id' => auth()->id(),
                'facture_id' => $id
            ]);

            return redirect()->back()->with('success', 'Facture clôturée avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur clôture', [
                'facture_id' => $id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Rejeter une facture (à tout moment sauf si déjà clôturée)
     */
  public function rejeter(Request $request, $id)
{
    Log::info('ID reçu dans rejeter', ['id' => $id, 'request_id' => $request->input('id')]);
    try {
        // Validation des données
        $request->validate([
            'motif' => 'required|string|max:500'
        ]);

        $ligne = LigneSuivi::findOrFail($id);

        // Log avant mise à jour
        Log::info('Tentative de rejet', [
            'facture_id' => $id,
            'ancien_rejete' => $ligne->rejete,
            'user_id' => auth()->id()
        ]);

        // Mise à jour avec update() au lieu de set/save
        $updated = $ligne->update([
            'rejete' => 1,
            'motif_rejet' => $request->motif,
            'date_rejet' => Carbon::now(),
            'userRejet' => auth()->user()->name,  // Note : c'est 'userRejet' dans votre DB, pas 'userrejet'
        ]);

        // Vérifier si la mise à jour a réussi
        if (!$updated) {
            throw new \Exception('Échec de la mise à jour en base');
        }

        // Recharger la ligne pour vérifier
        $ligne->refresh();

        Log::info('Facture rejetée avec succès', [
            'facture_id' => $id,
            'nouveau_rejete' => $ligne->rejete,
            'motif' => $request->motif,
            'user_id' => auth()->id()
        ]);

        return redirect()->back()->with('success', 'Facture rejetée avec succès');

    } catch (\Exception $e) {
        Log::error('Erreur rejet', [
            'facture_id' => $id,
            'request_data' => $request->all(),
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return redirect()->back()->with('error', 'Erreur lors du rejet : ' . $e->getMessage());
    }
}


 public function indexTransmission()
    {
     $user = auth()->user();
        $codeProfil = $user->profil->code_profil ?? null;
        // Utilisation des codes de profil
        $isIndividuel = in_array($codeProfil, ['RSI']); // Régleur Sinistre Individuel
        $isTiersPayant = in_array($codeProfil, ['RSTP']); // Régleur Sinistre Tiers Payant
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

        return view('transmission-factures', compact('factures', 'isAdmin', 'isIndividuel'));
    }

    /**
     * Transmission multiple (statut 0 → 5)
     */
    public function transmitBatch(Request $request)
    {
        $request->validate([
            'factures' => 'required|string',
            'date_action' => 'required|date',
        ], [
            'factures.required' => 'Aucune facture sélectionnée',
            'date_action.required' => 'La date de transmission est obligatoire',
            'date_action.date' => 'Format de date invalide'
        ]);

        try {
            $factureIds = explode(',', $request->factures);
            $dateTransmission = Carbon::parse($request->date_action);
            $user = auth()->user();

            DB::beginTransaction();

            // Vérifier que toutes les factures existent et ont le bon statut
            $facturesToUpdate = LigneSuivi::whereIn('Id_Ligne', $factureIds)
                ->where('Statut_Ligne', 0) // Uniquement les factures validées
                ->get();

            if ($facturesToUpdate->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune facture valide à transmettre (vérifiez les statuts)'
                ], 400);
            }

            // Mise à jour
            $updated = LigneSuivi::whereIn('Id_Ligne', $factureIds)
                ->where('Statut_Ligne', 0)
                ->update([
                    'datetransMedecin' => $dateTransmission,
                    'usertransMedecin' => $user->name,
                    'Statut_Ligne' => 5, // Transmise au médecin

                ]);

            DB::commit();

            Log::info('Transmission batch effectuée', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'factures_ids' => $factureIds,
                'date_transmission' => $dateTransmission->format('Y-m-d'),
                'updated_count' => $updated
            ]);

          $facturesTransmises = LigneSuivi::whereIn('Id_Ligne', $factureIds)
                ->where('Statut_Ligne', 5)
                ->get();

            foreach ($facturesTransmises as $facture) {
                $this->notificationService->notifierChangementStatut($facture, 0, 5);
            }

            return response()->json([
                'success' => true,
                'message' => "{$updated} facture(s) transmise(s) au médecin avec succès"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de la transmission batch', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la transmission : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retour multiple (statut 5 → 6)
     */
    public function retourBatch(Request $request)
    {
        $request->validate([
            'factures' => 'required|string',
            'date_action' => 'required|date',
        ], [
            'factures.required' => 'Aucune facture sélectionnée',
            'date_action.required' => 'La date de retour est obligatoire',
            'date_action.date' => 'Format de date invalide'
        ]);

        try {
            $factureIds = explode(',', $request->factures);
            $dateRetour = Carbon::parse($request->date_action);
            $user = auth()->user();

            DB::beginTransaction();

            // Vérifier que toutes les factures existent et ont le bon statut
            $facturesToUpdate = LigneSuivi::whereIn('Id_Ligne', $factureIds)
                ->where('Statut_Ligne', 5) // Uniquement les factures transmises
                ->get();

            if ($facturesToUpdate->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune facture valide pour retour (vérifiez qu\'elles sont bien transmises)'
                ], 400);
            }

            // Mise à jour
            $updated = LigneSuivi::whereIn('Id_Ligne', $factureIds)
                ->where('Statut_Ligne', 5)
                ->update([
                    'dateRetourMedecin' => $dateRetour,
                    'usertransRMedecin' => $user->name,
                    'Statut_Ligne' => 6, // Retour reçu

                ]);

            DB::commit();

            Log::info('Retour batch enregistré', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'factures_ids' => $factureIds,
                'date_retour' => $dateRetour->format('Y-m-d'),
                'updated_count' => $updated
            ]);

                 $facturesRetournees = LigneSuivi::whereIn('Id_Ligne', $factureIds)
                ->where('Statut_Ligne', 6)
                ->get();

            foreach ($facturesRetournees as $facture) {
                $this->notificationService->notifierChangementStatut($facture, 5, 6);
            }

            return response()->json([
                'success' => true,
                'message' => "{$updated} retour(s) enregistré(s) avec succès"
            ]);


        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de l\'enregistrement des retours', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement : ' . $e->getMessage()
            ], 500);
        }
    }




public function detailReseau(Request $request)
{
    \Log::info('=== FONCTION APPELÉE ===', [
        'url' => request()->fullUrl(),
        'params' => $request->all()
    ]);

    try {
        $annee = $request->get('annee', Carbon::now()->year);
        $annee = (int) $annee;
        $reseau = $request->get('reseau', 'pharmacies');

        // LOG 1: Params (force visible)
        \Log::info('DEBUG detailReseau - Params reçus', ['reseau' => $reseau, 'annee' => $annee]);

        // Normalize réseau (tolère singulier/majuscules)
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
                'conditions' => ['ls.Code_partenaire' => 'IS NOT NULL', 'p.type' => '= prestataire'], // Corrigé : Code_partenaire et p.type
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
                'conditions' => ['ls.Code_partenaire' => 'IS NULL', 'ls.is_evac' => '= 1'], // Corrigé : Code_partenaire
                'useRejete' => false,
                'titre' => 'Évacuations Sanitaires',
                'icone' => 'fa-ambulance',
            ],
            'individuels' => [
                'categorie' => null,
                'type' => null,
                'exclusions' => [],
                'inclusionsOnly' => [],
                'conditions' => ['ls.Code_partenaire' => 'IS NULL', 'ls.is_evac' => '= 0', 'ls.rejete' => '= 0'], // Corrigé : Code_partenaire
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

        $commonWhere = function ($query) use ($annee, $categorie, $type, $exclusions, $inclusionsOnly, $conditions, $reseau) {
            $query->where('ls.Annee_Facture', $annee);

            // Pour individuels, pas de filtre sur Code_partenaire (ils n'ont pas de partenaire)
            if (!in_array($reseau, ['evacuations', 'individuels'])) {
                // Pas de whereNull ici, car la logique est gérée dans conditions
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

        $applyCommonWhere = function ($query) use ($commonWhere) {
            $commonWhere($query);
            return $query;
        };

        // LOG 3: Count total base (debug clé)
        $debugQuery = $baseQuery()->selectRaw('COUNT(*) as total_count')->applyCommonWhere();
        $debugCount = $debugQuery->first();
        \Log::info('DEBUG detailReseau - Count total après filtres', [
            'reseau' => $reseau, 'annee' => $annee, 'count' => $debugCount->total_count ?? 0,
            'sql' => $debugQuery->toSql(), 'bindings' => $debugQuery->getBindings()
        ]);

        // LIGNE 1
        $nonTraitesQuery = $baseQuery()->selectRaw('COUNT(ls.Id_Ligne) as nbre_inst, ISNULL(SUM(ls.Montant_Ligne), 0) as total_inst')
            ->whereNull('ls.Numero_demande')->applyCommonWhere();
        $nonTraites = $nonTraitesQuery->first() ?? (object)['nbre_inst' => 0, 'total_inst' => 0];
        \Log::info('DEBUG - Non-traités', ['nbre' => $nonTraites->nbre_inst, 'total' => $nonTraites->total_inst]);

        $demandeQuery = $baseQuery()->selectRaw('
            COUNT(ls.Id_Ligne) as nbre_traite,
            ISNULL(SUM(ls.Montant_Reglement), 0) as total_demande,
            ISNULL(SUM(ls.Montant_Ligne), 0) as total_all,
            CASE WHEN ISNULL(SUM(ls.Montant_Ligne), 0) = 0 THEN 0 ELSE ROUND((ISNULL(SUM(ls.Montant_Reglement), 0) / NULLIF(ISNULL(SUM(ls.Montant_Ligne), 0), 0)) * 100, 2) END as taux_reglement
        ')
            ->whereNotNull('ls.Numero_demande')->applyCommonWhere();
        $demande = $demandeQuery->first() ?? (object)['nbre_traite' => 0, 'total_demande' => 0, 'total_all' => 0, 'taux_reglement' => 0];
        \Log::info('DEBUG - Demande', ['nbre' => $demande->nbre_traite, 'total_all' => $demande->total_all, 'taux' => $demande->taux_reglement]);

        $regleQuery = $baseQuery()->selectRaw('COUNT(ls.Id_Ligne) as nbre_regle, ISNULL(SUM(ls.Montant_Reglement), 0) as total_regle')
            ->whereNotNull('ls.Numero_Cheque')->applyCommonWhere();
        $regle = $regleQuery->first() ?? (object)['nbre_regle' => 0, 'total_regle' => 0];
        \Log::info('DEBUG - Réglé', ['nbre' => $regle->nbre_regle, 'total' => $regle->total_regle]);

        $tauxRegle = ($demande->total_all ?? 0) > 0 ? round(($demande->total_demande / $demande->total_all) * 100, 2) : 0;

        // LIGNE 2
        $instanceQuery = $baseQuery()->whereNull('ls.Numero_Demande')->applyCommonWhere();
        $instance = $instanceQuery->count('ls.Id_Ligne');
        \Log::info('DEBUG - Instance', ['count' => $instance]);

        $traitees = $demande->nbre_traite ?? 0;

        $tresorQuery = $baseQuery()->whereNotNull('ls.Numero_Demande')->whereNotNull('ls.Date_Transmission')->applyCommonWhere();
        $tresor = $tresorQuery->count('ls.Id_Ligne');
        \Log::info('DEBUG - Trésorerie', ['count' => $tresor]);

        $reglees = $regle->nbre_regle ?? 0;

        $soldeesQuery = $baseQuery()->whereNotNull('ls.Date_Cloture')->applyCommonWhere();
        $soldees = $soldeesQuery->count('ls.Id_Ligne');
        \Log::info('DEBUG - Soldées', ['count' => $soldees]);

        $rejetsQuery = $baseQuery()->selectRaw('COUNT(ls.Id_Ligne) as nbre_lg, ISNULL(SUM(ls.Montant_Ligne), 0) as total_rejet')
            ->where('ls.rejete', 1)->applyCommonWhere();
        $rejets = $rejetsQuery->first() ?? (object)['nbre_lg' => 0, 'total_rejet' => 0];
        \Log::info('DEBUG - Rejets', ['nbre' => $rejets->nbre_lg, 'total' => $rejets->total_rejet]);

        $totalFacture = $demande->total_all ?? 0;
        $totalRegle = $regle->total_regle ?? 0;
        $totalGlobal = $totalFacture + ($nonTraites->total_inst ?? 0) + $totalRegle;

        // LIGNE 3 : Mensuel optimisé (1 query GROUP BY)
        $mensuelQuery = $baseQuery()
            ->selectRaw('
                ls.Mois_Facture,
                ISNULL(SUM(' . ($useRejete ? 'CASE WHEN ls.rejete = 0 THEN ls.Montant_Ligne ELSE 0 END' : 'ls.Montant_Ligne') . '), 0) as total_facture,
                ISNULL(SUM(' . ($useRejete ? 'CASE WHEN ls.rejete = 0 THEN ls.Montant_Reglement ELSE 0 END' : 'ls.Montant_Reglement') . '), 0) as total_regle,
                ISNULL(SUM(CASE WHEN ls.Numero_demande IS NOT NULL ' . ($useRejete ? 'AND ls.rejete = 0 ' : '') . 'THEN (ls.Montant_Ligne - ls.Montant_Reglement) ELSE 0 END), 0) as total_ecart
            ')
            ->groupBy('ls.Mois_Facture')
            ->applyCommonWhere();
        $mensuelResults = $mensuelQuery->get();
        \Log::info('DEBUG detailReseau - Mensuel Query', [
            'reseau' => $reseau, 'sql' => $mensuelQuery->toSql(), 'bindings' => $mensuelQuery->getBindings(),
            'results_count' => $mensuelResults->count(), 'sample_row' => $mensuelResults->first() ? $mensuelResults->first()->toArray() : null
        ]);

        // Remplissage tabs (avec fallback 0)
        $tabMoisFacture = array_fill(1, 12, 0);
        $tabMoisRegle = array_fill(1, 12, 0);
        $tabMoisEcart = array_fill(1, 12, 0);
        $tabSoldes = array_fill(1, 12, 0);

        foreach ($mensuelResults as $row) {
            $i = (int) $row->Mois_Facture;
            if ($i >= 1 && $i <= 12) {
                $tabMoisFacture[$i] = $row->total_facture;
                $tabMoisRegle[$i] = $row->total_regle;
                $tabMoisEcart[$i] = $row->total_ecart;
                $tabSoldes[$i] = $tabMoisFacture[$i] - $tabMoisRegle[$i];
            }
        }
        \Log::info('DEBUG detailReseau - Tabs mensuels (ex. Octobre)', [
            'facture_oct' => $tabMoisFacture[10] ?? 0,
            'regle_oct' => $tabMoisRegle[10] ?? 0,
            'ecart_oct' => $tabMoisEcart[10] ?? 0,
            'solde_oct' => $tabSoldes[10] ?? 0
        ]);

        $totalFacture = $demande->total_all ?? 0;
        $totalRegle = $regle->total_regle ?? 0;
        $totalGlobal = $totalFacture + ($nonTraites->total_inst ?? 0) + $totalRegle;

        // Mapping variables pour vue (Ligne 2 cards)
        $instance = $instance;  // En instance (déjà calculé)
        $traitees = $demande->nbre_traite ?? 0;
        $tresor = $tresor;
        $reglees = $reglees;
        $soldees = $soldees;

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
            'mensuel_mois_non_zero' => array_keys(array_filter($tabMoisFacture, fn($v) => $v > 0)),
            'useRejete' => $useRejete
        ]);

        // Juste avant : return view('pages.detail-reseau', compact(...
        dd([
            'reseau' => $reseau,
            'count_total' => $debugCount->total_count ?? 0,
            'nonTraites' => [
                'nbre' => $nonTraites->nbre_inst ?? 0,
                'total' => $nonTraites->total_inst ?? 0
            ],
            'demande' => [
                'nbre' => $demande->nbre_traite ?? 0,
                'total_all' => $demande->total_all ?? 0,
                'total_demande' => $demande->total_demande ?? 0
            ],
            'regle' => [
                'nbre' => $regle->nbre_regle ?? 0,
                'total' => $regle->total_regle ?? 0
            ],
            'instance' => $instance,
            'traitees' => $traitees,
            'tresor' => $tresor,
            'soldees' => $soldees,
            'rejets' => [
                'nbre' => $rejets->nbre_lg ?? 0,
                'total' => $rejets->total_rejet ?? 0
            ],
            'totalGlobal' => $totalGlobal,
            'mois_octobre' => [
                'facture' => $tabMoisFacture[10] ?? 0,
                'regle' => $tabMoisRegle[10] ?? 0,
                'solde' => $tabSoldes[10] ?? 0
            ]
        ]);

        return view('pages.detail-reseau', compact(
            'annee', 'annees', 'reseau', 'titreReseau', 'iconeReseau',
            'nonTraites', 'demande', 'regle', 'tauxRegle',
            'instance', 'traitees', 'tresor', 'reglees', 'soldees', 'rejets',
            'totalFacture', 'totalRegle', 'totalGlobal',
            'tabMoisFacture', 'tabMoisRegle', 'tabMoisEcart', 'tabSoldes',
            'moisAnnee'
        ));

    } catch (\Exception $e) {
        // Log erreur détaillée (avec trace pour debug)
        \Log::error('ERREUR detailReseau - Exception capturée', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'reseau' => $reseau ?? 'N/A',
            'annee' => $annee ?? 'N/A'
        ]);
        return redirect()->back()->with('error', 'Erreur lors du chargement des détails réseau : ' . $e->getMessage());
    }
}

// ==========================================
// SOLUTION FINALE : MÉTHODE UPDATECORRECTION
// ==========================================

public function updateCorrection(Request $request)
{
 $request->validate([
 'id' => 'required|exists:Ligne_Suivi,Id_Ligne',
 'MontantF' => 'required|numeric|min:0',
 'montantReglement' => 'required|numeric|min:0',
 'motifcorretion' => 'nullable|string|max:500',
 ]);

 try {
 $user = auth()->user();

 // Vérification des permissions
 if (!in_array($user->profil->code_profil ?? null, ['RRSI', 'RRSTP', 'ADMIN'])) {
  return response()->json([
  'success' => false,
  'message' => 'Accès refusé.'
  ], 403);
 }

 // ✅ SOLUTION : Utiliser CONVERT dans une requête SQL brute
 $sql = "UPDATE [Ligne_Suivi]
  SET [Montant_Ligne] = ?,
   [Montant_Reglement] = ?,
   [montrejete] = ?,
   [usercorretion] = ?,
   [datecorretion] = CONVERT(DATETIME, ?, 120),
   [motifcorretion] = ?
  WHERE [Id_Ligne] = ?";

 $updated = DB::update($sql, [
  (float) $request->MontantF,
  (float) $request->montantReglement,
  (float) ($request->MontantF - $request->montantReglement),
  $user->name,
  Carbon::now()->format('Y-m-d H:i:s'), // Format sans millisecondes
  $request->motifcorretion,
  $request->id
 ]);

 if (!$updated) {
  throw new \Exception('Aucune ligne mise à jour');
 }

 Log::info('Correction de facture effectuée', [
  'user_id' => $user->id,
  'facture_id' => $request->id,
  'nouveau_montant' => $request->MontantF,
 ]);


        return redirect()->back()->with('success', 'Correction enregistrée avec succès.');

 } catch (\Exception $e) {
 Log::error('Erreur correction facture', [
  'error' => $e->getMessage(),
  'facture_id' => $request->id,
  'trace' => $e->getTraceAsString(),
 ]);

 return response()->json([
  'success' => false,
  'message' => 'Erreur lors de la correction : ' . $e->getMessage(),
 ], 500);
 }
}

// ==========================================
// MÊME SOLUTION POUR UPDATEANNULATION
// ==========================================

public function updateAnnulation(Request $request)
{
 $request->validate([
 'id' => 'required|exists:Ligne_Suivi,Id_Ligne',
 'motifcorretion' => 'required|string|max:500',
 ]);

 try {
 $user = auth()->user();

 if (!in_array($user->profil->code_profil ?? null, ['RRSI', 'RRSTP'])) {
  return response()->json([
  'success' => false,
  'message' => 'Accès refusé.'
  ], 403);
 }

 // ✅ Utiliser CONVERT dans SQL brut
 $sql = "UPDATE [Ligne_Suivi]
  SET [Statut_Ligne] = ?,
   [usercorretion] = ?,
   [datecorretion] = CONVERT(DATETIME, ?, 120),
   [motifcorretion] = ?
  WHERE [Id_Ligne] = ?";

 $updated = DB::update($sql, [
  8, // Statut annulé
  $user->name,
  Carbon::now()->format('Y-m-d H:i:s'),
  $request->motifcorretion,
  $request->id
 ]);

 if (!$updated) {
  throw new \Exception('Aucune ligne mise à jour');
 }

 Log::info('Annulation de facture effectuée', [
  'user_id' => $user->id,
  'facture_id' => $request->id,
  'motif' => $request->motifcorretion,
 ]);


        return redirect()->back()->with('success', 'Annulation enregistrée avec succès.');

 } catch (\Exception $e) {
 Log::error('Erreur annulation facture', [
  'error' => $e->getMessage(),
  'facture_id' => $request->id,
 ]);

 return response()->json([
  'success' => false,
  'message' => 'Erreur lors de l\'annulation : ' . $e->getMessage(),
 ], 500);
 }
}

}
