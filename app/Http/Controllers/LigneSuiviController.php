<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LigneSuivi;
use App\Models\Partenaire;
use Carbon\Carbon;
use DB;

class LigneSuiviController extends Controller
{
public function index()
{
    $user = auth()->user();
    $profil_id = $user->profil->id ?? null;

    $isIndividuel = in_array($profil_id, [3, 4, 5]);
    $isTiersPayant = in_array($profil_id, [3, 5, 8]);

    // Base de la requête
    $query = LigneSuivi::query()

        ->whereNotNull('Date_Enregistrement')
        ->with(['souscripteur', 'prestataire']);

    // Filtres selon le type de profil
    if ($isIndividuel && !$isTiersPayant) {
        // Assuré uniquement
        $query->whereNotNull('Nom_Assure')->whereNull('Code_Prestataire');
    } elseif ($isTiersPayant && !$isIndividuel) {
        // Prestataire uniquement
        $query->whereNotNull('Code_Prestataire');
    } elseif ($isIndividuel && $isTiersPayant) {
        // Les deux cas
        $query->where(function ($q) {
            $q->where(function ($sub) {
                $sub->whereNotNull('Nom_Assure')->whereNull('Code_Prestataire');
            })->orWhere(function ($sub) {
                $sub->whereNotNull('Code_Prestataire');
            });
        });
    }

    $factures = $query->get();

    $souscripteurs = Partenaire::souscripteurs()->orderBy('nom')->get();
    $prestataires = Partenaire::prestataires()->orderBy('nom')->get();

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
                'Code_Prestataire' => 'required|exists:partenaires,id',
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
                    'Code_Souscripteur' => (int) $request->idSouscripteur,
                    'Code_Prestataire' => null, // Pas de prestataire pour les individuelles
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
                    'Nom_Assure' => null, // Pas de nom d'assuré pour les tiers-payant
                    'Code_Souscripteur' => null, // Optionnel pour tiers-payant, ou garder si nécessaire
                    'Code_Prestataire' => (int) $request->Code_Prestataire,
                    'Numero_Reception' => $request->Numero_Reception,
                    'Reference_Facture' => $request->Reference_Facture,
                    'Date_Debut' => $dateDebut->format('Y-m-d\TH:i:s'),
                    'Date_Fin' => $dateFin->format('Y-m-d\TH:i:s'),
                    'Montant_Ligne' => (float) $request->Montant_Ligne,
                    'Date_Enregistrement' => $dateEnregistrement->format('Y-m-d\TH:i:s'),
                    'Mois_Facture' => (int) $request->Mois_Facture,
                    'Annee_Facture' => $request->Annee_Facture,
                    'is_evac' => 0, // Par défaut pour tiers-payant
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
$ligne = LigneSuivi::with(['souscripteur', 'prestataire', 'redacteur'])->findOrFail($id);

    // Récupérer les souscripteurs pour le select
    $souscripteurs = Partenaire::souscripteurs()->orderBy('nom')->get();
    $prestataires = Partenaire::prestataires()->orderBy('nom')->get();

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
            'Code_Souscripteur' => 'nullable|exists:partenaires,id',
            'Date_Reception' => 'nullable|date',
            'Date_Enregistrement' => 'nullable|date',
            'datetransMedecin' => 'nullable|date',
            'Numero_Reception' => 'nullable|string|max:50',
            'is_evac' => 'nullable|boolean',
            'Code_Prestataire' => 'nullable|exists:partenaires,id',
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

        if (isset($validatedData['Code_Souscripteur'])) {
            $updateData['Code_Souscripteur'] = $validatedData['Code_Souscripteur'];
        }

        if (isset($validatedData['Code_Prestataire'])) {
            $updateData['Code_Prestataire'] = $validatedData['Code_Prestataire'];
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


// Méthode pour vérifier les permissions (optionnelle)
private function canEditFacture($ligne, $user)
{
    // Exemple de logique de permissions
    $profil_id = $user->profil->id ?? null;

    // Super admin peut tout modifier
    if ($profil_id == 1) {
        return true;
    }

    // Les utilisateurs profil 4 ne peuvent modifier que les factures individuelles
    if ($profil_id == 4) {
        return !empty($ligne->Nom_Assure) && empty($ligne->Code_Prestataire);
    }

    // Autres profils peuvent modifier les factures tiers-payant
    return !empty($ligne->Code_Prestataire);
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

    // Parse date_demande correctement (assume format Y-m-d)
    try {
        $ligne->Date_Demande = Carbon::parse($request->date_demande)->format('Y-m-d H:i:s');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Date invalide : ' . $e->getMessage());
    }

    $ligne->Montant_Reglement = $request->montant_regle;
    $ligne->Statut_Ligne = 1;


    $ligne->save();

    return redirect()->back()->with('success', 'Facture traitée avec succès');
}


public function rejeter(Request $request, $id)
{
    $ligne = LigneSuivi::findOrFail($id);
    $ligne->rejete = '1';
    $ligne->motif_rejet = $request->motif ?? 'Rejet sans motif';
    $ligne->save();

}

public function cloturer(Request $request, $id)
{
    $ligne = LigneSuivi::findOrFail($id);
    $ligne->Statut_Ligne = 4;
    $ligne->save();


}





}
