<?php

namespace App\Http\Controllers;

use App\Models\LigneSuivi;
use App\Models\Partenaire;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Log;

class LigneSuiviController extends Controller
{
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
            ->whereNotNull('annee_facture')
            ->where('annee_facture', '<>', Carbon::now()->year)
            ->distinct()
            ->take(5)
            ->orderByDesc('annee_facture')
            ->pluck('annee_facture');

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
                'idSouscripteur' => 'required|exists:partenaires,id', // Vérifie dans partenaires (sera mappé à Code_partenaire)
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
                'Code_partenaire' => 'required|exists:partenaires,id', // Corrigé : utilise Code_partenaire directement
                'Numero_Reception' => 'required|string|max:50',
                'Reference_Facture' => 'required|string|max:50',
                'Mois_Facture' => 'required|integer|min:1|max:12',
                'Annee_Facture' => 'required|string',
                'Date_Debut' => 'required|date',
                'Date_Fin' => 'required|date|after_or_equal:Date_Debut',
                'Montant_Ligne' => 'required|numeric|min:0',
            ]);
        }

        try {
            if ($isIndividuelle) {
                // Traitement facture individuelle
                $dateDebut = Carbon::createFromFormat('Y-m-d', $request->date_debut)->startOfDay();
                $dateFin = Carbon::createFromFormat('Y-m-d', $request->date_fin)->startOfDay();
                $dateEnregistrement = Carbon::now();

                DB::table('Ligne_Suivi')->insert([
                    'Nom_Assure' => $request->assure,
                    'Code_partenaire' => (int) $request->idSouscripteur, // Mappé depuis idSouscripteur
                    'Numero_Reception' => $request->numero_reception,
                    'Reference_Facture' => $request->reference_facture,
                    'Date_Debut' => $dateDebut->format('Y-m-d\TH:i:s'),
                    'Date_Fin' => $dateFin->format('Y-m-d\TH:i:s'),
                    'Montant_Ligne' => (float) $request->montant,
                    'Date_Enregistrement' => $dateEnregistrement->format('Y-m-d\TH:i:s'),
                    'Mois_Facture' => (int) $request->mois,
                    'Annee_Facture' => $request->annee,
                    'is_evac' => $request->has('is_evac') ? 1 : 0,
                    'Statut_Ligne' => 0,
                    'rejete' => 0,
                    'Redacteur' => auth()->id(),
                ]);

                $message = 'Facture individuelle enregistrée avec succès.';

            } else {
                // Traitement facture tiers-payant
                $dateDebut = Carbon::createFromFormat('Y-m-d', $request->Date_Debut)->startOfDay();
                $dateFin = Carbon::createFromFormat('Y-m-d', $request->Date_Fin)->startOfDay();
                $dateEnregistrement = Carbon::now();

                DB::table('Ligne_Suivi')->insert([
                    'Nom_Assure' => null,
                    'Code_partenaire' => (int) $request->Code_partenaire, // Directement depuis le formulaire
                    'Numero_Reception' => $request->Numero_Reception,
                    'Reference_Facture' => $request->Reference_Facture,
                    'Date_Debut' => $dateDebut->format('Y-m-d\TH:i:s'),
                    'Date_Fin' => $dateFin->format('Y-m-d\TH:i:s'),
                    'Montant_Ligne' => (float) $request->Montant_Ligne,
                    'Date_Enregistrement' => $dateEnregistrement->format('Y-m-d\TH:i:s'),
                    'Mois_Facture' => (int) $request->Mois_Facture,
                    'Annee_Facture' => $request->Annee_Facture,
                    'is_evac' => 0,
                    'Statut_Ligne' => 0,
                    'rejete' => 0,
                    'Redacteur' => auth()->id(),
                ]);

                $message = 'Facture tiers-payant enregistrée avec succès.';
            }

            return redirect()->route('gestion-factures')
                ->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création de la facture: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    // Méthode pour afficher le modal d'édition
    public function editModal(Request $request)
    {
        $id = $request->id;
        $ligne = LigneSuivi::with(['partenaire'])->findOrFail($id); // Utilise partenaire

        // Récupérer les souscripteurs pour le select
        $souscripteurs = Partenaire::where('type', 'souscripteur')->orderBy('nom')->get();
        $prestataires = Partenaire::where('type', 'prestataire')->orderBy('nom')->get();

        return view('pages.edit_modal', compact('ligne', 'souscripteurs', 'prestataires'));
    }

    // Méthode pour mettre à jour la facture
    public function update(Request $request, $id)
    {
        try {
            // ✅ 1. Valider les données
            $validatedData = $request->validate([
                'Reference_Facture' => 'required|string|max:255',
                'Mois_Facture' => 'required|integer|min:1|max:12',
                'Date_Debut' => 'required|date',
                'Date_Fin' => 'required|date|after_or_equal:Date_Debut',
                'Montant_Ligne' => 'required|numeric|min:0',
                'Annee_Facture' => 'nullable|integer|min:2020|max:' . (date('Y') + 1),
                'Nom_Assure' => 'nullable|string|max:255',
                'Code_partenaire' => 'nullable|exists:partenaires,id', // Corrigé : utilise Code_partenaire
                'Date_Reception' => 'nullable|date',
                'Date_Enregistrement' => 'nullable|date',
                'datetransMedecin' => 'nullable|date',
                'Numero_Reception' => 'nullable|string|max:50',
                'is_evac' => 'nullable|boolean',
            ]);

            // ✅ 2. Charger la ligne à mettre à jour
            $ligne = LigneSuivi::findOrFail($id);

            // ✅ 3. Vérifier les permissions si nécessaire
            $user = auth()->user();
            if (!$this->canEditFacture($ligne, $user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les droits pour modifier cette facture'
                ], 403);
            }

            // ✅ 4. Préparer les données à mettre à jour
            $updateData = [
                'Reference_Facture' => $validatedData['Reference_Facture'],
                'Mois_Facture' => $validatedData['Mois_Facture'],
                'Date_Debut' => $validatedData['Date_Debut'],
                'Date_Fin' => $validatedData['Date_Fin'],
                'Montant_Ligne' => $validatedData['Montant_Ligne'],
                'Annee_Facture' => $validatedData['Annee_Facture'] ?? date('Y'),
                'Numero_Reception' => $validatedData['Numero_Reception'] ?? null,
                'is_evac' => $request->has('is_evac') ? 1 : 0,
                'Redacteur' => $user->id,
            ];

            // ✅ 5. Champs conditionnels
            if (isset($validatedData['Nom_Assure'])) {
                $updateData['Nom_Assure'] = $validatedData['Nom_Assure'];
            }

            if (isset($validatedData['Code_partenaire'])) {
                $updateData['Code_partenaire'] = $validatedData['Code_partenaire']; // Corrigé
            }

            // ✅ 6. Gérer les dates optionnelles
            foreach (['Date_Reception', 'Date_Enregistrement', 'datetransMedecin'] as $dateField) {
                if (!empty($validatedData[$dateField] ?? null)) {
                    $updateData[$dateField] = $validatedData[$dateField];
                }
            }

            // ✅ 7. Appliquer la mise à jour
            $ligne->update($updateData);

            // ✅ 8. Log des modifications
            \Log::info('Facture modifiée', [
                'ligne_id' => $ligne->getKey(),
                'user_id' => $user->id,
                'changes' => $ligne->getChanges()
            ]);

            // ✅ 9. Retour JSON
            return response()->json([
                'success' => true,
                'message' => 'Facture modifiée avec succès',
                'data' => [
                    'id' => $ligne->getKey(),
                    'reference' => $ligne->Reference_Facture,
                    'montant' => number_format($ligne->Montant_Ligne, 0, ',', ' ') . ' FCFA'
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreurs de validation',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la modification de facture', [
                'ligne_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la modification'
            ], 500);
        }
    }

    private function canEditFacture($ligne, $user)
    {
        $codeProfil = $user->profil->code_profil ?? null;

        // Admin peut tout modifier
        if ($codeProfil === 'ADMIN') {
            return true;
        }

        // Régleur Sinistre Individuel (RSI) ne peut modifier que les factures individuelles
        if ($codeProfil === 'RSI') {
            return !empty($ligne->Nom_Assure) && (!$ligne->partenaire || $ligne->partenaire->type === 'souscripteur');
        }

        // Autres profils peuvent modifier les factures tiers-payant
        return $ligne->partenaire && $ligne->partenaire->type === 'prestataire';
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
        'date_demande' => 'required|date',
        'montant_regle' => 'required|numeric|min:1',
    ]);

    $ligne = LigneSuivi::findOrFail($id);

    $ligne->Numero_Demande = $request->numero_demande;

    // Fix: Format date properly for SQL Server
    try {
        // Parse the date and format it specifically for SQL Server
        $dateDemande = Carbon::parse($request->date_demande);

        // Option 1: Use ISO format which SQL Server handles better
        $ligne->Date_Demande = $dateDemande->format('Y-m-d H:i:s');

        // Option 2: Alternative - use Carbon directly (recommended)
        // $ligne->Date_Demande = $dateDemande;

    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Date invalide : ' . $e->getMessage());
    }

    $ligne->Montant_Reglement = $request->montant_regle;
    $ligne->Statut_Ligne = 1;

    try {
        $ligne->save();
    } catch (\Exception $e) {
        \Log::error('Error saving ligne_suivi: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Erreur lors de la sauvegarde: ' . $e->getMessage());
    }

    return redirect()->back()->with('success', 'Facture traitée avec succès');
}


public function rejeter(Request $request, $id)
{
    $ligne = LigneSuivi::findOrFail($id);

    if ($ligne->Statut_Ligne == 3 || $ligne->Statut_Ligne == 4) {
        return redirect()->back()->with('error', 'Impossible de rejeter une facture validée ou clôturée.');
    }

    $ligne->rejete = '1';
    $ligne->motif_rejet = $request->motif ?? 'Rejet sans motif';
    $ligne->save();

    return redirect()->back()->with('success', 'Facture rejetée avec succès');
}


public function cloturer(Request $request, $id)
{
    $ligne = LigneSuivi::findOrFail($id);
    $ligne->Statut_Ligne = 4;
    $ligne->save();

return redirect()->back()->with('success', 'Facture cloturer avec succès');
}

public function valider(Request $request)
{
    $request->validate([
        'factures' => 'required|string',
        'date_transmission' => 'required|date',
    ]);

    $ids = explode(',', $request->factures);
    $dateTransmission = Carbon::parse($request->date_transmission);

    LigneSuivi::whereIn('Id_Ligne', $ids)
        ->where('Statut_Ligne', '!=', 4)  // exclure clôturées
        ->where('rejete', '!=', 1)        // exclure rejetées
        ->update([
            'Date_Transmission' => $dateTransmission,
            'Statut_Ligne' => 3,          // mettre à jour le statut à 3
        ]);

    return redirect()->back()
        ->with('success', 'Date de transmission mise à jour pour les factures sélectionnées.');
}





public function regler(Request $request, $id)
{
    $request->validate([
        'numero_cheque' => 'required|string|max:50',
        // autres validations si besoin
    ]);

    $ligne = LigneSuivi::findOrFail($id);

    // Mettre à jour les champs liés au règlement
    $ligne->Numero_Cheque = $request->input('numero_cheque');
    $ligne->userSaisieReg = auth()->user()->name;
    $ligne->datesaisiereg = Carbon::now();

    $ligne->save();
  return redirect()->back()
        ->with('success', 'Règlement enregistré avec succès.');
}




   public function handleRetour(Request $request) {
       $request->validate(['factures' => 'required|string', 'date_retour' => 'required|date']);
       $ids = explode(',', $request->factures);
       $d = Carbon::parse($request->date_retour);
       $usersaisie = auth()->user();
       $date_enreg = Carbon::now();
       $updated = LigneSuivi::whereIn('Id_Ligne', $ids)->where('Statut_Ligne', 5)->update([
           'Statut_Ligne' => 6, 'dateRetourMedecin' => $d, 'dateEnregRMedecin' => $date_enreg, 'usertransRMedecin' => $usersaisie
       ]);
       return response()->json(['success' => $updated > 0, 'message' => $updated > 0 ? "$updated retour(s) enregistré(s) (status 6)" : 'Erreur']);
   }

    /**
     * Charge le modal pour transmission individuelle (AJAX).
     */
    public function loadTransmissionModal(Request $request, $id = null)
    {
        if (!$request->ajax()) {
            return redirect()->back()->with('error', 'Accès non autorisé.');
        }

        $ligne = LigneSuivi::with(['souscripteur', 'prestataire'])->findOrFail($id);
        if ($ligne->Statut_Ligne != 3) {
            return response()->json(['success' => false, 'message' => 'Facture non prête à transmettre.'], 400);
        }

        return view('pages.modals.transmission_modal', compact('ligne'));
    }

    /**
     * Charge le modal pour saisie retour individuelle (AJAX).
     */
   public function loadRetourModal(Request $request, $id)
   {
       if (!$request->ajax()) {
           return redirect()->back()->with('error', 'Accès non autorisé.');
       }

       try {
           Log::info('loadRetourModal: Tentative de chargement pour ID', ['requested_id' => $id]);

           $ligne = LigneSuivi::find($id);  // Utilisez find() pour éviter les exceptions

           if (!$ligne) {
               Log::error('loadRetourModal: Ligne non trouvée pour ID', ['id' => $id]);
               return response()->view('errors.modal_error', [
                   'message' => 'Facture introuvable pour l\'ID ' . $id
               ], 404);
           }

           Log::info('loadRetourModal: Ligne chargée', [
               'ligne_id' => $ligne->Id_Ligne,  // Utilisez Id_Ligne explicitement
               'full_ligne' => $ligne->toArray(),
               'statut_ligne' => $ligne->Statut_Ligne
           ]);

           if ($ligne->Statut_Ligne != 5) {
               return response()->view('errors.modal_error', [
                   'message' => 'Cette facture n\'est pas en attente de retour'
               ], 400);
           }

           return view('pages.modals.retour_modal', compact('ligne'));

       } catch (\Exception $e) {
           Log::error('Erreur loadRetourModal', ['id' => $id, 'error' => $e->getMessage()]);
           return response()->view('errors.modal_error', ['message' => 'Erreur: ' . $e->getMessage()], 500);
       }
   }




    public function transmitBatch(Request $request)
    {
        $request->validate([
            'factures' => 'required|string|regex:/^[\d,]+$/u',
            'date_transmission' => 'required|date',
        ]);

        try {
            $ids = array_filter(explode(',', $request->factures), 'is_numeric');
            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune facture valide sélectionnée.'
                ], 400);
            }

            $dateTransmission = Carbon::parse($request->date_transmission);
                    $user = auth()->user();

            $userSaisie = auth()->user();
            $dateEnreg = Carbon::now();

            $updated = LigneSuivi::whereIn('Id_Ligne', $ids)
                ->where('Statut_Ligne', 3)
                ->where('rejete', 0)
                ->update([
                    'Statut_Ligne' => 5,
                    'datetransMedecin' => $dateTransmission,
                    'dateEnregMedecin' => $dateEnreg,
                    'usertransMedecin' => $userSaisie,
                ]);

            Log::info('Transmission Batch', [
                'user_id' => $userSaisie,
                'ids' => $ids,
                'updated_count' => $updated,
                'date' => $dateTransmission
            ]);

            return response()->json([
                'success' => $updated > 0,
                'message' => $updated > 0
                    ? "{$updated} facture(s) transmise(s)."
                    : 'Aucune facture mise à jour.'
            ]);

        } catch (\Exception $e) {
            Log::error('Transmission Batch Error', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Individual transmission (single invoice)
     */
    public function transmitIndividual(Request $request, $id)
    {
        $request->validate([
            'transmission_date' => 'required|date',
        ]);

        try {
            $ligne = LigneSuivi::findOrFail($id);

            if ($ligne->Statut_Ligne != 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette facture n\'est pas prête à être transmise.'
                ], 400);
            }

            $ligne->update([
                'Statut_Ligne' => 5,
                'datetransMedecin' => Carbon::parse($request->transmission_date),
                'dateEnregMedecin' => Carbon::now(),
                'usertransMedecin' => auth()->user(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Facture transmise avec succès.'
            ]);

        } catch (\Exception $e) {
            Log::error('Transmission Individual Error', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batch retour (multiple invoices)
     */
    public function handleRetourBatch(Request $request)
    {
        $request->validate([
            'factures' => 'required|string',
            'date_retour' => 'required|date'
        ]);

        $ids = explode(',', $request->factures);
        $dateRetour = Carbon::parse($request->date_retour);
        $userSaisie = auth()->user();
        $dateEnreg = Carbon::now();

        $updated = LigneSuivi::whereIn('Id_Ligne', $ids)
            ->where('Statut_Ligne', 5)
            ->update([
                'Statut_Ligne' => 6,
                'dateRetourMedecin' => $dateRetour,
                'dateEnregRMedecin' => $dateEnreg,
                'usertransRMedecin' => $userSaisie
            ]);

        return response()->json([
            'success' => $updated > 0,
            'message' => $updated > 0
                ? "$updated retour(s) enregistré(s)"
                : 'Aucune facture mise à jour'
        ]);
    }

    /**
     * Individual retour (single invoice)
     */
/**
 * Individual retour (single invoice)
 */
public function handleRetourIndividual(Request $request, $id)
{
    // Validation de base
    if (!is_numeric($id)) {
        Log::error('handleRetourIndividual: ID invalide', ['id' => $id]);
        return response()->json(['success' => false, 'message' => 'ID invalide'], 400);
    }

    try {
        $ligne = LigneSuivi::findOrFail($id);

        if ($ligne->Statut_Ligne != 5) {
            return response()->json([
                'success' => false,
                'message' => 'Cette facture n\'est pas en attente de retour.'
            ], 400);
        }

        // Validation des données du formulaire
        $request->validate([
            'date_retour' => 'required|date|after_or_equal:today',  // Ajustez si nécessaire
            'commentaire' => 'nullable|string|max:500',  // Exemple, ajustez selon vos champs
        ]);

        // Mise à jour de la ligne
        $ligne->update([
            'Statut_Ligne' => 6,  // Retour enregistré
            'dateRetourMedecin' => Carbon::parse($request->date_retour),
            'dateEnregRMedecin' => Carbon::now(),
            'usertransRMedecin' => auth()->user(),
            // Ajoutez d'autres champs si nécessaire, comme commentaire
        ]);

        Log::info('Retour Individual enregistré', [
            'id' => $id,
            'user_id' => auth()->user()->id,
            'date_retour' => $request->date_retour
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Retour enregistré avec succès.'
        ]);

    } catch (\Exception $e) {
        Log::error('Erreur handleRetourIndividual', ['id' => $id, 'error' => $e->getMessage()]);
        return response()->json([
            'success' => false,
            'message' => 'Erreur: ' . $e->getMessage()
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


/**
 * Display unified page for transmission and returns.
 * Fetches invoices ready for transmission (Statut_Ligne=0) AND transmitted waiting return (Statut_Ligne=3).
 */
public function transmissionRetours()
{
    $user = Auth::user();
    $profilId = $user->profil->id ?? null;
    $isAdmin = ($profilId == 5); // Admin : Tous types
    $isIndividuel = !$isAdmin && ($profilId == 7); // Individuel pur

    Log::info('TransmissionRetours Debug: Profil', [
        'profil_id' => $profilId,
        'isAdmin' => $isAdmin,
        'isIndividuel' => $isIndividuel,
        'user_id' => $user->id
    ]);

    $factures = collect();

    if ($isAdmin) {
        // Admin : Toutes (stLigne IN [3,5]), sans filtre type
        $query = DB::connection('sqlsrv')->table('Ligne_Suivi as ls')
            ->leftJoin('partenaires as p', 'ls.Code_partenaire', '=', 'p.id') // Corrigé : Code_partenaire
            ->select([
                'ls.Id_Ligne as id',
                DB::raw('COALESCE(p.nom, ls.Nom_Assure, \'N/A\') as prest'), // Corrigé : utilise p.nom directement
                'ls.Numero_Reception',
                DB::raw('COALESCE(ls.Reference_Facture, ls.Nom_Assure, \'N/A\') as ref'),
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
                    WHEN 6 THEN \'Retour Enregistré\'
                    ELSE \'Autre\' END as statut_label')
            ])
            ->where('ls.rejete', 0)
            ->whereRaw('ISNULL(ls.annuler, 0) = 0')
            ->whereIn('ls.Statut_Ligne', [3, 5]) // ✅ Validées (3) + Transmises en attente (5)
            ->whereNotNull('ls.Date_Transmission') // Seulement avec date transmission
            ->orderBy('ls.Statut_Ligne', 'asc') // 3 en premier
            ->orderBy('ls.dateRetourMedecin', 'asc'); // En attente en premier

        $factures = collect($query->get());

        Log::info('TransmissionRetours: Admin Results', [
            'count' => $factures->count(),
            'sample_ids' => $factures->pluck('id')->take(3)->toArray(),
            'statuts_found' => $factures->pluck('stLigne')->unique()->toArray() // [3,5]
        ]);

        $factures->each(function ($facture) {
            $facture->prest = $facture->prest ?? 'N/A';
            $facture->ref = $facture->ref ?? 'N/A';
            $facture->dtTransMed = $facture->dtTransMed ?? 'Non transmis';
            $facture->dtRetourMed = $facture->dtRetourMed ?? 'En attente';
        });

    } elseif ($isIndividuel) {
        // Individuel : stLigne IN [3,5]
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
        ->whereNotNull('Date_Transmission')
        ->whereNotNull('Code_partenaire') // Corrigé : Code_partenaire
        ->where('p.type', 'souscripteur') // Corrigé : utilise p.type
        ->where('is_evac', 0)
        ->orderBy('Statut_Ligne', 'asc')
        ->orderBy('dateRetourMedecin', 'asc')
        ->get();

        Log::info('TransmissionRetours: Individuels Results', [
            'count' => $factures->count(),
            'sample_ids' => $factures->pluck('id')->take(3)->toArray()
        ]);

        $factures->each(function ($facture) {
            $facture->ref = $facture->ref ?? 'N/A';
            $facture->prest = $facture->prest ?? $facture->Nom_Assure ?? 'N/A';
            $facture->dtTransMed = $facture->dtTransMed ?? 'Non transmis';
            $facture->dtRetourMed = $facture->dtRetourMed ?? 'En attente';
            $facture->type_label = 'Individuel';
        });

    } else {
        // Tiers-Payant : stLigne IN [3,5]
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
        ->whereNotNull('Date_Transmission')
        ->whereNotNull('Code_partenaire') // Corrigé : Code_partenaire
        ->where('p.type', 'prestataire') // Corrigé : utilise p.type
        ->whereRaw('(SELECT coutierG FROM partenaires WHERE id = Code_partenaire) IS NULL OR coutierG = 0') // Corrigé : Code_partenaire
        ->where('is_evac', 0)
        ->orderBy('Statut_Ligne', 'asc')
        ->orderBy('dateRetourMedecin', 'asc')
        ->get();

        Log::info('TransmissionRetours: Tiers-Payant Results', [
            'count' => $factures->count(),
            'sample_ids' => $factures->pluck('id')->take(3)->toArray()
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
        ? 'Transmission et Retour des Factures aux Médécins (ADMIN - Tous Types)'
        : ($isIndividuel
            ? 'Transmission et Retour des Factures Individuelles aux Médécins'
            : 'Transmission et Retour des Factures Tiers-Payant aux Médécins');

    Log::info('TransmissionRetours: Final', [
        'factures_count' => $factures->count(),
        'title' => $title,
        'statuts' => $factures->pluck('stLigne')->unique()->toArray() // [3,5]
    ]);

    return view('pages.transmission-facture', [ // Ajustez si vue différente
        'factures' => $factures,
        'isIndividuel' => $isIndividuel,
        'isAdmin' => $isAdmin,
        'title' => $title,
        'profil_id' => $profilId
    ]);
}


/**
 * Batch ou individuel transmission (stLigne=3 → 5, comme votre code PHP).
 */
public function transmit(Request $request)
{
    $request->validate([
        'factures' => 'required|string|regex:/^[\d,]+$/u', // Comma-separated IDs
        'date_transmission' => 'required|date|after_or_equal:today', // Prevent future dates if needed
    ]);

    try {
        $ids = array_filter(explode(',', $request->factures), 'is_numeric'); // Clean IDs
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Aucune facture valide sélectionnée.'], 400);
        }

        $dateTransmission = Carbon::parse($request->date_transmission);
        $userSaisie = auth()->user();
        $dateEnreg = Carbon::now();

        $updated = LigneSuivi::whereIn('Id_Ligne', $ids)
            ->where('Statut_Ligne', 3) // Only ready ones
            ->where('rejete', 0) // Not rejected
            ->update([
                'Statut_Ligne' => 5,
                'datetransMedecin' => $dateTransmission,
                'dateEnregMedecin' => $dateEnreg, // Fixed field name if it's dateEnregMedecin
                         'usertransMedecin' => $userSaisie,
            ]);

        Log::info('Transmission Batch', [
            'user_id' => $userSaisie,
            'ids' => $ids,
            'updated_count' => $updated,
            'date' => $dateTransmission
        ]);

        return response()->json([
            'success' => $updated > 0,
            'message' => $updated > 0 ? "{$updated} facture(s) transmise(s) (statut 5)." : 'Aucune facture mise à jour (vérifiez les statuts).'
        ]);

    } catch (\Exception $e) {
        Log::error('Transmission Error', ['error' => $e->getMessage(), 'request' => $request->all()]);
        return response()->json(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()], 500);
    }
}
}
