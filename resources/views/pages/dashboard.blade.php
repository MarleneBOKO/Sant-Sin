@extends('../layout/' . $layout)

@section('subcontent')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white shadow rounded mb-6">
        <div class="bg-blue-600 py-3 px-4 rounded-t flex justify-between items-center">
            <h1 class="text-white font-semibold text-lg">Tableau de bord</h1>
            <div class="flex items-center">
                <label class="text-white mr-2">Exercice :</label>
                <select id="exercice" class="rounded border border-gray-300 px-3 py-1 text-sm">
                    @foreach($annees as $an)
                        <option value="{{ $an }}" {{ $an == $annee ? 'selected' : '' }}>{{ $an }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div id="stats-container" class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Cartes statistiques -->
            <div class="bg-red-100 border-l-4 border-red-500 p-4 rounded">
                <div class="text-red-600 font-bold">{{ number_format($data['stats']['nonTraites']['montant'], 0, ',', ' ') }} FCFA non traité(s)</div>
                <div class="text-sm">soit {{ $data['stats']['nonTraites']['nombre'] }} factures</div>
            </div>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 rounded">
                <div class="text-yellow-600 font-bold">{{ number_format($data['stats']['demandes']['montant'], 0, ',', ' ') }} FCFA demandé(s)</div>
                <div class="text-sm">sur {{ number_format($data['stats']['demandes']['totalFacture'], 0, ',', ' ') }} FCFA facturé(s) - {{ $data['stats']['demandes']['taux'] }}%</div>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                    <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ $data['stats']['demandes']['taux'] }}%"></div>
                </div>
            </div>
            <div class="bg-green-100 border-l-4 border-green-500 p-4 rounded">
                <div class="text-green-600 font-bold">{{ number_format($data['stats']['regles']['montant'], 0, ',', ' ') }} FCFA réglé(s)</div>
                <div class="text-sm">sur {{ number_format($data['stats']['regles']['montantDemande'], 0, ',', ' ') }} FCFA demandé(s) - {{ $data['stats']['regles']['taux'] }}%</div>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ $data['stats']['regles']['taux'] }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau FACTURES SANTE (POINT MENSUEL) -->
    <div class="bg-white shadow rounded mb-6">
        <div class="bg-yellow-600 py-3 px-4 rounded-t">
            <h6 class="text-center text-white font-semibold">FACTURES SANTE (POINT MENSUEL)</h6>
        </div>
        <div class="p-4 overflow-x-auto">
            <table id="pointMensuelTable" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Catégorie</th>
                        @for($i = 1; $i <= 12; $i++)
                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ \Carbon\Carbon::create()->month($i)->format('F') }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody id="pointMensuelBody" class="bg-white divide-y divide-gray-200">
                    @foreach($data['pointMensuel'] as $categorie => $moisData)
                        <tr class="hover:bg-gray-50">
                            <td class="px-2 py-2 font-semibold">{{ $categorie }}</td>
                            @for($i = 1; $i <= 12; $i++)
                                <td class="px-2 py-2">{{ number_format($moisData[$i], 0, ',', ' ') }}</td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tableau REPARTITION MENSUELLE -->
    <div class="bg-white shadow rounded">
        <div class="bg-yellow-600 py-3 px-4 rounded-t">
            <h6 class="text-center text-white font-semibold">REPARTITION MENSUELLE</h6>
        </div>
        <div class="p-4 overflow-x-auto">
            <table id="repartitionTable" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Catégorie</th>
                        @for($i = 1; $i <= 12; $i++)
                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ \Carbon\Carbon::create()->month($i)->format('F') }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody id="repartitionBody" class="bg-white divide-y divide-gray-200">
                    @foreach($data['repartitionMensuelle'] as $categorie => $moisData)
                        <tr class="hover:bg-gray-50">
                            <td class="px-2 py-2 font-semibold">{{ $categorie }}</td>
                            @for($i = 1; $i <= 12; $i++)
                                <td class="px-2 py-2">{{ $moisData[$i] }}</td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal pour les détails des factures -->
<div class="modal fade" id="showdetail" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold">Détail des factures</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Libelle_Prestataire</th>
                                <th>Numero_Reception</th>
                                <th>Reference_Facture</th>
                                <th>Montant_Ligne</th>
                            </tr>
                        </thead>
                        <tbody id='det_row'></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#exercice').change(function() {
        const annee = $(this).val();
        $.ajax({
            url: '{{ route("dashboard.data") }}',
            method: 'GET',
            data: { annee: annee },
            success: function(response) {
                // Mettre à jour les stats
                $('#stats-container').html(`
                    <div class="bg-red-100 border-l-4 border-red-500 p-4 rounded">
                        <div class="text-red-600 font-bold">${response.stats.nonTraites.montant.toLocaleString()} FCFA non traité(s)</div>
                        <div class="text-sm">soit ${response.stats.nonTraites.nombre} factures</div>
                    </div>
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 rounded">
                        <div class="text-yellow-600 font-bold">${response.stats.demandes.montant.toLocaleString()} FCFA demandé(s)</div>
                        <div class="text-sm">sur ${response.stats.demandes.totalFacture.toLocaleString()} FCFA facturé(s) - ${response.stats.demandes.taux}%</div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="bg-yellow-500 h-2 rounded-full" style="width: ${response.stats.demandes.taux}%"></div>
                        </div>
                    </div>
                    <div class="bg-green-100 border-l-4 border-green-500 p-4 rounded">
                        <div class="text-green-600 font-bold">${response.stats.regles.montant.toLocaleString()} FCFA réglé(s)</div>
                        <div class="text-sm">sur ${response.stats.regles.montantDemande.toLocaleString()} FCFA demandé(s) - ${response.stats.regles.taux}%</div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: ${response.stats.regles.taux}%"></div>
                        </div>
                    </div>
                `);

                // Mettre à jour le tableau FACTURES SANTE (POINT MENSUEL)
                let pointMensuelHtml = '';
                for (const [categorie, moisData] of Object.entries(response.pointMensuel)) {
                    pointMensuelHtml += '<tr class="hover:bg-gray-50"><td class="px-2 py-2 font-semibold">' + categorie + '</td>';
                    for (let i = 1; i <= 12; i++) {
                        pointMensuelHtml += '<td class="px-2 py-2">' + (moisData[i] ? moisData[i].toLocaleString() : '0') + '</td>';
                    }
                    pointMensuelHtml += '</tr>';
                }
                $('#pointMensuelBody').html(pointMensuelHtml);

                // Mettre à jour le tableau REPARTITION MENSUELLE
                let repartitionHtml = '';
                for (const [categorie, moisData] of Object.entries(response.repartitionMensuelle)) {
                    repartitionHtml += '<tr class="hover:bg-gray-50"><td class="px-2 py-2 font-semibold">' + categorie + '</td>';
                    for (let i = 1; i <= 12; i++) {
                        repartitionHtml += '<td class="px-2 py-2">' + (moisData[i] || 0) + '</td>';
                    }
                    repartitionHtml += '</tr>';
                }
                $('#repartitionBody').html(repartitionHtml);
            }
        });
    });

    // Gestion des clics pour les détails (si vous avez des liens avec data-categ, etc.)
    $(document).on('click', '.det', function() {
        var annee = $(this).attr('data-an');
        var mois = $(this).attr('data-mois');
        var categorie = $(this).attr('data-categ');
        // Ajoutez ici l'appel AJAX pour charger les détails, similaire à votre code original
    });
});
</script>
@endsection

