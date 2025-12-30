@extends('../layout/' . $layout)

@section('subhead')
    <title>Situation Prestataire</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="//unpkg.com/alpinejs" defer></script>
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.tailwindcss.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.tailwindcss.min.js"></script>
    <!-- Ajout de Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection

@section('subcontent')
<div x-data="situationPrestataire()" x-cloak class="p-6 bg-gray-50 min-h-screen">
    @php
        $profilCode = Auth::user()->profil?->code_profil;
        // Vérifier l'accès (ajustez selon vos besoins)
        if (!in_array($profilCode, ['AUD', 'CTRL', 'RSIN', 'RSI', 'RSTP', 'RRSTP', 'RRSI', 'ADMIN'])) {
            abort(403, 'Accès refusé.');
        }
    @endphp

    <div class="max-w-7xl mx-auto">
        <!-- Titre -->
        <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
            <h2 class="text-3xl font-bold text-gray-800">Situation des Prestataires</h2>
            <p class="text-gray-600 mt-2">Point des factures reçues par prestataire et par mois.</p>
        </div>

        <!-- Formulaire de recherche -->
        <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
            <form id="searchForm" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Réseau</label>
                    <select id="reseau" name="reseau" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 select2">
                        <option value="tt">Tous les réseaux</option>
                        <option value="phar">Pharmacie</option>
                        <option value="para">Parapharmacie</option>
                        <option value="ind">Individuels</option>
                        <option value="evac">Evacuation</option>
                        <option value="apfd">Appels de fonds</option>
                    </select>
                </div>
               <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Année Facture</label>
                    <select id="statutr" name="statutr" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 select2">
                        <option value="tt">Toutes les années</option>
                        @foreach($annees as $annee)
                            <option value="{{ $annee }}">{{ $annee }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Prestataire</label>
                    <select id="prestataire" name="prestataire" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 select2">
                        <option value="">Tous les prestataires</option>
                        <!-- Les options seront chargées dynamiquement -->
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="button" id="btnSearch" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-md transition-all duration-200">
                        <i class="fas fa-search mr-2"></i>Afficher
                    </button>
                </div>
            </form>
        </div>

        <!-- Zone d'affichage des résultats -->
        <div id="results" class="bg-white shadow-lg rounded-lg overflow-hidden">
            <!-- Les données seront chargées ici via AJAX -->
        </div>
    </div>
</div>

<script>
function situationPrestataire() {
    return {
        // Pas de données réactives nécessaires ici
    }
}

$(document).ready(function() {
    // Initialiser Select2
    $('.select2').select2({
        placeholder: "Sélectionnez une option",
        allowClear: true
    });

    // Charger tous les prestataires au chargement de la page
    loadPrestataires();

    function loadPrestataires() {
        $('#prestataire').empty().append('<option value="">Tous les prestataires</option>');
        $.ajax({
            url: '{{ route("situation-prestataire.prestataires") }}',
            method: 'GET',
            success: function(data) {
                data.forEach(function(item) {
                    $('#prestataire').append('<option value="' + item.code + '">' + item.libelle + '</option>');
                });
                $('#prestataire').trigger('change.select2');
            },
            error: function() {
                console.error('Erreur lors du chargement des prestataires');
            }
        });
    }

    $('#btnSearch').click(function(event) {
        event.preventDefault();
        const reseau = $('#reseau').val();
        const statutr = $('#statutr').val();
        const prestataire = $('#prestataire').val();

        // Loader
        $('#results').html('<div class="p-6 text-center"><i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i> Chargement...</div>');

        $.ajax({
            url: '{{ route("situation-prestataire.data") }}',
            method: 'GET',
            data: { reseau: reseau, statutr: statutr, prestataire: prestataire },
            success: function(data) {
                $('#results').html(data);
                // Initialiser DataTable après chargement
                $('#dataTable').DataTable({
                    "scrollX": true,
                    "scrollY": 400,
                    "order": [[14, 'desc']], // Trier par Total Montant
                    "pageLength": 25,
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json"
                    }
                });
            },
            error: function() {
                $('#results').html('<div class="p-6 text-center text-red-500">Erreur lors du chargement des données.</div>');
            }
        });
    });
});
</script>

<style>
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        margin: 0 4px;
        padding: 6px 12px;
        border: 1px solid #ddd;
        background: #fff;
        color: #333;
        border-radius: 4px;
        cursor: pointer;
        display: inline-block;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f0f0f0;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }
</style>
@endsection
