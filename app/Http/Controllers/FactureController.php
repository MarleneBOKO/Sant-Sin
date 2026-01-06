<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FactureController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'type_facture' => 'required|in:recues,courrier,instance,etat_reglement',
            'reseau' => 'nullable|in:tt,phar,para,ind,evac,apfd',
            'statut_reglement' => 'nullable|in:ttreg,reg,nreg,annul',
            'statut_instance' => 'nullable|in:ei,tr,An,it',
            'type_courrier' => 'nullable|integer',
            'nature' => 'nullable|integer',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'DateS' => 'nullable|date',
            'finperiode' => 'nullable|date',
        ]);

        $resultats = collect();

        if ($request->filled(['type_facture', 'date_debut', 'date_fin'])) {
            $params = [
                'type_facture' => $request->type_facture,
                'reseau' => $request->reseau ?? 'tt',
                'statut_reglement' => $request->statut_reglement ?? 'ttreg',
                'statut_instance' => $request->statut_instance ?? 'ei',
                'type_courrier' => $request->type_courrier ?? 1,
                'nature' => $request->nature ?? 3,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'DateS' => $request->DateS,
                'finperiode' => $request->finperiode,
            ];

            Log::info('Paramètres reçus pour listing : ', $params);
            $resultats = $this->getResultats($params);
        }

        return view('pages.listing-reporting', [
            'layout' => 'side-menu',
            'theme' => 'light',
            'page_name' => 'listing-reporting',
            'factures' => $resultats,
            'top_menu' => [],
            'side_menu' => (new \App\Services\MenuService())->getMenusForAuthenticatedUser(),
            'simple_menu' => [],
            'first_page_name' => 'factures',
            'second_page_name' => null,
            'third_page_name' => null,
            'fakers' => [],
        ]);
    }
private function getResultats($params)
{
    $typeFacture = $params['type_facture'];
    $reseau = $params['reseau'];
    $dateDebut = $params['date_debut'];
    $dateFin = $params['date_fin'];

    switch ($typeFacture) {
        case 'recues':
            $query = $this->buildQueryRecues($reseau, $params['statut_reglement']);
            break;
        case 'courrier':
            $resultats = $this->getCourriers($params['type_courrier'], $params['nature'], $dateDebut, $dateFin);
            break;
        case 'instance':
            $query = $this->buildQueryInstance($reseau, $params['statut_instance']);
            break;
        case 'etat_reglement':
            $query = $this->buildQueryEtatReglement($reseau, $params['finperiode']);
            break;
        default:
            $resultats = collect([]);
    }

    if (isset($query)) {
        $finalQuery = "SELECT * FROM ({$query}) AS tb
                       WHERE CAST(dateenreg AS DATE) BETWEEN CAST(? AS DATE) AND CAST(? AS DATE)
                       ORDER BY dateenreg DESC";

        Log::info('Requête SQL générée : ' . substr($finalQuery, 0, 500) . '...');

        $factures = DB::connection('sqlsrv')->select($finalQuery, [$dateDebut, $dateFin]);
        $resultats = collect($factures);
        Log::info('Nombre de factures récupérées : ' . $resultats->count());
    }

    // Pagination pour tous les cas
    $perPage = $params['per_page'] ?? 50;
    $currentPage = request()->get('page', 1);
    $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
        $resultats->forPage($currentPage, $perPage)->values(),
        $resultats->count(),
        $perPage,
        $currentPage,
        ['path' => request()->url(), 'pageName' => 'page']
    );

    return $paginated;
}
    private function getCourriers($typeCourrier, $nature, $dateDebut, $dateFin)
    {
        $sql = "
            SELECT
                NumCour AS id,
                RefCour AS reference,
                Objet AS objet,
                expediteur AS expediteur,
                CONVERT(varchar, DateEnreg, 103) AS date_enregistrement,
                CONVERT(varchar, DateEdit, 103) AS date_edition,
                CONVERT(varchar, DateRecep, 103) AS date_reception,
                Nom AS nom,
                Prenom AS prenom,
                telephone,
                t.LibType AS type_courrier,
                n.LibNat AS nature_courrier,
                annee,
                usersaisie AS utilisateur_saisie
            FROM courrier c
            INNER JOIN (
                SELECT codtyparam AS CodeType, libelleparam AS LibType
                FROM parametres WHERE typaram = 'TypeCourrier'
            ) t ON c.codeType = t.CodeType
            INNER JOIN (
                SELECT codtyparam AS CodeNat, libelleparam AS LibNat
                FROM parametres WHERE typaram = 'NatureCourrier'
            ) n ON c.CodeNat = n.CodeNat
            WHERE c.DateEnreg BETWEEN ? AND ?
                AND c.codeType = ? AND c.CodeNat = ?
            ORDER BY c.DateEnreg DESC
        ";

        return collect(DB::connection('sqlsrv')->select($sql, [$dateDebut, $dateFin, $typeCourrier, $nature]));
    }

    private function buildQueryRecues($reseau, $statutReglement)
    {
        if ($reseau === 'tt') {
            return $this->buildUnionQueryForAllNetworks('recues', $statutReglement);
        }

        $baseQuery = $this->getQueryByReseau($reseau, 'recues', null);
        $conditionStatut = $this->getStatutReglementCondition($statutReglement);

        return "SELECT * FROM ({$baseQuery}) AS subquery {$conditionStatut}";
    }

    private function buildQueryInstance($reseau, $statutInstance)
    {
        if ($reseau === 'tt') {
            return $this->buildUnionQueryForAllNetworks('instance', null, $statutInstance);
        }
        return $this->getQueryByReseau($reseau, 'instance', $statutInstance);
    }

    private function buildQueryEtatReglement($reseau, $finperiode = null)
    {
        $table = $finperiode ? 'historiqueLigne_Suivi' : 'Ligne_Suivi';
        $extraCondition = $finperiode ? "AND CAST(dateSauvegarde AS DATE) = '{$finperiode}'" : '';

        if ($reseau === 'tt') {
            $queries = [];
            foreach (['phar', 'para', 'ind', 'evac', 'apfd'] as $res) {
                $queries[] = $this->getQueryEtatReglementByReseau($res, $table);
            }
            $unionQuery = implode(' UNION ', $queries);
            return "SELECT * FROM ({$unionQuery}) AS tb WHERE Numero_Cheque IS NOT NULL {$extraCondition}";
        }

        $query = $this->getQueryEtatReglementByReseau($reseau, $table);
        return "SELECT * FROM ({$query}) AS subquery WHERE Numero_Cheque IS NOT NULL {$extraCondition}";
    }

    private function getQueryEtatReglementByReseau($reseau, $table)
    {
        $baseSelect = "
            CONVERT(varchar, ls.Date_Enregistrement, 103) AS Date_Enregistrement,
            ls.Date_Enregistrement AS dateenreg,
            ls.Reference_facture,
            ls.Numero_reception,
            mois.libelleparam AS Mois_Facture,
            ls.annee_facture,
            CONVERT(varchar, ls.date_debut, 103) AS date_debut,
            CONVERT(varchar, ls.date_fin, 103) AS date_fin,
            ls.montant_ligne AS Montant_facture,
            ls.Montant_Reglement,
            CONVERT(varchar, ls.Date_Transmission, 103) AS Date_Transmission,
            ls.redacteur,
            ls.Statut_Ligne,
            ISNULL(param_statut.libelleparam, 'Non défini') AS transmission,
            CONVERT(varchar, ls.datetransMedecin, 103) AS datetransMedecin,
            CONVERT(varchar, ls.dateRetourMedecin, 103) AS RetourMedecin,
            ls.dateRetourMedecin,
            mois.codtyparam AS id_mois,
            ls.Numero_demande,
            ls.Numero_Cheque,
            ls.Date_Cloture
        ";

        $baseJoins = "
            INNER JOIN partenaires p ON ls.Code_Partenaire = p.id
            INNER JOIN (
                SELECT codtyparam, libelleparam
                FROM parametres WHERE typaram='MoisFacture'
            ) mois ON mois.codtyparam = CAST(ls.Mois_Facture AS VARCHAR)
            LEFT JOIN parametres param_statut
                ON param_statut.typaram = 'etape_Facture'
                AND CONVERT(varchar, param_statut.codtyparam) = CONVERT(varchar, ls.statut_ligne)
        ";

        switch ($reseau) {
            case 'phar':
                // Code natif: Id_Categorie=2 (Pharmacie), Code_Souscripteur IS NULL
                return "
                    SELECT DISTINCT
                        p.nom AS Tiers,
                        (CASE WHEN ls.is_evac = 0 THEN '' ELSE 'Evacuation' END) AS evacuation,
                        'Facture' AS typeF,
                        'Pharmacie' AS reseau,
                        {$baseSelect}
                    FROM {$table} ls
                    {$baseJoins}
                    INNER JOIN type_prestataires tp ON p.code_type_prestataire = tp.code_type_prestataire
                    WHERE ISNULL(ls.annuler, 0) = 0
                    AND p.type = 'prestataire'
                    AND tp.code_type_prestataire = '0'
                    AND ls.is_evac = 0
                ";

            case 'para':
                // Code natif: Id_Categorie=1 (Parapharmacie), coutierG=0, Code_Souscripteur IS NULL
                return "
                    SELECT DISTINCT
                        p.nom AS Tiers,
                        (CASE WHEN ls.is_evac = 0 THEN '' ELSE 'Evacuation' END) AS evacuation,
                        'Facture' AS typeF,
                        'Parapharmacie' AS reseau,
                        {$baseSelect}
                    FROM {$table} ls
                    {$baseJoins}
                    INNER JOIN type_prestataires tp ON p.code_type_prestataire = tp.code_type_prestataire
                    WHERE ISNULL(ls.annuler, 0) = 0
                    AND p.type = 'prestataire'
                    AND tp.code_type_prestataire = '1'
                    AND ls.is_evac = 0
                    AND p.coutierG = 0
                ";

            case 'ind':
                // Code natif: Code_Prestataire IS NULL → p.type = 'souscripteur', is_evac=0
                return "
                    SELECT DISTINCT
                        COALESCE(p.nom, ls.Nom_Assure) AS Tiers,
                        '' AS evacuation,
                        'Facture' AS typeF,
                        'Individuel' AS reseau,
                        {$baseSelect}
                    FROM {$table} ls
                    {$baseJoins}
                    WHERE ISNULL(ls.annuler, 0) = 0
                    AND p.type = 'souscripteur'
                    AND ls.is_evac = 0
                ";

            case 'evac':
                // Code natif: Code_Prestataire IS NULL → p.type = 'souscripteur', is_evac=1
                return "
                    SELECT DISTINCT
                        COALESCE(p.nom, ls.Nom_Assure) AS Tiers,
                        'Evacuation' AS evacuation,
                        'Facture' AS typeF,
                        'Evacuation' AS reseau,
                        {$baseSelect}
                    FROM {$table} ls
                    {$baseJoins}
                    WHERE ISNULL(ls.annuler, 0) = 0
                    AND p.type = 'souscripteur'
                    AND ls.is_evac = 1
                ";

            case 'apfd':
                // Code natif: Id_Categorie=1, coutierG=1, Code_Souscripteur IS NULL
                return "
                    SELECT DISTINCT
                        p.nom AS Tiers,
                        (CASE WHEN ls.is_evac = 0 THEN '' ELSE 'Evacuation' END) AS evacuation,
                        'Appel de Fond' AS typeF,
                        'Courtier Gestionnaire' AS reseau,
                        {$baseSelect}
                    FROM {$table} ls
                    {$baseJoins}
                    INNER JOIN type_prestataires tp ON p.code_type_prestataire = tp.code_type_prestataire
                    WHERE ISNULL(ls.annuler, 0) = 0
                    AND p.type = 'prestataire'
                    AND tp.code_type_prestataire = '1'
                    AND p.coutierG = 1
                ";

            default:
                return "";
        }
    }

    private function getQueryByReseau($reseau, $type, $statutInstance = null)
    {
        $instanceCondition = $statutInstance ? $this->getInstanceCondition($statutInstance) : '';
       
        $baseSelect = "
            CONVERT(varchar, ls.Date_Enregistrement, 103) AS Date_Enregistrement,
            ls.Date_Enregistrement AS dateenreg,
            CONVERT(varchar, ls.Date_Demande, 103) AS Date_Demande,
            ls.Reference_facture,
            ls.Numero_reception,
            mois.libelleparam AS Mois_Facture,
            ls.annee_facture,
            CONVERT(varchar, ls.date_debut, 103) AS date_debut,
            CONVERT(varchar, ls.date_fin, 103) AS date_fin,
            ls.montant_ligne AS Montant_facture,
            ls.Montant_Reglement,
            ISNULL(ls.montrejete, 0) AS montrejete,
            CONVERT(varchar, ls.Date_Transmission, 103) AS Date_Transmission,
            ls.redacteur,
            ls.Statut_Ligne,
            ISNULL(param_statut.libelleparam, 'Non défini') AS transmission,
            CONVERT(varchar, ls.datetransMedecin, 103) AS datetransMedecin,
            CONVERT(varchar, ls.dateRetourMedecin, 103) AS RetourMedecin,
            ls.dateRetourMedecin,
            mois.codtyparam AS id_mois,
            ls.Numero_demande,
            ls.Numero_Cheque,
            ls.Date_Cloture
        ";

        $baseJoins = "
            INNER JOIN partenaires p ON ls.Code_Partenaire = p.id
            INNER JOIN (
                SELECT codtyparam, libelleparam
                FROM parametres WHERE typaram='MoisFacture'
            ) mois ON mois.codtyparam = CAST(ls.Mois_Facture AS VARCHAR)
            LEFT JOIN parametres param_statut
                ON param_statut.typaram = 'etape_Facture'
                AND CONVERT(varchar, param_statut.codtyparam) = CONVERT(varchar, ls.statut_ligne)
        ";

        switch ($reseau) {
            case 'phar':
                // Code natif: rejete=0, annuler=0, statut_ligne NOT IN (8,4), Code_Souscripteur IS NULL, Id_Categorie=2
                return "
                    SELECT DISTINCT
                        p.nom AS Tiers,
                        (CASE WHEN ls.is_evac = 0 THEN '' ELSE 'Evacuation' END) AS evacuation,
                        'Facture' AS typeF,
                        'Pharmacie' AS reseau,
                        {$baseSelect}
                    FROM Ligne_Suivi ls
                    {$baseJoins}
                    INNER JOIN type_prestataires tp ON p.code_type_prestataire = tp.code_type_prestataire
                    WHERE ISNULL(ls.annuler, 0) = 0
                    AND ISNULL(ls.rejete, 0) = 0
                    AND ls.Statut_Ligne NOT IN (8, 4)
                    AND p.type = 'prestataire'
                    AND tp.code_type_prestataire = '0'
                    AND ls.is_evac = 0
                    {$instanceCondition}
                ";

            case 'para':
                // Code natif: rejete=0, annuler=0, statut_ligne NOT IN (8,4), Code_Souscripteur IS NULL, Id_Categorie=1, coutierG=0
                return "
                    SELECT DISTINCT
                        p.nom AS Tiers,
                        (CASE WHEN ls.is_evac = 0 THEN '' ELSE 'Evacuation' END) AS evacuation,
                        'Facture' AS typeF,
                        'Parapharmacie' AS reseau,
                        {$baseSelect}
                    FROM Ligne_Suivi ls
                    {$baseJoins}
                    INNER JOIN type_prestataires tp ON p.code_type_prestataire = tp.code_type_prestataire
                    WHERE ISNULL(ls.annuler, 0) = 0
                    AND ISNULL(ls.rejete, 0) = 0
                    AND ls.Statut_Ligne NOT IN (8, 4)
                    AND p.type = 'prestataire'
                    AND tp.code_type_prestataire = '1'
                    AND ls.is_evac = 0
                    AND p.coutierG = 0
                    {$instanceCondition}
                ";

            case 'ind':
                // Code natif: rejete=0, annuler=0, statut_ligne NOT IN (8,4), Code_Prestataire IS NULL, is_evac=0
                return "
                    SELECT DISTINCT
                        COALESCE(p.nom, ls.Nom_Assure) AS Tiers,
                        '' AS evacuation,
                        'Facture' AS typeF,
                        'Individuel' AS reseau,
                        {$baseSelect}
                    FROM Ligne_Suivi ls
                    {$baseJoins}
                    WHERE ISNULL(ls.annuler, 0) = 0
                    AND ISNULL(ls.rejete, 0) = 0
                    AND ls.Statut_Ligne NOT IN (8, 4)
                    AND p.type = 'souscripteur'
                    AND ls.is_evac = 0
                    {$instanceCondition}
                ";

            case 'evac':
                // Code natif: rejete=0, annuler=0, statut_ligne NOT IN (8,4), Code_Prestataire IS NULL, is_evac=1
                return "
                    SELECT DISTINCT
                        COALESCE(p.nom, ls.Nom_Assure) AS Tiers,
                        'Evacuation' AS evacuation,
                        'Facture' AS typeF,
                        'Evacuation' AS reseau,
                        {$baseSelect}
                    FROM Ligne_Suivi ls
                    {$baseJoins}
                    WHERE ISNULL(ls.annuler, 0) = 0
                    AND ISNULL(ls.rejete, 0) = 0
                    AND ls.Statut_Ligne NOT IN (8, 4)
                    AND p.type = 'souscripteur'
                    AND ls.is_evac = 1
                    {$instanceCondition}
                ";

            case 'apfd':
                // Code natif: rejete=0, annuler=0, statut_ligne NOT IN (8,4), Code_Souscripteur IS NULL, Id_Categorie=1, coutierG=1
                return "
                    SELECT DISTINCT
                        p.nom AS Tiers,
                        (CASE WHEN ls.is_evac = 0 THEN '' ELSE 'Evacuation' END) AS evacuation,
                        'Appel de Fond' AS typeF,
                        'Courtier Gestionnaire' AS reseau,
                        {$baseSelect}
                    FROM Ligne_Suivi ls
                    {$baseJoins}
                    INNER JOIN type_prestataires tp ON p.code_type_prestataire = tp.code_type_prestataire
                    WHERE ISNULL(ls.annuler, 0) = 0
                    AND ISNULL(ls.rejete, 0) = 0
                    AND ls.Statut_Ligne NOT IN (8, 4)
                    AND p.type = 'prestataire'
                    AND tp.code_type_prestataire = '1'
                    AND p.coutierG = 1
                    {$instanceCondition}
                ";

            default:
                return "";
        }
    }

    private function buildUnionQueryForAllNetworks($type, $statutReglement = null, $statutInstance = null)
    {
        $reseaux = ['phar', 'para', 'ind', 'evac', 'apfd'];
        $unions = [];

        foreach ($reseaux as $reseau) {
            $unions[] = $this->getQueryByReseau($reseau, $type, $statutInstance);
        }

        $unionQuery = implode("\n UNION \n", $unions);

        if ($statutReglement && $type === 'recues') {
            $conditionStatut = $this->getStatutReglementCondition($statutReglement);
            return "SELECT * FROM ({$unionQuery}) AS combined {$conditionStatut}";
        }

        return $unionQuery;
    }

    private function getInstanceCondition($statutInstance)
    {
        switch ($statutInstance) {
            case 'ei': // En Instance - code natif: Numero_demande IS NULL
                return " AND ls.Numero_demande IS NULL ";
               
            case 'tr': // Traité - code natif: Numero_demande IS NOT NULL, Date_Demande IS NOT NULL
                return " AND ls.Numero_demande IS NOT NULL AND ls.Date_Demande IS NOT NULL ";
               
            case 'An': // Annulé - code natif: statut_ligne IN (8)
                return " AND ls.Statut_Ligne = 8 ";
               
            case 'it': // En instance Trésorerie - code natif: Date_Transmission IS NOT NULL, Numero_Cheque IS NULL
                return " AND ls.Numero_demande IS NOT NULL AND ls.Date_Transmission IS NOT NULL AND ls.Numero_Cheque IS NULL ";
               
            default:
                return "";
        }
    }

    private function getStatutReglementCondition($statutReglement)
    {
        switch ($statutReglement) {
            case 'reg': // Réglé - Numero_Cheque renseigné
                return "WHERE Numero_Cheque IS NOT NULL";
               
            case 'nreg': // Non réglé - Numero_Cheque vide
                return "WHERE Numero_Cheque IS NULL";
               
            case 'annul': // Annulé - Statut_Ligne = 8
                return "WHERE Statut_Ligne = 8";
               
            case 'ttreg': // Tous
            default:
                return "";
        }
    }
}