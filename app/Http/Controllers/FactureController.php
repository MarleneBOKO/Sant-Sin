<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class FactureController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'type_facture' => 'required|in:recues,courrier,instance,etat_reglement', // Ajout de etat_reglement
            'reseau' => 'nullable|in:tt,phar,para,ind,evac,apfd',
            'statut_reglement' => 'nullable|in:ttreg,reg,nreg,annul',
            'statut_instance' => 'nullable|in:ei,tr,An,it',
            'type_courrier' => 'nullable|integer',
            'nature' => 'nullable|integer',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'DateS' => 'nullable|date',
            'finperiode' => 'nullable|date', // Nouveau pour etat_reglement
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
                'finperiode' => $request->finperiode, // Nouveau
            ];

            Log::info('Paramètres reçus pour listing : ', $params);
            $resultats = $this->getResultats($params);
            $perPage = 5;
    $currentPage = $request->get('page', 1);
    $factures = new LengthAwarePaginator(
        $resultats->forPage($currentPage, $perPage),  // Éléments de la page actuelle
        $resultats->count(),  // Nombre total d'éléments
        $perPage,
        $currentPage,
        [
            'path' => $request->url(),
            'pageName' => 'page',
    ]
);   
$factures->appends($request->query());  // Garde les filtres dans l'URL
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
        $statutReglement = $params['statut_reglement'] ?? 'ttreg';
        $dateDebut = $params['date_debut'];
        $dateFin = $params['date_fin'];
        $finperiode = $params['finperiode'] ?? null; // Nouveau pour etat_reglement

        switch ($typeFacture) {
            case 'recues':
                $query = $this->buildQueryRecues($reseau, $statutReglement);
                break;
            case 'courrier':
                $courriers = $this->getCourriers($params['type_courrier'], $params['nature'], $dateDebut, $dateFin);
                return $this->paginateCollection($courriers, 15, request()->get('page', 1));
            case 'instance':
                $query = $this->buildQueryInstance($reseau);
                break;
            case 'etat_reglement': // Nouveau case
                $query = $this->buildQueryEtatReglement($reseau, $finperiode);
                break;
            default:
                return $this->paginateCollection(collect([]), 15, request()->get('page', 1));
        }

        $finalQuery = "SELECT * FROM ({$query}) AS tb
                       WHERE CAST(dateenreg AS DATE) BETWEEN CAST(? AS DATE) AND CAST(? AS DATE)
                       ORDER BY dateenreg DESC";

        Log::info('Requête SQL générée : ' . substr($finalQuery, 0, 500) . '...');

        $factures = DB::connection('sqlsrv')->select($finalQuery, [$dateDebut, $dateFin]);
        Log::info('Nombre de factures récupérées : ' . count($factures));

        return $this->paginateCollection(collect($factures), 15, request()->get('page', 1));
    }

    private function paginateCollection($collection, $perPage, $currentPage)
    {
        $total = $collection->count();
        $offset = ($currentPage - 1) * $perPage;
        $items = $collection->slice($offset, $perPage);

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
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

        $courriers = DB::connection('sqlsrv')->select($sql, [$dateDebut, $dateFin, $typeCourrier, $nature]);
        Log::info('Nombre de courriers récupérés : ' . count($courriers));
        return collect($courriers);
    }

    private function buildQueryRecues($reseau, $statutReglement)
    {
        if ($reseau === 'tt') {
            return $this->buildUnionQueryForAllNetworks('recues', $statutReglement);
        }

        $baseQuery = $this->getQueryByReseau($reseau, 'recues');
        $conditionStatut = $this->getStatutReglementCondition($statutReglement);

        return "SELECT * FROM ({$baseQuery}) AS subquery {$conditionStatut}";
    }

    private function buildQueryInstance($reseau)
    {
        if ($reseau === 'tt') {
            return $this->buildUnionQueryForAllNetworks('instance');
        }
        return $this->getQueryByReseau($reseau, 'instance');
    }

    private function buildQueryEtatReglement($reseau, $finperiode = null) // Nouvelle méthode : adaptée de ton code PHP
    {
        $table = $finperiode ? 'historiqueLigne_Suivi' : 'Ligne_Suivi'; // Utilise historique si finperiode fourni
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

    private function getQueryEtatReglementByReseau($reseau, $table) // Helper pour etat_reglement
    {
        switch ($reseau) {
            case 'phar':
                return "
                    SELECT DISTINCT
                        CONVERT(varchar, ls.Date_Enregistrement, 103) AS Date_Enregistrement,
                        ls.Date_Enregistrement AS dateenreg,
                        p.nom AS Tiers,
                        ls.Reference_facture,
                        ls.Numero_reception,
                        mois.libelle_mois AS Mois_Facture,
                        ls.annee_facture,
                        CONVERT(varchar, ls.date_debut, 103) AS date_debut,
                        CONVERT(varchar, ls.date_fin, 103) AS date_fin,
                        ls.montant_ligne AS Montant_facture,
                        ls.Montant_Reglement,
                        CONVERT(varchar, ls.Date_Transmission, 103) AS Date_Transmission,
                        (CASE WHEN ls.is_evac = 0 THEN '' ELSE 'Evacuation' END) AS evacuation,
                        ls.redacteur,
                        ls.Statut_Ligne,
                        ISNULL(param_statut.libelleparam, 'Non défini') AS transmission,
                        'Facture' AS typeF,
                        'Pharmacie' AS reseau,
                        CONVERT(varchar, ls.datetransMedecin, 103) AS datetransMedecin,
                        CONVERT(varchar, ls.dateRetourMedecin, 103) AS RetourMedecin,
                        ls.dateRetourMedecin,
                        mois.id_mois,
                        ls.Numero_demande,
                        ls.Numero_Cheque,
                        ls.Date_Cloture,
                        ls.Date_Transmission AS DateTransmission
                    FROM {$table} ls
                    INNER JOIN partenaires p ON ls.Code_partenaire = p.id  -- Corrigé : Code_partenaire
                    INNER JOIN type_prestataires tp ON p.code_type_prestataire = tp.code_type_prestataire
                    INNER JOIN (
                        SELECT codtyparam AS id_mois, libelleparam AS libelle_mois
                        FROM parametres WHERE typaram='MoisFacture'
                    ) mois ON mois.id_mois = ls.Mois_Facture
                    LEFT JOIN parametres param_statut
                        ON param_statut.typaram = 'etape_Facture'
                        AND CONVERT(varchar, param_statut.codtyparam) = CONVERT(varchar, ls.statut_ligne)
                    WHERE ISNULL(ls.annuler, 0) = 0
                    AND p.type = 'prestataire'  -- Corrigé : utilise p.type
                    AND tp.code_type_prestataire = '0'
                    AND ls.Code_partenaire IS NULL  -- Corrigé : Code_partenaire (pour éviter souscripteurs)
                    AND ls.is_evac = 0
                ";
            case 'para':
                return "
                    SELECT DISTINCT
                        CONVERT(varchar, ls.Date_Enregistrement, 103) AS Date_Enregistrement,
                        ls.Date_Enregistrement AS dateenreg,
                        p.nom AS Tiers,
                        ls.Reference_facture,
                        ls.Numero_reception,
                        mois.libelle_mois AS Mois_Facture,
                        ls.annee_facture,
                        CONVERT(varchar, ls.date_debut, 103) AS date_debut,
                        CONVERT(varchar, ls.date_fin, 103) AS date_fin,
                        ls.montant_ligne AS Montant_facture,
                        ls.Montant_Reglement,
                        CONVERT(varchar, ls.Date_Transmission, 103) AS Date_Transmission,
                        (CASE WHEN ls.is_evac = 0 THEN '' ELSE 'Evacuation' END) AS evacuation,
                        ls.redacteur,
                        ls.Statut_Ligne,
                        ISNULL(param_statut.libelleparam, 'Non défini') AS transmission,
                        'Facture' AS typeF,
                        'Parapharmacie' AS reseau,
                        CONVERT(varchar, ls.datetransMedecin, 103) AS datetransMedecin,
                        CONVERT(varchar, ls.dateRetourMedecin, 103) AS RetourMedecin,
                        ls.dateRetourMedecin,
                        mois.id_mois,
                        ls.Numero_demande,
                        ls.Numero_Cheque,
                        ls.Date_Cloture,
                        ls.Date_Transmission AS DateTransmission
                    FROM {$table} ls
                    INNER JOIN partenaires p ON ls.Code_partenaire = p.id  -- Corrigé : Code_partenaire
                    INNER JOIN type_prestataires tp ON p.code_type_prestataire = tp.code_type_prestataire
                    INNER JOIN (
                        SELECT codtyparam AS id_mois, libelleparam AS libelle_mois
                        FROM parametres WHERE typaram='MoisFacture'
                    ) mois ON mois.id_mois = ls.Mois_Facture
                    LEFT JOIN parametres param_statut
                        ON param_statut.typaram = 'etape_Facture'
                        AND CONVERT(varchar, param_statut.codtyparam) = CONVERT(varchar, ls.statut_ligne)
                    WHERE ISNULL(ls.annuler, 0) = 0
                    AND p.type = 'prestataire'  -- Corrigé : utilise p.type
                    AND tp.code_type_prestataire = '1'
                    AND ls.Code_partenaire IS NULL  -- Corrigé : Code_partenaire
                    AND ls.is_evac = 0
                    AND p.coutierG = 0
                ";
            case 'ind':
                return "
                    SELECT DISTINCT
                        CONVERT(varchar, ls.Date_Enregistrement, 103) AS Date_Enregistrement,
                        ls.Date_Enregistrement AS dateenreg,
                        COALESCE(p.nom, ls.Nom_Assure) AS Tiers,
                        ls.Reference_facture,
                        ls.Numero_reception,
                        mois.libelle_mois AS Mois_Facture,
                        ls.annee_facture,
                        CONVERT(varchar, ls.date_debut, 103) AS date_debut,
                        CONVERT(varchar, ls.date_fin, 103) AS date_fin,
                        ls.montant_ligne AS Montant_facture,
                        ls.Montant_Reglement,
                        CONVERT(varchar, ls.Date_Transmission, 103) AS Date_Transmission,
                        '' AS evacuation,
                        ls.redacteur,
                        ls.Statut_Ligne,
                        ISNULL(param_statut.libelleparam, 'Non défini') AS transmission,
                        'Facture' AS typeF,
                        'Individuel' AS reseau,
                        CONVERT(varchar, ls.datetransMedecin, 103) AS datetransMedecin,
                        CONVERT(varchar, ls.dateRetourMedecin, 103) AS RetourMedecin,
                        ls.dateRetourMedecin,
                        mois.id_mois,
                        ls.Numero_demande,
                        ls.Numero_Cheque,
                        ls.Date_Cloture,
                        ls.Date_Transmission AS DateTransmission
                    FROM {$table} ls
                    LEFT JOIN partenaires p ON ls.Code_partenaire = p.id AND p.type = 'souscripteur'  -- Corrigé : Code_partenaire et p.type
                    INNER JOIN (
                        SELECT codtyparam AS id_mois, libelleparam AS libelle_mois
                        FROM parametres WHERE typaram='MoisFacture'
                    ) mois ON mois.id_mois = ls.Mois_Facture
                    LEFT JOIN parametres param_statut
                        ON param_statut.typaram = 'etape_Facture'
                        AND CONVERT(varchar, param_statut.codtyparam) = CONVERT(varchar, ls.statut_ligne)
                    WHERE ISNULL(ls.annuler, 0) = 0
                    AND ls.Code_partenaire IS NULL  -- Corrigé : Code_partenaire (pour individuels purs)
                    AND ls.is_evac = 0
                ";
            case 'evac':
                return "
                    SELECT DISTINCT
                        CONVERT(varchar, ls.Date_Enregistrement, 103) AS Date_Enregistrement,
                        ls.Date_Enregistrement AS dateenreg,
                        COALESCE(p.nom, ls.Nom_Assure) AS Tiers,
                        ls.Reference_facture,
                        ls.Numero_reception,
                        mois.libelle_mois AS Mois_Facture,
                        ls.annee_facture,
                        CONVERT(varchar, ls.date_debut, 103) AS date_debut,
                        CONVERT(varchar, ls.date_fin, 103) AS date_fin,
                        ls.montant_ligne AS Montant_facture,
                        ls.Montant_Reglement,
                        CONVERT(varchar, ls.Date_Transmission, 103) AS Date_Transmission,
                        'Evacuation' AS evacuation,
                        ls.redacteur,
                        ls.Statut_Ligne,
                        ISNULL(param_statut.libelleparam, 'Non défini') AS transmission,
                        'Facture' AS typeF,
                        'Evacuation' AS reseau,
                        CONVERT(varchar, ls.datetransMedecin, 103) AS datetransMedecin,
                        CONVERT(varchar, ls.dateRetourMedecin, 103) AS RetourMedecin,
                        ls.dateRetourMedecin,
                        mois.id_mois,
                        ls.Numero_demande,
                        ls.Numero_Cheque,
                        ls.Date_Cloture,
                        ls.Date_Transmission AS DateTransmission
                    FROM {$table} ls
                    LEFT JOIN partenaires p ON ls.Code_partenaire = p.id AND p.type = 'souscripteur'  -- Corrigé : Code_partenaire et p.type
                    INNER JOIN (
                        SELECT codtyparam AS id_mois, libelleparam AS libelle_mois
                        FROM parametres WHERE typaram='MoisFacture'
                    ) mois ON mois.id_mois = ls.Mois_Facture
                    LEFT JOIN parametres param_statut
                        ON param_statut.typaram = 'etape_Facture'
                        AND CONVERT(varchar, param_statut.codtyparam) = CONVERT(varchar, ls.statut_ligne)
                    WHERE ISNULL(ls.annuler, 0) = 0
                    AND ls.Code_partenaire IS NULL  -- Corrigé : Code_partenaire
                    AND ls.is_evac = 1
                ";
                        case 'apfd':
                return "
                    SELECT DISTINCT
                        CONVERT(varchar, ls.Date_Enregistrement, 103) AS Date_Enregistrement,
                        ls.Date_Enregistrement AS dateenreg,
                        p.nom AS Tiers,
                        ls.Reference_facture,
                        ls.Numero_reception,
                        mois.libelle_mois AS Mois_Facture,
                        ls.annee_facture,
                        CONVERT(varchar, ls.date_debut, 103) AS date_debut,
                        CONVERT(varchar, ls.date_fin, 103) AS date_fin,
                        ls.montant_ligne AS Montant_facture,
                        ls.Montant_Reglement,
                        CONVERT(varchar, ls.Date_Transmission, 103) AS Date_Transmission,
                        (CASE WHEN ls.is_evac = 0 THEN '' ELSE 'Evacuation' END) AS evacuation,
                        ls.redacteur,
                        ls.Statut_Ligne,
                        ISNULL(param_statut.libelleparam, 'Non défini') AS transmission,
                        'Appel de Fond' AS typeF,
                        'Courtier Gestionnaire' AS reseau,
                        CONVERT(varchar, ls.datetransMedecin, 103) AS datetransMedecin,
                        CONVERT(varchar, ls.dateRetourMedecin, 103) AS RetourMedecin,
                        ls.dateRetourMedecin,
                        mois.id_mois,
                        ls.Numero_demande,
                        ls.Numero_Cheque,
                        ls.Date_Cloture,
                        ls.Date_Transmission AS DateTransmission
                    FROM {$table} ls
                    INNER JOIN partenaires p ON ls.Code_partenaire = p.id  -- Corrigé : Code_partenaire
                    INNER JOIN type_prestataires tp ON p.code_type_prestataire = tp.code_type_prestataire
                    INNER JOIN (
                        SELECT codtyparam AS id_mois, libelleparam AS libelle_mois
                        FROM parametres WHERE typaram='MoisFacture'
                    ) mois ON mois.id_mois = ls.Mois_Facture
                    LEFT JOIN parametres param_statut
                        ON param_statut.typaram = 'etape_Facture'
                        AND CONVERT(varchar, param_statut.codtyparam) = CONVERT(varchar, ls.statut_ligne)
                    WHERE ISNULL(ls.annuler, 0) = 0
                    AND p.type = 'prestataire'  -- Corrigé : utilise p.type
                    AND tp.code_type_prestataire = '1'
                    AND p.coutierG = 1
                ";
            default:
                return "";
        }
    }

    private function getQueryByReseau($reseau, $type)
    {
        switch ($reseau) {
            case 'phar':
                return $this->getQueryPharmacie($type);
            case 'para':
                return $this->getQueryParapharmacie($type);
            case 'ind':
                return $this->getQueryIndividuels($type);
            case 'evac':
                return $this->getQueryEvacuations($type);
            case 'apfd':
                return $this->getQueryAppelsFonds($type);
            default:
                return "";
        }
    }

    // PHARMACIE
    private function getQueryPharmacie($type)
    {
        $instanceCondition = $this->getInstanceCondition($type);

        return "
        SELECT DISTINCT
            CONVERT(varchar, ls.Date_Enregistrement, 103) AS Date_Enregistrement,
            ls.Date_Enregistrement AS dateenreg,
            p.nom AS Tiers,
            ls.Reference_facture,
            ls.Numero_reception,
            mois.libelle_mois AS Mois_Facture,
            ls.annee_facture,
            CONVERT(varchar, ls.date_debut, 103) AS date_debut,
            CONVERT(varchar, ls.date_fin, 103) AS date_fin,
            ls.montant_ligne AS Montant_facture,
            ls.Montant_Reglement,
            ls.montrejete,
            CONVERT(varchar, ls.Date_Transmission, 103) AS Date_Transmission,
            (CASE WHEN ls.is_evac = 0 THEN '' ELSE 'Evacuation' END) AS evacuation,
            ls.redacteur,
            ls.Statut_Ligne,
            ISNULL(param_statut.libelleparam, 'Non défini') AS transmission,
            'Facture' AS typeF,
            'Pharmacie' AS reseau,
            CONVERT(varchar, ls.datetransMedecin, 103) AS datetransMedecin,
            CONVERT(varchar, ls.dateRetourMedecin, 103) AS RetourMedecin,
            ls.dateRetourMedecin,
            mois.id_mois,
            ls.Numero_demande,
            ls.Numero_Cheque,
            ls.Date_Cloture,
            ls.Date_Transmission AS DateTransmission
        FROM Ligne_Suivi ls
        INNER JOIN partenaires p ON ls.Code_partenaire = p.id  -- Corrigé : Code_partenaire
        INNER JOIN type_prestataires tp ON p.code_type_prestataire = tp.code_type_prestataire
        INNER JOIN (
            SELECT codtyparam AS id_mois, libelleparam AS libelle_mois
            FROM parametres WHERE typaram='MoisFacture'
        ) mois ON mois.id_mois = ls.Mois_Facture
        LEFT JOIN parametres param_statut
            ON param_statut.typaram = 'etape_Facture'
            AND CONVERT(varchar, param_statut.codtyparam) = CONVERT(varchar, ls.statut_ligne)
        WHERE ISNULL(ls.annuler, 0) = 0
        AND p.type = 'prestataire'  -- Corrigé : utilise p.type
        AND tp.code_type_prestataire = '0'
        AND ls.Code_partenaire IS NULL  -- Corrigé : Code_partenaire (pour éviter souscripteurs)
        AND ls.is_evac = 0
        {$instanceCondition}
        ";
    }

    // PARAPHARMACIE
    private function getQueryParapharmacie($type)
    {
        $instanceCondition = $this->getInstanceCondition($type);

        return "
        SELECT DISTINCT
            CONVERT(varchar, ls.Date_Enregistrement, 103) AS Date_Enregistrement,
            ls.Date_Enregistrement AS dateenreg,
            p.nom AS Tiers,
            ls.Reference_facture,
            ls.Numero_reception,
            mois.libelle_mois AS Mois_Facture,
            ls.annee_facture,
            CONVERT(varchar, ls.date_debut, 103) AS date_debut,
            CONVERT(varchar, ls.date_fin, 103) AS date_fin,
            ls.montant_ligne AS Montant_facture,
            ls.Montant_Reglement,
            ls.montrejete,
            CONVERT(varchar, ls.Date_Transmission, 103) AS Date_Transmission,
            (CASE WHEN ls.is_evac = 0 THEN '' ELSE 'Evacuation' END) AS evacuation,
            ls.redacteur,
            ls.Statut_Ligne,
            ISNULL(param_statut.libelleparam, 'Non défini') AS transmission,
            'Facture' AS typeF,
            'Parapharmacie' AS reseau,
            CONVERT(varchar, ls.datetransMedecin, 103) AS datetransMedecin,
            CONVERT(varchar, ls.dateRetourMedecin, 103) AS RetourMedecin,
            ls.dateRetourMedecin,
            mois.id_mois,
            ls.Numero_demande,
            ls.Numero_Cheque,
            ls.Date_Cloture,
            ls.Date_Transmission AS DateTransmission
        FROM Ligne_Suivi ls
        INNER JOIN partenaires p ON ls.Code_partenaire = p.id  -- Corrigé : Code_partenaire
        INNER JOIN type_prestataires tp ON p.code_type_prestataire = tp.code_type_prestataire
        INNER JOIN (
            SELECT codtyparam AS id_mois, libelleparam AS libelle_mois
            FROM parametres WHERE typaram='MoisFacture'
        ) mois ON mois.id_mois = ls.Mois_Facture
        LEFT JOIN parametres param_statut
            ON param_statut.typaram = 'etape_Facture'
            AND CONVERT(varchar, param_statut.codtyparam) = CONVERT(varchar, ls.statut_ligne)
        WHERE ISNULL(ls.annuler, 0) = 0
        AND p.type = 'prestataire'  -- Corrigé : utilise p.type
        AND tp.code_type_prestataire = '1'
        AND ls.Code_partenaire IS NULL  -- Corrigé : Code_partenaire
        AND ls.is_evac = 0
        AND p.coutierG = 0
        {$instanceCondition}
        ";
    }

    // INDIVIDUELS
    private function getQueryIndividuels($type)
    {
        $instanceCondition = $this->getInstanceCondition($type);

        return "
        SELECT DISTINCT
            CONVERT(varchar, ls.Date_Enregistrement, 103) AS Date_Enregistrement,
            ls.Date_Enregistrement AS dateenreg,
            COALESCE(p.nom, ls.Nom_Assure) AS Tiers,
            ls.Reference_facture,
            ls.Numero_reception,
            mois.libelle_mois AS Mois_Facture,
            ls.annee_facture,
            CONVERT(varchar, ls.date_debut, 103) AS date_debut,
            CONVERT(varchar, ls.date_fin, 103) AS date_fin,
            ls.montant_ligne AS Montant_facture,
            ls.Montant_Reglement,
            ls.montrejete,
            CONVERT(varchar, ls.Date_Transmission, 103) AS Date_Transmission,
            '' AS evacuation,
            ls.redacteur,
            ls.Statut_Ligne,
            ISNULL(param_statut.libelleparam, 'Non défini') AS transmission,
            'Facture' AS typeF,
            'Individuel' AS reseau,
            CONVERT(varchar, ls.datetransMedecin, 103) AS datetransMedecin,
            CONVERT(varchar, ls.dateRetourMedecin, 103) AS RetourMedecin,
            ls.dateRetourMedecin,
            mois.id_mois,
            ls.Numero_demande,
            ls.Numero_Cheque,
            ls.Date_Cloture,
            ls.Date_Transmission AS DateTransmission
        FROM Ligne_Suivi ls
        LEFT JOIN partenaires p ON ls.Code_partenaire = p.id AND p.type = 'souscripteur'  -- Corrigé : Code_partenaire et p.type
        INNER JOIN (
            SELECT codtyparam AS id_mois, libelleparam AS libelle_mois
            FROM parametres WHERE typaram='MoisFacture'
        ) mois ON mois.id_mois = ls.Mois_Facture
        LEFT JOIN parametres param_statut
            ON param_statut.typaram = 'etape_Facture'
            AND CONVERT(varchar, param_statut.codtyparam) = CONVERT(varchar, ls.statut_ligne)
        WHERE ISNULL(ls.annuler, 0) = 0
        AND ls.Code_partenaire IS NULL  -- Corrigé : Code_partenaire (pour individuels purs)
        AND ls.is_evac = 0
        {$instanceCondition}
        ";
    }

    // EVACUATIONS
    private function getQueryEvacuations($type)
    {
        $instanceCondition = $this->getInstanceCondition($type);

        return "
        SELECT DISTINCT
            CONVERT(varchar, ls.Date_Enregistrement, 103) AS Date_Enregistrement,
            ls.Date_Enregistrement AS dateenreg,
            COALESCE(p.nom, ls.Nom_Assure) AS Tiers,
            ls.Reference_facture,
            ls.Numero_reception,
            mois.libelle_mois AS Mois_Facture,
            ls.annee_facture,
            CONVERT(varchar, ls.date_debut, 103) AS date_debut,
            CONVERT(varchar, ls.date_fin, 103) AS date_fin,
            ls.montant_ligne AS Montant_facture,
            ls.Montant_Reglement,
            ls.montrejete,
            CONVERT(varchar, ls.Date_Transmission, 103) AS Date_Transmission,
            'Evacuation' AS evacuation,
            ls.redacteur,
            ls.Statut_Ligne,
            ISNULL(param_statut.libelleparam, 'Non défini') AS transmission,
            'Facture' AS typeF,
            'Evacuation' AS reseau,
            CONVERT(varchar, ls.datetransMedecin, 103) AS datetransMedecin,
            CONVERT(varchar, ls.dateRetourMedecin, 103) AS RetourMedecin,
            ls.dateRetourMedecin,
            mois.id_mois,
            ls.Numero_demande,
            ls.Numero_Cheque,
            ls.Date_Cloture,
            ls.Date_Transmission AS DateTransmission
        FROM Ligne_Suivi ls
        LEFT JOIN partenaires p ON ls.Code_partenaire = p.id AND p.type = 'souscripteur'  -- Corrigé : Code_partenaire et p.type
        INNER JOIN (
            SELECT codtyparam AS id_mois, libelleparam AS libelle_mois
            FROM parametres WHERE typaram='MoisFacture'
        ) mois ON mois.id_mois = ls.Mois_Facture
        LEFT JOIN parametres param_statut
            ON param_statut.typaram = 'etape_Facture'
            AND CONVERT(varchar, param_statut.codtyparam) = CONVERT(varchar, ls.statut_ligne)
        WHERE ISNULL(ls.annuler, 0) = 0
        AND ls.Code_partenaire IS NULL  -- Corrigé : Code_partenaire
        AND ls.is_evac = 1
        {$instanceCondition}
        ";
    }

    // APPELS DE FONDS
    private function getQueryAppelsFonds($type)
    {
        $instanceCondition = $this->getInstanceCondition($type);

        return "
        SELECT DISTINCT
            CONVERT(varchar, ls.Date_Enregistrement, 103) AS Date_Enregistrement,
            ls.Date_Enregistrement AS dateenreg,
            p.nom AS Tiers,
            ls.Reference_facture,
            ls.Numero_reception,
            mois.libelle_mois AS Mois_Facture,
            ls.annee_facture,
            CONVERT(varchar, ls.date_debut, 103) AS date_debut,
            CONVERT(varchar, ls.date_fin, 103) AS date_fin,
            ls.montant_ligne AS Montant_facture,
            ls.Montant_Reglement,
            ls.montrejete,
            CONVERT(varchar, ls.Date_Transmission, 103) AS Date_Transmission,
            (CASE WHEN ls.is_evac = 0 THEN '' ELSE 'Evacuation' END) AS evacuation,
            ls.redacteur,
            ls.Statut_Ligne,
            ISNULL(param_statut.libelleparam, 'Non défini') AS transmission,
            'Appel de Fond' AS typeF,
            'Courtier Gestionnaire' AS reseau,
            CONVERT(varchar, ls.datetransMedecin, 103) AS datetransMedecin,
            CONVERT(varchar, ls.dateRetourMedecin, 103) AS RetourMedecin,
            ls.dateRetourMedecin,
            mois.id_mois,
            ls.Numero_demande,
            ls.Numero_Cheque,
            ls.Date_Cloture,
            ls.Date_Transmission AS DateTransmission
        FROM Ligne_Suivi ls
        INNER JOIN partenaires p ON ls.Code_partenaire = p.id  -- Corrigé : Code_partenaire
        INNER JOIN type_prestataires tp ON p.code_type_prestataire = tp.code_type_prestataire
        INNER JOIN (
            SELECT codtyparam AS id_mois, libelleparam AS libelle_mois
            FROM parametres WHERE typaram='MoisFacture'
        ) mois ON mois.id_mois = ls.Mois_Facture
        LEFT JOIN parametres param_statut
            ON param_statut.typaram = 'etape_Facture'
            AND CONVERT(varchar, param_statut.codtyparam) = CONVERT(varchar, ls.statut_ligne)
        WHERE ISNULL(ls.annuler, 0) = 0
        AND p.type = 'prestataire'  -- Corrigé : utilise p.type
        AND tp.code_type_prestataire = '1'
        AND p.coutierG = 1
        {$instanceCondition}
        ";
    }

    private function buildUnionQueryForAllNetworks($type, $statutReglement = null)
    {
        $reseaux = ['phar', 'para', 'ind', 'evac'];
        $unions = [];

        foreach ($reseaux as $reseau) {
            $unions[] = $this->getQueryByReseau($reseau, $type);
        }

        $unionQuery = implode("\n UNION \n", $unions);

        if ($statutReglement && $type === 'recues') {
            $conditionStatut = $this->getStatutReglementCondition($statutReglement);
            return "SELECT * FROM ({$unionQuery}) AS combined {$conditionStatut}";
        }

        return $unionQuery;
    }

  private function getInstanceCondition($type)
{
    switch ($type) {
        case 'ei': // En Instance
            return " AND ls.Statut_Ligne NOT IN (4, 8) AND ISNULL(ls.Montant_Reglement, 0) = 0 ";
        case 'tr': // Traité
            return " AND ls.Statut_Ligne IN (4, 8) ";
        case 'An': // Annulé
            return " AND ls.annuler = 1 ";
        case 'it': // En instance Trésorerie
            return " AND ls.datetransMedecin IS NOT NULL AND ls.Date_Transmission IS NULL ";
        default:
            return "";
    }
}

    private function getStatutReglementCondition($statutReglement)
    {
        switch ($statutReglement) {
            case 'reg':
                return "WHERE Numero_Cheque IS NOT NULL";
            case 'nreg':
                return "WHERE Numero_Cheque IS NULL";
            case 'annul':
                return "WHERE Statut_Ligne = 8";
            case 'ttreg':
            default:
                return "";
        }
    }
}

