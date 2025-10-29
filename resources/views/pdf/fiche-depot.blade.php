<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche de Dépôt - {{ $courrier->CodeCour ?? 'N/A' }}</title>
    <style>
        /* Styles pour PDF (Dompdf supporte CSS basique) */
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif; /* Fallback pour accents */
            font-size: 12pt;
            line-height: 1.4;
            margin: 20mm;
            color: #000;
            background: white;
        }
        .header {
            text-align: center;
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .section {
            margin-bottom: 15px;
            padding-left: 30px; /* Retrait comme dans votre code original */
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 200px;
            min-width: 200px;
        }
        .value {
            margin-left: 10px;
        }
        .receptionniste {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin: 30px 0;
            border-top: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
            padding: 10px;
        }
        .signature-line {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            font-style: italic;
            font-size: 11pt;
        }
        .signature {
            width: 45%;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        .footer {
            position: fixed;
            bottom: 10mm;
            left: 20mm;
            right: 20mm;
            text-align: center;
            font-size: 9pt;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
        .page-break { page-break-before: always; } /* Si multi-pages */
    </style>
</head>
<body>
    <!-- Entête -->
    <div class="header">Fiche de Dépôt</div>

    <!-- Section Dépôt -->
    <div class="section">
        <div><span class="label">Ref. Courrier :</span><span class="value">{{ $courrier->CodeCour ?? 'N/A' }}</span></div>
        <div><span class="label">Date Dépôt :</span><span class="value">{{ $courrier->DateDepot ? $courrier->DateDepot->format('d/m/Y') : 'N/A' }}</span></div>
        <div><span class="label">Nom et prénom du déposant :</span><span class="value">{{ strtoupper(($courrier->NomDeposant ?? '') . ' ' . ($courrier->PrenomDeposant ?? '')) }}</span></div>
        <div><span class="label">Structure :</span><span class="value">{{ strtoupper($courrier->structure ?? '') }}</span></div>
        <div><span class="label">Nombre d'état(s) :</span><span class="value">{{ $courrier->nbreetatdepot ?? 0 }}</span></div>
        <div><span class="label">Pour le compte de :</span><span class="value">{{ $courrier->Comptede ?? '' }}</span></div>
        <div><span class="label">Motif :</span><span class="value">{{ $courrier->motif ?? '' }}</span></div>
    </div>

    <!-- Section Réceptionniste -->
    <div class="receptionniste">RÉCEPTIONNISTE</div>

    <div class="section">
        <div><span class="label">Date :</span><span class="value">{{ $courrier->datereception ? $courrier->datereception->format('d/m/Y') : 'N/A' }}</span></div>
        <div><span class="label">Nom et prénom :</span><span class="value">{{ strtoupper($courrier->Receptioniste ?? '') }}</span></div>
        <div><span class="label">Nombre d'état(s) Reçu(s) :</span><span class="value">{{ $courrier->nbrerecu ?? 0 }}</span></div>
    </div>

    <!-- Signatures -->
    <div class="signature-line">
        <div class="signature">Signature du déposant</div>
        <div class="signature">Pour la NSIA Assurances</div>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        Édité le : {{ now()->format('d/m/Y H:i') }} | Page {{ $pdf_page_number ?? 1 }}
    </div>
</body>
</html>
