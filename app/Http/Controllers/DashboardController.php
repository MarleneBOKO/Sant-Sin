<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $annee = $request->get('annee', date('Y'));

        // Récupérer les années disponibles
        $annees = DB::connection('sqlsrv')
            ->table('Ligne_Suivi')
            ->whereNotNull('Annee_Facture')
            ->distinct()
            ->orderByDesc('Annee_Facture')
            ->pluck('Annee_Facture')
            ->take(10);

        // DIAGNOSTIC: Vérifier la structure de la table partenaires
        $this->diagnosticPartenaires();

        // Charger les données
        $data = $this->getDashboardData($annee);

        return view('pages.dashboard', [
            'layout' => 'side-menu',
            'theme' => 'light',
            'page_name' => 'dashboard',
            'annee' => $annee,
            'annees' => $annees,
            'data' => $data,
            'top_menu' => [],
            'side_menu' => (new \App\Services\MenuService())->getMenusForAuthenticatedUser(),
            'simple_menu' => [],
            'first_page_name' => 'dashboard',
            'second_page_name' => null,
            'third_page_name' => null,
            'fakers' => [],
        ]);
    }

    // Méthode de diagnostic
    private function diagnosticPartenaires()
    {
        try {
            // Vérifier les colonnes de la table partenaires
            $columns = DB::connection('sqlsrv')->select("
                SELECT COLUMN_NAME
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_NAME = 'partenaires'
            ");

            Log::info('Colonnes de la table partenaires:', array_column($columns, 'COLUMN_NAME'));

            // Vérifier les types de partenaires
            $types = DB::connection('sqlsrv')->select("
                SELECT type, COUNT(*) as count
                FROM partenaires
                GROUP BY type
            ");

            Log::info('Types de partenaires:', (array)$types);

            // Vérifier les lignes avec Code_Partenaire
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
    }

    public function getData(Request $request)
    {
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
                       SUM(CASE WHEN Numero_demande IS NULL THEN Montant_Ligne - ISNULL(Montant_Reglement, 0) ELSE 0 END) AS montant
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

    }

    private function getDashboardData($annee)
    {
        $stats = $this->getStats($annee);
        $pointMensuel = $this->getPointMensuel($annee);
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
        /**
         * LOGIQUE SIMPLIFIÉE basée sur la structure de votre table partenaires:
         *
         * D'après votre document, la table partenaires a:
         * - type (prestataire/souscripteur)
         * - code_type_prestataire (0 pour pharmacie, autres valeurs pour parapharmacie, etc.)
         * - coutierG (0 ou 1)
         */

        $categories = [
            // Pharmacie: prestataires avec code_type_prestataire = 0
            'Pharmacie' => "ls.Code_Partenaire IS NOT NULL
                           AND p.type = 'prestataire'
                           AND CAST(p.code_type_prestataire AS VARCHAR) = '0'
                           AND ls.is_evac = 0",

            // Parapharmacie: prestataires avec code_type_prestataire != 0 et pas courtier
            'Parapharmacie' => "ls.Code_Partenaire IS NOT NULL
                               AND p.type = 'prestataire'
                               AND CAST(p.code_type_prestataire AS VARCHAR) != '0'
                               AND p.code_type_prestataire IS NOT NULL
                               AND ISNULL(p.coutierG, 0) = 0
                               AND ls.is_evac = 0",

            // Individuels: souscripteurs sans évacuation
            'Individuels' => "ls.Code_Partenaire IS NOT NULL
                             AND p.type = 'souscripteur'
                             AND ls.is_evac = 0",

            // Evacuation: souscripteurs avec évacuation
            'Evacuation' => "ls.Code_Partenaire IS NOT NULL
                            AND p.type = 'souscripteur'
                            AND ls.is_evac = 1",

            // Courtiers: prestataires avec coutierG = 1
            'Appels de fonds' => "ls.Code_Partenaire IS NOT NULL
                                 AND p.type = 'prestataire'
                                 AND p.coutierG = 1
                                 AND ls.is_evac = 0",
        ];

        $data = [];
        foreach ($categories as $categorie => $condition) {
            try {
                $query = "
                    SELECT Mois_Facture,
                           SUM(CASE WHEN Numero_demande IS NULL THEN Montant_Ligne - ISNULL(Montant_Reglement, 0) ELSE 0 END) AS montant
                    FROM Ligne_Suivi ls
                    INNER JOIN partenaires p ON ls.Code_Partenaire = p.id
                    WHERE rejete = 0
                      AND ISNULL(annuler, 0) = 0
                      AND Annee_Facture = ?
                      AND statut_ligne NOT IN (8, 4)
                      AND {$condition}
                    GROUP BY Mois_Facture
                ";

                $results = DB::connection('sqlsrv')->select($query, [$annee]);

                // Log pour debug
                Log::info("Catégorie: {$categorie}, Résultats: " . count($results));

                $moisData = array_fill(1, 12, 0);
                foreach ($results as $row) {
                    $moisData[$row->Mois_Facture] = $row->montant;
                }
                $data[$categorie] = $moisData;

            } catch (\Exception $e) {
                Log::error("Erreur pour {$categorie}: " . $e->getMessage());
                $data[$categorie] = array_fill(1, 12, 0);
            }
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
            'Pharmacie' => "ls.Code_Partenaire IS NOT NULL
                           AND p.type = 'prestataire'
                           AND CAST(p.code_type_prestataire AS VARCHAR) = '0'
                           AND ls.is_evac = 0",

            'Parapharmacie' => "ls.Code_Partenaire IS NOT NULL
                               AND p.type = 'prestataire'
                               AND CAST(p.code_type_prestataire AS VARCHAR) != '0'
                               AND p.code_type_prestataire IS NOT NULL
                               AND ISNULL(p.coutierG, 0) = 0
                               AND ls.is_evac = 0",

            'Individuels' => "ls.Code_Partenaire IS NOT NULL
                             AND p.type = 'souscripteur'
                             AND ls.is_evac = 0",

            'Evacuation' => "ls.Code_Partenaire IS NOT NULL
                            AND p.type = 'souscripteur'
                            AND ls.is_evac = 1",

            'Appels de fonds' => "ls.Code_Partenaire IS NOT NULL
                                 AND p.type = 'prestataire'
                                 AND p.coutierG = 1
                                 AND ls.is_evac = 0",
        ];

        $data = [];
        foreach ($categories as $categorie => $condition) {
            try {
                $query = "
                    SELECT Mois_Facture, COUNT(*) AS nombre
                    FROM Ligne_Suivi ls
                    INNER JOIN partenaires p ON ls.Code_Partenaire = p.id
                    WHERE rejete = 0
                      AND ISNULL(annuler, 0) = 0
                      AND Annee_Facture = ?
                      AND {$condition}
                    GROUP BY Mois_Facture
                ";

                $results = DB::connection('sqlsrv')->select($query, [$annee]);

                $moisData = array_fill(1, 12, 0);
                foreach ($results as $row) {
                    $moisData[$row->Mois_Facture] = $row->nombre;
                }
                $data[$categorie] = $moisData;

            } catch (\Exception $e) {
                Log::error("Erreur répartition pour {$categorie}: " . $e->getMessage());
                $data[$categorie] = array_fill(1, 12, 0);
            }
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
}
