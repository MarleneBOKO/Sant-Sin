<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SituationPrestataireController extends Controller
{
    public function getData(Request $request)
    {
        $reseau = $request->get('reseau', 'tt');
        $statutr = $request->get('statutr', 'tt');
        $prestataire = $request->get('prestataire', '');

        // Construire la requête SQL basée sur le réseau, l'année et le prestataire
        $sql = $this->buildSql($reseau, $statutr, $prestataire);

        if (!$sql) {
            return '<div class="p-6 text-center text-red-500">Erreur dans les paramètres.</div>';
        }

        $results = DB::select($sql);

        if (empty($results)) {
            return '<div class="p-6 text-center">Aucune donnée disponible.</div>';
        }

        // Générer le HTML du tableau
        return $this->generateTableHtml($results);
    }

    public function getPrestataires(Request $request)
    {
        // Retourner tous les prestataires depuis la table partenaires
        return DB::table('partenaires')
            ->where('type', 'prestataire')
            ->select('id as code', 'nom as libelle')
            ->orderBy('nom')
            ->get();
    }

    private function buildSql($reseau, $statutr, $prestataire)
    {
        $baseSql = "
            SELECT
                ls.Code_Partenaire AS Code_Prestataire,
                p.nom AS Libelle_Prestataire,
                SUM(CASE WHEN Mois_Facture = 1 THEN ls.Montant_Ligne ELSE 0 END) AS JANVIER,
                SUM(CASE WHEN Mois_Facture = 2 THEN ls.Montant_Ligne ELSE 0 END) AS FEVRIER,
                SUM(CASE WHEN Mois_Facture = 3 THEN ls.Montant_Ligne ELSE 0 END) AS MARS,
                SUM(CASE WHEN Mois_Facture = 4 THEN ls.Montant_Ligne ELSE 0 END) AS AVRIL,
                SUM(CASE WHEN Mois_Facture = 5 THEN ls.Montant_Ligne ELSE 0 END) AS MAI,
                SUM(CASE WHEN Mois_Facture = 6 THEN ls.Montant_Ligne ELSE 0 END) AS JUIN,
                SUM(CASE WHEN Mois_Facture = 7 THEN ls.Montant_Ligne ELSE 0 END) AS JUILLET,
                SUM(CASE WHEN Mois_Facture = 8 THEN ls.Montant_Ligne ELSE 0 END) AS AOUT,
                SUM(CASE WHEN Mois_Facture = 9 THEN ls.Montant_Ligne ELSE 0 END) AS SEPTEMBRE,
                SUM(CASE WHEN Mois_Facture = 10 THEN ls.Montant_Ligne ELSE 0 END) AS OCTOBRE,
                SUM(CASE WHEN Mois_Facture = 11 THEN ls.Montant_Ligne ELSE 0 END) AS NOVEMBRE,
                SUM(CASE WHEN Mois_Facture = 12 THEN ls.Montant_Ligne ELSE 0 END) AS DECEMBRE,
                Annee_Facture,
                SUM(ls.Montant_Ligne) AS TOTAL_MONTANT
            FROM Ligne_Suivi ls
            INNER JOIN lesparametres lp ON lp.codtyparam = ls.Mois_Facture AND lp.typaram = 'MoisFacture'
            INNER JOIN partenaires p ON p.id = ls.Code_Partenaire
            WHERE ls.Code_Partenaire IS NOT NULL
            AND p.type = 'prestataire'
            AND statut_ligne NOT IN (8)
            AND rejete = 0
            AND ISNULL(annuler, 0) = 0
        ";

        // Conditions selon le réseau
        switch ($reseau) {
            case 'tt':
                // Tous les réseaux
                break;
            case 'phar':
                $baseSql .= " AND p.Id_Categorie = 2 AND ls.is_evac = 0";
                break;
            case 'para':
                $baseSql .= " AND ls.Code_Partenaire NOT IN (SELECT id FROM partenaires WHERE Id_Categorie = 1 AND ISNULL(coutierG, 0) = 1)
                              AND p.Id_Categorie = 1 AND ls.is_evac = 0";
                break;
            case 'ind':
                // Pour individuels, changer la jointure pour souscripteurs
                $baseSql = str_replace('INNER JOIN partenaires p ON p.id = ls.Code_Partenaire', 'INNER JOIN partenaires p ON p.id = ls.Code_Partenaire', $baseSql);
                $baseSql = str_replace("AND p.type = 'prestataire'", "AND p.type = 'souscripteur'", $baseSql);
                $baseSql .= " AND ls.is_evac = 0";
                break;
            case 'evac':
                // Pour évacuations, changer la jointure pour souscripteurs
                $baseSql = str_replace('INNER JOIN partenaires p ON p.id = ls.Code_Partenaire', 'INNER JOIN partenaires p ON p.id = ls.Code_Partenaire', $baseSql);
                $baseSql = str_replace("AND p.type = 'prestataire'", "AND p.type = 'souscripteur'", $baseSql);
                $baseSql .= " AND ls.is_evac = 1";
                break;
            case 'apfd':
                $baseSql .= " AND p.Id_Categorie = 1 AND p.coutierG = 1";
                break;
            default:
                return false;
        }

        // Filtre par prestataire si sélectionné
        if (!empty($prestataire)) {
            $baseSql .= " AND ls.Code_Partenaire = '$prestataire'";
        }

        $baseSql .= " GROUP BY ls.Code_Partenaire, p.nom, Annee_Facture";

        if ($statutr !== 'tt') {
            $baseSql .= " HAVING Annee_Facture = '$statutr'";
        }

        $baseSql .= " ORDER BY p.nom";

        return $baseSql;
    }

    private function generateTableHtml($results)
    {
        $html = '
        <div class="p-6">
            <div class="overflow-x-auto">
                <table id="dataTable" class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                        <tr>
                            <th class="px-6 py-3">Prestataire</th>
                            <th class="px-6 py-3">Janvier</th>
                            <th class="px-6 py-3">Février</th>
                            <th class="px-6 py-3">Mars</th>
                            <th class="px-6 py-3">Avril</th>
                            <th class="px-6 py-3">Mai</th>
                            <th class="px-6 py-3">Juin</th>
                            <th class="px-6 py-3">Juillet</th>
                            <th class="px-6 py-3">Août</th>
                            <th class="px-6 py-3">Septembre</th>
                            <th class="px-6 py-3">Octobre</th>
                            <th class="px-6 py-3">Novembre</th>
                            <th class="px-6 py-3">Décembre</th>
                            <th class="px-6 py-3">Total Montant</th>
                            <th class="px-6 py-3">Année</th>
                        </tr>
                    </thead>
                    <tbody>';

        foreach ($results as $row) {
            $html .= '<tr class="bg-white border-b hover:bg-gray-50">';
            $html .= '<td class="px-6 py-4">' . htmlspecialchars($row->Libelle_Prestataire) . '</td>';
            $html .= '<td class="px-6 py-4">' . number_format($row->JANVIER, 0, ',', ' ') . '</td>';
            $html .= '<td class="px-6 py-4">' . number_format($row->FEVRIER, 0, ',', ' ') . '</td>';
            $html .= '<td class="px-6 py-4">' . number_format($row->MARS, 0, ',', ' ') . '</td>';
            $html .= '<td class="px-6 py-4">' . number_format($row->AVRIL, 0, ',', ' ') . '</td>';
            $html .= '<td class="px-6 py-4">' . number_format($row->MAI, 0, ',', ' ') . '</td>';
            $html .= '<td class="px-6 py-4">' . number_format($row->JUIN, 0, ',', ' ') . '</td>';
            $html .= '<td class="px-6 py-4">' . number_format($row->JUILLET, 0, ',', ' ') . '</td>';
            $html .= '<td class="px-6 py-4">' . number_format($row->AOUT, 0, ',', ' ') . '</td>';
            $html .= '<td class="px-6 py-4">' . number_format($row->SEPTEMBRE, 0, ',', ' ') . '</td>';
            $html .= '<td class="px-6 py-4">' . number_format($row->OCTOBRE, 0, ',', ' ') . '</td>';
            $html .= '<td class="px-6 py-4">' . number_format($row->NOVEMBRE, 0, ',', ' ') . '</td>';
            $html .= '<td class="px-6 py-4">' . number_format($row->DECEMBRE, 0, ',', ' ') . '</td>';
            $html .= '<td class="px-6 py-4 font-bold">' . number_format($row->TOTAL_MONTANT, 0, ',', ' ') . '</td>';
            $html .= '<td class="px-6 py-4">' . $row->Annee_Facture . '</td>';
            $html .= '</tr>';
        }

        $html .= '
                    </tbody>
                </table>
            </div>
        </div>';

        return $html;
    }
}
