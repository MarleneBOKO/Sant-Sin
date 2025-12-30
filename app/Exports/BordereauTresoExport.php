<?php

namespace App\Exports;

use App\Models\LigneSuivi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Illuminate\Support\Facades\Log; // Ajouter pour le débogage

class BordereauTresoExport implements FromCollection, WithHeadings, WithEvents
{
    protected $ids;
    protected $dateDebut;
    protected $dateFin;

    public function __construct(array $ids, $dateDebut, $dateFin)
    {
        $this->ids = $ids;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
    }

    public function collection()
    {
        Log::info('BordereauTresoExport: Collection appelée', ['ids' => $this->ids]); // Log pour déboguer
        return LigneSuivi::whereIn('Id_Ligne', $this->ids)
            ->get()
            ->map(function ($l) {
                return [
                    $l->Numero_demande,
                    optional($l->Date_Enregistrement)->format('d/m/Y'),
                    $l->Nom_Assure,
                    formatFcfa($l->Montant_Ligne),
                    $l->Redacteur,
                    optional($l->Date_Transmission)->format('d/m/Y'),
                    $l->CodeCour,
                    $l->Reference_Facture,
                    $l->Code_Partenaire,
                    formatFcfa($l->Montant_Ligne),
                ];
            });
    }

    public function headings(): array
    {
        return [
            'N° Demande règlement',
            'Date création',
            'Bénéficiaire',
            'Montant demandé',
            'Utilisateur',
            'Date statut',
            'N° Décompte',
            'Description',
            'Prestataire',
            'Montant à payer',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                Log::info('BordereauTresoExport: AfterSheet appelé'); // Log pour déboguer
                $sheet = $event->sheet->getDelegate();
                
                // 1. D'ABORD insérer 5 lignes vides en haut
                $sheet->insertNewRowBefore(1, 5);
                
                // 2. ENSUITE placer le logo (ligne 1) - Vérifier si le fichier existe
                $logoPath = public_path('images/en-tete.png');
                if (file_exists($logoPath)) {
                    $drawing = new Drawing();
                    $drawing->setPath($logoPath);
                    $drawing->setHeight(60);
                    $drawing->setCoordinates('A1');
                    $drawing->setWorksheet($sheet);
                } else {
                    Log::warning('BordereauTresoExport: Logo non trouvé', ['path' => $logoPath]); // Log si le logo n'existe pas
                }
                
                // 3. TITRE (ligne 2, fusionner colonnes C à J)
                $sheet->mergeCells('C2:J2');
                $sheet->setCellValue('C2', 'LISTE DES DEMANDES DE RÈGLEMENTS DÉCOMPTÉS');
                $sheet->getStyle('C2')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('C2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                
                // 4. SOUS-TITRE (ligne 3)
                $sheet->mergeCells('C3:J3');
                $sheet->setCellValue('C3', 'Base : Date Demande du ' . $this->dateDebut . ' au ' . $this->dateFin);
                $sheet->getStyle('C3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                
                // 5. Les en-têtes du tableau seront maintenant à la ligne 6
                // et les données commencent à la ligne 7
                
                // Styliser les en-têtes (ligne 6)
                $sheet->getStyle('A6:J6')->getFont()->setBold(true);
                $sheet->getStyle('A6:J6')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');
                
                // Auto-dimensionner les colonnes
                foreach(range('A','J') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            }
        ];
    }
}