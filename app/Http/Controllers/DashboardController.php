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
        $annee = $request->get('annee', date('Y')); // Année par défaut : année en cours

        // Récupérer les années disponibles (comme dans votre PHP)
        $annees = DB::connection('sqlsrv')
            ->table('Ligne_Suivi')
            ->whereNotNull('Annee_Facture')
            ->distinct()
            ->orderByDesc('Annee_Facture')
            ->pluck('Annee_Facture')
            ->take(10); // Limite à 10 ans

        // Charger les données initiales (pour l'année sélectionnée)
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

    // Méthode AJAX pour recharger les données par année
    public function getData(Request $request)
    {
        $annee = $request->get('annee', date('Y'));
        $data = $this->getDashboardData($annee);

        return response()->json($data);
    }

    private function getDashboardData($annee)
    {
        // 1. Cartes statistiques (adapté de votre PHP)
        $stats = $this->getStats($annee);

        // 2. Tableau "FACTURES SANTE (POINT MENSUEL)" (par catégorie)
        $pointMensuel = $this->getPointMensuel($annee);

        // 3. Tableau "REPARTITION MENSUELLE" (nombre de factures par mois)
        $repartitionMensuelle = $this->getRepartitionMensuelle($annee);

        return [
            'stats' => $stats,
            'pointMensuel' => $pointMensuel,
            'repartitionMensuelle' => $repartitionMensuelle,
        ];
    }

    private function getStats($annee)
    {
        // Requête pour les cartes (adapté de votre PHP)
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
            'Pharmacie' => "p.Id_Categorie = 2 AND ls.Code_partenaire IS NULL AND ls.is_evac = 0 AND p.type = 'prestataire'", // Corrigé : Code_partenaire et p.type
            'Parapharmacie' => "p.Id_Categorie = 1 AND ls.Code_partenaire IS NULL AND ls.is_evac = 0 AND (p.coutierG IS NULL OR p.coutierG = 0) AND p.type = 'prestataire'", // Corrigé
            'Individuels' => "ls.Code_partenaire IS NULL AND ls.is_evac = 0", // Corrigé : Code_partenaire
            'Evacuation' => "ls.Code_partenaire IS NULL AND ls.is_evac = 1", // Corrigé
            'Appels de fonds' => "p.Id_Categorie = 1 AND ls.Code_partenaire IS NULL AND ls.is_evac = 0 AND p.coutierG = 1 AND p.type = 'prestataire'", // Corrigé
        ];

        $data = [];
        foreach ($categories as $categorie => $condition) {
            $query = "
                SELECT Mois_Facture,
                       SUM(CASE WHEN Numero_demande IS NULL THEN Montant_Ligne - ISNULL(Montant_Reglement, 0) ELSE 0 END) AS montant
                FROM Ligne_Suivi ls
                LEFT JOIN partenaires p ON ls.Code_partenaire = p.id  -- Corrigé : Code_partenaire
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
            'Pharmacie' => "p.Id_Categorie = 2 AND ls.Code_partenaire IS NULL AND ls.is_evac = 0 AND p.type = 'prestataire'", // Corrigé
            'Parapharmacie' => "p.Id_Categorie = 1 AND ls.Code_partenaire IS NULL AND ls.is_evac = 0 AND (p.coutierG IS NULL OR p.coutierG = 0) AND p.type = 'prestataire'", // Corrigé
            'Individuels' => "ls.Code_partenaire IS NULL AND ls.is_evac = 0", // Corrigé
            'Evacuation' => "ls.Code_partenaire IS NULL AND ls.is_evac = 1", // Corrigé
            'Appels de fonds' => "p.Id_Categorie = 1 AND ls.Code_partenaire IS NULL AND ls.is_evac = 0 AND p.coutierG = 1 AND p.type = 'prestataire'", // Corrigé
        ];

        $data = [];
        foreach ($categories as $categorie => $condition) {
            $query = "
                SELECT Mois_Facture, COUNT(*) AS nombre
                FROM Ligne_Suivi ls
                LEFT JOIN partenaires p ON ls.Code_partenaire = p.id  -- Corrigé : Code_partenaire
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
}
