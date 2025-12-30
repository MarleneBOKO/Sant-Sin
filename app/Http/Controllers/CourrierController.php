<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\LigneSuivi;
use App\Models\Courier;

class CourrierController extends Controller
{
  /**
  * Affiche le formulaire modal pour saisir une facture à partir d'un courrier.
  *
  * @param Request $request
  * @param string $numCour Numéro du courrier
  * @return \Illuminate\Http\Response
  */
  public function saisieModal(Request $request, $numCour)
  {
    // ✅ Logs détaillés pour debugging
    Log::info('📥 saisieModal appelée', [
      'numCour' => $numCour,
      'params' => $request->all(),
      'url' => $request->fullUrl()
    ]);

    try {
      // Récupération des paramètres avec valeurs par défaut
      $expe = $request->input('expe', '');
      $objet = $request->input('objet', '');
      $annee = $request->input('annee', now()->year);
      $codecour = $request->input('codecour', '');

      Log::info('🔍 Recherche courrier avec critères', [
        'numCour' => $numCour,
        'annee' => $annee,
        'codecour' => $codecour
      ]);

      // ✅ CORRECTION : Récupération du courrier avec gestion d'erreur
      $courrier = DB::connection('sqlsrv')
        ->table('courrier as c')
        ->select('c.*', DB::raw("convert(varchar, c.DateRecep, 103) as date_DateRecep"))
        ->where('c.NumCour', $numCour)
        ->where('c.annee', $annee)
        ->where('c.CodeCour', $codecour)
        ->first();

      // Vérification si le courrier existe
      if (!$courrier) {
        Log::error('❌ Courrier non trouvé', [
          'numCour' => $numCour,
          'annee' => $annee,
          'codecour' => $codecour
        ]);

        return response()->view('errors.courrier-not-found', [
          'message' => 'Le courrier demandé est introuvable.',
          'numCour' => $numCour
        ], 404);
      }

      Log::info('✅ Courrier trouvé', ['courrier' => $courrier]);

      // Vérification des lignes déjà saisies
      $lignefac = DB::connection('sqlsrv')
        ->table('Ligne_Suivi')
        ->selectRaw('COUNT(*) as nr, numero_reception, nbfacture')
        ->where('numero_reception', $numCour)
        ->where('codecour', $codecour)
        ->groupBy('numero_reception', 'nbfacture')
        ->havingRaw('COUNT(*) <> nbfacture')
        ->first();

      Log::info('📊 Ligne facture', ['lignefac' => $lignefac]);

      // Récupération du profil utilisateur
      $profil = session('Profil', 7);

      // Récupération des prestataires/souscripteurs selon le profil
// Profil utilisateur
$profil = session('Profil', 7);

// Détermination du type à afficher
// Profil utilisateur
$profil = session('Profil', 7);

// Type de partenaire à afficher
$typeAffiche = ($profil == 7) ? 'souscripteur' : 'prestataire';

// Récupération des partenaires
$prestataires = DB::connection('sqlsrv')
    ->table('partenaires as p')
    ->leftJoin(
        'type_prestataires as tp',
        'tp.code_type_prestataire',
        '=',
        'p.code_type_prestataire'
    )
    ->select(
        'p.id as Code',
        'p.nom as Libelle',
        'p.type',
        'tp.libelle_type_prestataire'
    )
    ->where('p.type', $typeAffiche)
    ->orderBy('p.nom')   // ✅ colonne EXISTANTE
    ->get();


      // Récupération des mois
   $mois = DB::connection('sqlsrv')
    ->table('parametres')                // ✅ table correcte
    ->select(
        'codtyparam as Id_mois',
        'libelleparam as libelle_mois'
    )
    ->where('typaram', 'MoisFacture')
    ->orderByDesc('codtyparam')          // ou 'Id_mois' si tu veux trier par alias
    ->get();


      // Récupération des années
      $annees = DB::connection('sqlsrv')
        ->table('ligne_suivi')
        ->selectRaw('distinct top 2 annee_facture')
        ->whereNotNull('annee_facture')
        ->where('annee_facture', '!=', now()->year)
        ->orderByDesc('annee_facture')
        ->get();

      // Calcul du nombre restant
      $nombreRestant = $lignefac ? ($lignefac->nbfacture - $lignefac->nr) : 0;

      Log::info('✅ Données préparées avec succès', [
        'prestataires_count' => $prestataires->count(),
        'mois_count' => $mois->count(),
        'annees_count' => $annees->count(),
        'nombreRestant' => $nombreRestant
      ]);

      // Retour de la vue Blade
      return view('pages.modals.saisie-factureC', compact(
        'courrier',
        'lignefac',
        'prestataires',
        'mois',
        'annees',
        'profil',
        'nombreRestant',
        'numCour'
      ));

    } catch (\Exception $e) {
      Log::error('❌ Erreur dans saisieModal', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);

      return response()->view('errors.generic', [
        'message' => 'Une erreur est survenue lors du chargement du formulaire.',
        'details' => config('app.debug') ? $e->getMessage() : null
      ], 500);
    }
  }

  /**
  * Sauvegarde la facture saisie à partir du courrier.
  *
  * @param Request $request
  * @return \Illuminate\Http\RedirectResponse
  */
public function saveFactureByCourrier(Request $request)
{
    Log::info('💾 saveFactureByCourrier appelée', ['data' => $request->all()]);

    try {
        // Validation des données
        $validated = $request->validate([
            'prest' => 'required',
            'mois' => 'required|integer',
            'an' => 'required|integer',
            'recept' => 'required|integer',
            'souscrip' => 'required|string',
            'mont' => 'nullable|numeric|min:0',
            'nb' => 'required|integer|min:1',
            'datedeb' => 'required|date',
            'datefin' => 'required|date|after_or_equal:datedeb',
            'CodeCour' => 'required|string',
            'ass' => 'nullable|string',
        ], [
            'mont.min' => 'Le montant doit être supérieur ou égal à 0.',
            'nb.min' => 'Le nombre de factures doit être au moins 1.',
            'datefin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
        ]);

        // Récupération du profil
        $profil = session('Profil', 7);

        // Préparation des données selon le profil
        $evac = $request->has('isEvac') ? 1 : 0;
        $assure = $profil == 7 ? ($request->input('ass', '')) : '';
        $prest = $profil == 7 ? '' : $request->input('prest');
        $souscripteur = $profil == 7 ? $request->input('prest') : '';

        DB::beginTransaction();

        try {
            // ✅ UTILISER ELOQUENT AU LIEU DE QUERY BUILDER
            $ligne = new LigneSuivi();

            // Assignation des valeurs
            $ligne->Reference_Facture   = $request->souscrip;
            $ligne->Mois_Facture        = (int) $request->mois;
            $ligne->Annee_Facture       = $request->an;

            // ✅ Les dates seront automatiquement castées par Eloquent
            $ligne->Date_Debut          = $request->datedeb;
            $ligne->Date_Fin            = $request->datefin;
            $ligne->Date_Enregistrement = now();

            $ligne->Redacteur           = auth()->user()->name;
            $ligne->nbfacture           = (int) $request->nb;
            $ligne->Numero_Reception    = (int) $request->recept;
            $ligne->Statut_Ligne        = 0;
            $ligne->CodeCour            = $request->CodeCour;
            $ligne->Code_Partenaire     = (int) ($profil == 7 ? $souscripteur : $prest);
            $ligne->is_evac             = $evac;
            $ligne->Nom_Assure          = $assure;

            // Si montant fourni
            if ($request->filled('mont')) {
                $ligne->Montant_Ligne = (float) $request->mont;
            }

            Log::info('📝 Sauvegarde avec Eloquent', [
                'model_data' => $ligne->toArray()
            ]);

            // ✅ SAVE AVEC ELOQUENT (respecte les casts)
            $ligne->save();

            Log::info('✅ Facture enregistrée avec succès', [
                'Id_Ligne' => $ligne->Id_Ligne
            ]);

            // ✅ Créer les notifications
            if ($ligne->Id_Ligne) {
                try {
                    $notificationService = app(\App\Services\NotificationService::class);
                    $notificationService->notifierChangementStatut($ligne, null, 0);

                    Log::info('📧 Notifications créées', [
                        'facture_id' => $ligne->Id_Ligne
                    ]);
                } catch (\Exception $notifException) {
                    Log::error('⚠️ Erreur notification (non bloquant)', [
                        'error' => $notifException->getMessage()
                    ]);
                    // Ne pas bloquer l'enregistrement si la notification échoue
                }
            }

            DB::commit();

            return redirect()
                ->route('page', [
                    'layout' => 'side-menu',
                    'theme' => 'light',
                    'pageName' => 'courrier-instance'
                ])
                ->with('success', 'Facture enregistrée avec succès.');

        } catch (\Exception $innerException) {
            DB::rollBack();
            throw $innerException;
        }

    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::warning('⚠️ Erreur de validation', ['errors' => $e->errors()]);
        return back()->withErrors($e->errors())->withInput();

    } catch (\Exception $e) {
        Log::error('❌ Erreur lors de la sauvegarde', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ]);

        return back()
            ->with('error', 'Erreur : ' . $e->getMessage())
            ->withInput();
    }
}


}





