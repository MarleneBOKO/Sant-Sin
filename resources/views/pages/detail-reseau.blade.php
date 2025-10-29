@extends('../layout/' . $layout)

@section('subhead')
    <title>Gestion Factures - Réseau des {{ ucfirst($titreReseau ?? 'Pharmacies') }}</title>
    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
@endsection

@section('subcontent')
<div class="container mx-auto px-4 py-8 max-w-7xl">
    <!-- En-tête Dynamique -->
    <div class="bg-gradient-to-r from-purple-500 to-blue-600 text-white rounded-lg p-8 mb-8 text-center shadow-xl">
        <h1 class="text-4xl md:text-5xl font-bold mb-2 flex items-center justify-center">
            <i class="fas {{ $iconeReseau ?? 'fa-pills' }} mr-3 text-2xl"></i>
            Réseau des {{ ucfirst($titreReseau ?? 'Pharmacies') }}
        </h1>
        <p class="text-xl opacity-90">Dashboard de gestion des factures et suivi financier</p>
    </div>

    <!-- Filtre Amélioré (Année + Réseau) -->
    <div class="bg-white rounded-xl p-6 mb-8 shadow-lg">
        <form method="GET" action="{{ url()->current() }}" class="flex flex-col md:flex-row md:items-end gap-4">
            <!-- Select Réseau -->
            <div class="md:w-1/4">
                <label class="block text-sm font-bold text-blue-600 mb-2 flex items-center">
                    <i class="fas fa-sitemap mr-2"></i>Réseau :
                </label>
                <select name="reseau" id="reseau" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-300 text-lg" onchange="this.form.submit()">
                    <option value="pharmacies" {{ ($reseau ?? 'pharmacies') == 'pharmacies' ? 'selected' : '' }}>Pharmacies</option>
                    <option value="parapharmacie" {{ ($reseau ?? '') == 'parapharmacie' ? 'selected' : '' }}>Parapharmacies</option>
                    <option value="individuel" {{ ($reseau ?? '') == 'individuel' ? 'selected' : '' }}>Individuels</option>
                      <option value="evacuation" {{ ($reseau ?? '') == 'evacuation' ? 'selected' : '' }}>Evacuations</option>
                       <option value="courtiers" {{ ($reseau ?? '') == 'courtiers' ? 'selected' : '' }}>Courtiers</option>
                    {{-- Ajoutez d'autres : <option value="laboratoires">Laboratoires</option> --}}
                </select>
            </div>
            <!-- Select Année -->
            <div class="md:w-1/4">
                <label class="block text-sm font-bold text-blue-600 mb-2 flex items-center">
                    <i class="fas fa-calendar-alt mr-2"></i>Exercice :
                </label>
                <select name="annee" id="exercice" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-300 text-lg" onchange="this.form.submit()">
                    @foreach($annees as $a)
                        <option value="{{ $a }}" {{ $annee == $a ? 'selected' : '' }}>
                            {{ $a }}
                        </option>
                    @endforeach
                    <option value="{{ now()->year }}" {{ $annee == now()->year ? 'selected' : '' }}>
                        {{ now()->year }} (Année courante)
                    </option>
                </select>
            </div>
            {{-- Bouton Submit Optionnel (pour UX) --}}
            <div class="md:w-1/6">
                <button type="submit" class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-all duration-300 font-semibold flex items-center justify-center">
                    <i class="fas fa-search mr-2"></i>Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Ligne 1 : 3 Cards Principales (Dynamiques par Réseau) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Non Traités -->
        <div class="bg-white rounded-xl shadow-lg p-6 h-full transform transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl border-l-4 {{ ($reseau ?? 'pharmacies') == 'parapharmacie' ? 'border-orange-500' : 'border-red-500' }} card-animated">
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="text-sm font-bold text-red-600 uppercase mb-3 flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Non Traités
                    </div>
                    <div class="text-3xl font-bold text-gray-800 mb-3">
                        <span class="amount-badge amount-danger inline-block">
                            {{ number_format((float) ($nonTraites->total_inst ?? 0), 0, ',', ' ') }} FCFA
                        </span>
                    </div>
                    <div class="text-sm text-gray-600">
                        {{ number_format($nonTraites->nbre_inst ?? 0, 0, ',', ' ') }} facture(s)
                    </div>
                </div>
                <div class="ml-4 flex-shrink-0">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center card-icon">
                        <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Demande vs Facturé -->
        <div class="bg-white rounded-xl shadow-lg p-6 h-full transform transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl border-l-4 {{ ($reseau ?? 'pharmacies') == 'parapharmacie' ? 'border-blue-500' : 'border-blue-500' }} card-animated">
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="text-sm font-bold text-blue-600 uppercase mb-3 flex items-center">
                        <i class="fas fa-clipboard-list mr-2"></i>Demandé / Facturé
                    </div>
                    <div class="text-xl font-bold text-gray-800 mb-3">
                        <span class="amount-badge amount-info inline-block">
                            {{ number_format((float) ($demande->total_demande ?? 0), 0, ',', ' ') }} FCFA
                        </span>
                    </div>
                    <div class="text-sm text-gray-600 mb-4">
                        sur {{ number_format((float) ($demande->total_all ?? 0), 0, ',', ' ') }} FCFA facturé(s)
                    </div>
                    <div class="flex items-center">
                        <div class="text-2xl font-bold text-blue-600 mr-3">{{ $demande->taux_reglement ?? 0 }}%</div>
                        <div class="flex-1 bg-gray-200 rounded-full h-2">
                            <div class="bg-gradient-to-r from-blue-500 to-blue-700 h-2 rounded-full transition-all duration-1000 progress-bar-anim" style="width: {{ $demande->taux_reglement ?? 0 }}%" aria-valuenow="{{ $demande->taux_reglement ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
                <div class="ml-4 flex-shrink-0">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center card-icon">
                        <i class="fas fa-clipboard-list text-2xl text-blue-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Réglé -->
        <div class="bg-white rounded-xl shadow-lg p-6 h-full transform transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl border-l-4 {{ ($reseau ?? 'pharmacies') == 'parapharmacie' ? 'border-green-500' : 'border-green-500' }} card-animated">
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="text-sm font-bold text-green-600 uppercase mb-3 flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>Réglé
                    </div>
                    <div class="text-3xl font-bold text-gray-800 mb-3">
                        <span class="amount-badge amount-success inline-block">
                            {{ number_format((float) ($regle->total_regle ?? 0), 0, ',', ' ') }} FCFA
                        </span>
                    </div>
                    <div class="text-sm text-gray-600 mb-4">
                        sur {{ number_format((float) ($demande->total_demande ?? 0), 0, ',', ' ') }} FCFA demandé(s)
                    </div>
                    <div class="flex items-center">
                        <div class="text-2xl font-bold text-green-600 mr-3">{{ $tauxRegle ?? 0 }}%</div>
                        <div class="flex-1 bg-gray-200 rounded-full h-2">
                            <div class="bg-gradient-to-r from-green-500 to-green-700 h-2 rounded-full transition-all duration-1000 progress-bar-anim" style="width: {{ $tauxRegle ?? 0 }}%" aria-valuenow="{{ $tauxRegle ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
                <div class="ml-4 flex-shrink-0">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center card-icon">
                        <i class="fas fa-dollar-sign text-2xl text-green-600"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Séparateur Modernisé -->
    <div class="w-full h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent my-12 modern-divider"></div>

    <!-- Ligne 2 : 6 Cards Détaillées (Adaptatives - Noms Harmonisés) -->
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
        <!-- En Instance -->
        <div class="bg-white rounded-lg shadow-md p-4 h-full transform transition-all duration-300 hover:-translate-y-1 hover:shadow-lg border-l-4 border-blue-500 card-animated">
            <div class="flex items-center justify-center">
                <div class="text-center">
                    <div class="text-xs font-bold text-blue-600 uppercase mb-2 flex items-center justify-center">
                        <i class="fas fa-hourglass-half mr-1"></i>En Instance
                    </div>
                    <div class="text-2xl font-bold text-gray-800">{{ $instance ?? 0 }}</div>
                </div>
                <i class="fas fa-comments text-3xl text-gray-300 ml-2"></i>
            </div>
        </div>

        <!-- Traitées -->
        <div class="bg-white rounded-lg shadow-md p-4 h-full transform transition-all duration-300 hover:-translate-y-1 hover:shadow-lg border-l-4 border-green-500 card-animated">
            <div class="flex items-center justify-center">
                <div class="text-center">
                    <div class="text-xs font-bold text-green-600 uppercase mb-2 flex items-center justify-center">
                        <i class="fas fa-paper-plane mr-1"></i>Traitées
                    </div>
                    <div class="text-2xl font-bold text-gray-800">{{ $traitees ?? 0 }}</div>
                </div>
                <i class="fas fa-paper-plane text-3xl text-gray-300 ml-2"></i>
            </div>
        </div>

        <!-- En Trésorerie -->
        <div class="bg-white rounded-lg shadow-md p-4 h-full transform transition-all duration-300 hover:-translate-y-1 hover:shadow-lg border-l-4 border-yellow-500 card-animated">
            <div class="flex items-center justify-center">
                <div class="text-center">
                    <div class="text-xs font-bold text-yellow-600 uppercase mb-2 flex items-center justify-center">
                        <i class="fas fa-university mr-1"></i>En Trésorerie
                    </div>
                    <div class="text-2xl font-bold text-gray-800">{{ $tresor ?? 0 }}</div>
                </div>
                <i class="fas fa-university text-3xl text-gray-300 ml-2"></i>
            </div>
        </div>

        <!-- Réglées -->
        <div class="bg-white rounded-lg shadow-md p-4 h-full transform transition-all duration-300 hover:-translate-y-1 hover:shadow-lg border-l-4 border-indigo-500 card-animated">
            <div class="flex items-center justify-center">
                <div class="text-center">
                    <div class="text-xs font-bold text-indigo-600 uppercase mb-2 flex items-center justify-center">
                        <i class="fas fa-handshake mr-1"></i>Réglées
                    </div>
                    <div class="text-2xl font-bold text-gray-800">{{ $reglees ?? 0 }}</div>
                </div>
                <i class="fas fa-handshake text-3xl text-gray-300 ml-2"></i>
            </div>
        </div>

        <!-- Soldées -->
        <div class="bg-white rounded-lg shadow-md p-4 h-full transform transition-all duration-300 hover:-translate-y-1 hover:shadow-lg border-l-4 border-cyan-500 card-animated">
            <div class="flex items-center justify-center">
                <div class="text-center">
                    <div class="text-xs font-bold text-cyan-600 uppercase mb-2 flex items-center justify-center">
                        <i class="fas fa-lock mr-1"></i>Soldées
                    </div>
                    <div class="text-2xl font-bold text-gray-800">{{ $soldees ?? 0 }}</div>
                </div>
                <i class="fas fa-lock text-3xl text-gray-300 ml-2"></i>
            </div>
        </div>

        <!-- Rejets -->
        <div class="bg-white rounded-lg shadow-md p-4 h-full transform transition-all duration-300 hover:-translate-y-1 hover:shadow-lg border-l-4 border-red-500 card-animated">
            <div class="flex items-center justify-center">
                <div class="text-center">
                    <div class="text-xs font-bold text-red-600 uppercase mb-2 flex items-center justify-center">
                        <i class="fas fa-times-circle mr-1"></i>Rejetées
                    </div>
                    <div class="text-2xl font-bold text-gray-800">{{ $rejets->nbre_lg ?? 0 }}</div>
                </div>
                <i class="fas fa-exclamation-circle text-3xl text-gray-300 ml-2"></i>
            </div>
        </div>
    </div>

    <!-- Séparateur Modernisé -->
    <div class="w-full h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent my-12 modern-divider"></div>

    <!-- Tableau Mensuel (Dynamique avec $moisAnnee) -->
   <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-purple-500 to-blue-600 p-6 flex justify-between items-center">
            <h6 class="text-xl font-bold text-white flex items-center">
                <i class="fas fa-chart-line mr-2"></i>
                Suivi Mensuel des Factures {{ ucfirst($titreReseau ?? 'Pharmacies') }} ({{ $annee }})
            </h6>
            @if(($totalGlobal ?? 0) > 0)
                <button id="exportExcelBtn" class="bg-white text-purple-600 px-4 py-2 rounded-lg font-semibold hover:bg-gray-100 transition-all duration-300 flex items-center shadow-md">
                    <i class="fas fa-file-excel mr-2"></i>Exporter Excel
                </button>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full table-auto table-modern" id="dataTable">
                <thead>
                    <tr class="bg-gradient-to-r from-purple-500 to-blue-600 text-white">
                        <th class="text-left p-4 font-semibold uppercase text-sm tracking-wide">Indicateur</th>
                        @foreach($moisAnnee as $numMois => $libelleMois)
                            <th class="text-center p-4 font-semibold uppercase text-sm tracking-wide">{{ $libelleMois }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr class="hover:bg-blue-50 transition-colors duration-200">
                        <th class="text-left p-4 font-semibold bg-gray-50 border-r border-gray-200 flex items-center">
                            <i class="fas fa-file-invoice text-blue-600 mr-2"></i>Facturé(s)
                        </th>
                        @foreach($moisAnnee as $numMois => $libelleMois)
                            <td class="text-center p-4 font-semibold text-blue-600">
                                {{ number_format((float) ($tabMoisFacture[$numMois] ?? 0), 0, ',', ' ') }} FCFA
                            </td>
                        @endforeach
                    </tr>
                    <tr class="hover:bg-green-50 transition-colors duration-200">
                        <th class="text-left p-4 font-semibold bg-gray-50 border-r border-gray-200 flex items-center">
                            <i class="fas fa-check-circle text-green-600 mr-2"></i>Traité(s)
                        </th>
                        @foreach($moisAnnee as $numMois => $libelleMois)
                            <td class="text-center p-4 font-semibold text-green-600">
                                {{ number_format((float) ($tabMoisRegle[$numMois] ?? 0), 0, ',', ' ') }} FCFA
                            </td>
                        @endforeach
                    </tr>
                    <tr class="hover:bg-cyan-50 transition-colors duration-200">
                        <th class="text-left p-4 font-semibold bg-gray-50 border-r border-gray-200 flex items-center">
                            <i class="fas fa-minus-circle text-cyan-600 mr-2"></i>Frais Exclus
                        </th>
                        @foreach($moisAnnee as $numMois => $libelleMois)
                            <td class="text-center p-4 font-semibold text-cyan-600">
                                {{ number_format((float) ($tabMoisEcart[$numMois] ?? 0), 0, ',', ' ') }} FCFA
                            </td>
                        @endforeach
                    </tr>
                </tbody>
                <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                    <tr class="hover:bg-red-50 transition-colors duration-200">
                        <th class="text-left p-4 font-bold text-red-600 bg-gray-100 border-r border-gray-200 flex items-center">
                            <i class="fas fa-balance-scale text-red-600 mr-2"></i>Solde
                        </th>
                        @foreach($moisAnnee as $numMois => $libelleMois)
                            @php
                                $solde = ($tabSoldes[$numMois] ?? 0);
                                $soldeClass = $solde < 0 ? 'text-red-600 bg-red-50' : 'text-green-600 bg-green-50';
                            @endphp
                            <th class="text-center p-4 font-bold {{ $soldeClass }}">
                                {{ number_format((float) $solde, 0, ',', ' ') }} FCFA
                            </th>
                        @endforeach
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    </div>

    {{-- Alert si Pas de Données (Dynamique) --}}
    @if(($totalGlobal ?? 0) == 0)
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center mb-8">
        <div class="flex items-center justify-center">
            <i class="fas fa-info-circle text-blue-500 text-3xl mr-4"></i>
            <div>
                <h4 class="text-lg font-bold text-blue-800 mb-2">Aucune donnée pour l'exercice {{ $annee }}</h4>
                <p class="text-blue-600">Ajoutez des factures {{ ucfirst($titreReseau ?? 'Pharmacies') }} pour voir les statistiques en temps réel !</p>
            </div>
        </div>
    </div>
    @endif
</div>

@endsection

@push('styles')
<!-- Styles Custom pour Animations et Gradients (Minimal pour Tailwind) -->
<style>
    /* Animation SlideInUp pour Cards (Staggered) */
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .card-animated {
        animation: slideInUp 0.6s ease-out forwards;
        opacity: 0;  /* Start hidden pour animation */
    }
    .card-animated:nth-child(1) { animation-delay: 0.1s; }
    .card-animated:nth-child(2) { animation-delay: 0.2s; }
    .card-animated:nth-child(3) { animation-delay: 0.3s; }
    .card-animated:nth-child(4) { animation-delay: 0.4s; }
    .card-animated:nth-child(5) { animation-delay: 0.5s; }
    .card-animated:nth-child(6) { animation-delay: 0.6s; }

    /* Badges de Montants (Gradients Custom) */
    .amount-badge {
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        font-weight: 600;
        font-size: 0.875rem;
        display: inline-block;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .amount-danger {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        color: #fff;
    }
    .amount-info {
        background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        color: #374151;
    }
    .amount-success {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: #fff;
    }

    /* Icônes Rondes avec Hover Scale */
    .card-icon {
        transition: all 0.3s ease;
    }
    .card:hover .card-icon {
        transform: scale(1.1);
    }

    /* Divider Moderne */
    .modern-divider {
        position: relative;
    }
    .modern-divider::before {
        content: '';
        position: absolute;
        top: -2px;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 2px;
        background: linear-gradient(90deg, #4e73df, #1cc88a);
        border-radius: 1px;
    }

    /* Table Hover et Modern */
    .table-modern tbody tr:hover {
        transform: scale(1.005);
    }

    /* Progress Bar Anim (Custom pour JS) */
    .progress-bar-anim {
        transition: width 1.5s ease-in-out !important;
    }

    /* Force Horizontal sur Grids (Pour Mobile - Scroll si Besoin) */
    @media (max-width: 768px) {
        .grid-cols-3 {
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 1rem !important;
            overflow-x: auto;
            min-width: 900px;  /* Force scroll horizontal si petit écran */
            padding-bottom: 1rem;
        }
        .grid-cols-6 {
            grid-template-columns: repeat(2, 1fr) !important;  /* 2 par ligne sur mobile */
            gap: 0.5rem;
        }
        .card {
            min-width: 250px;  /* Compact sur mobile */
            font-size: 0.9rem;
        }
        .text-3xl { font-size: 1.75rem !important; }  /* Ajuste tailles */
        .text-2xl { font-size: 1.25rem !important; }
    }

    /* Amélioration Responsive pour Tableau */
    @media (max-width: 1024px) {
        .overflow-x-auto {
            -webkit-overflow-scrolling: touch;
        }
    }
</style>
@endpush


{{-- SCRIPTS DIRECTEMENT DANS LE BLADE (sans @push) --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
console.log('🚀 Script chargé - vérification XLSX:', typeof XLSX !== 'undefined' ? '✅ OK' : '❌ MANQUANT');

$(document).ready(function() {
    console.log('📦 jQuery ready - Début initialisation');

    const totalGlobal = {{ $totalGlobal ?? 0 }};
    console.log('📊 Total Global:', totalGlobal);

    // ========== VÉRIFICATION BOUTON EXPORT ==========
    const btnExists = $('#exportExcelBtn').length > 0;
    console.log('🔘 Bouton Export trouvé:', btnExists ? '✅ OUI' : '❌ NON');

    // ========== DATATABLES ==========
    if (totalGlobal > 0 && $('#dataTable').length) {
        console.log('📋 Initialisation DataTables...');
        $('#dataTable').DataTable({
            paging: false,
            lengthChange: false,
            searching: true,
            ordering: true,
            info: false,
            autoWidth: false,
            responsive: true,
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
            },
            columnDefs: [
                { orderable: false, targets: 0 },
                { className: "text-center", targets: "_all" }
            ],
            order: [[1, 'desc']]
        });
        console.log('✅ DataTables initialisé');
    }

    // ========== EXPORT EXCEL ==========
    $(document).on('click', '#exportExcelBtn', function(e) {
        console.log('🖱️ CLICK DÉTECTÉ sur le bouton Export !');
        e.preventDefault();
        e.stopPropagation();

        // Vérification XLSX
        if (typeof XLSX === 'undefined') {
            console.error('❌ XLSX non chargé !');
            alert('❌ Bibliothèque d\'export non chargée. Vérifiez votre connexion internet.');
            return;
        }
        console.log('✅ XLSX disponible');

        try {
            const titreReseau = "{{ $titreReseau ?? 'Pharmacies' }}".replace(/ /g, '_');
            const annee = "{{ $annee }}";
            const fileName = `Suivi_Mensuel_${titreReseau}_${annee}.xlsx`;

            console.log('📝 Nom du fichier:', fileName);

            // Récupère le tableau
            const table = document.getElementById('dataTable');
            if (!table) {
                console.error('❌ Tableau #dataTable introuvable !');
                throw new Error('Tableau introuvable dans le DOM');
            }
            console.log('✅ Tableau trouvé, lignes:', table.rows.length);

            console.log('⏳ Conversion en Excel...');

            // Conversion en workbook
            const wb = XLSX.utils.table_to_book(table, { sheet: "Suivi Mensuel" });
            const ws = wb.Sheets["Suivi Mensuel"];

            // Ajoute infos exercice en haut
            XLSX.utils.sheet_add_aoa(ws, [
                ["Exercice: " + annee, "Réseau: " + "{{ $titreReseau ?? 'Pharmacies' }}"]
            ], { origin: -1 });

            console.log('💾 Téléchargement du fichier...');

            // Téléchargement
            XLSX.writeFile(wb, fileName);

            console.log('✅ EXPORT RÉUSSI !', fileName);

            // Feedback visuel
            const btn = $(this);
            const originalHTML = btn.html();
            btn.html('<i class="fas fa-check mr-2"></i>✅ Exporté !')
               .css({
                   'background-color': '#10b981',
                   'color': '#ffffff'
               })
               .prop('disabled', true);

            setTimeout(() => {
                btn.html(originalHTML)
                   .css({
                       'background-color': '',
                       'color': ''
                   })
                   .prop('disabled', false);
            }, 2500);

        } catch (error) {
            console.error('❌ ERREUR COMPLÈTE:', error);
            alert('❌ Erreur lors de l\'export:\n' + error.message);
        }
    });

    // ========== ANIMATIONS PROGRESS BARS ==========
    setTimeout(function() {
        $('.progress-bar-anim').each(function() {
            const $bar = $(this);
            const width = parseInt($bar.attr('style')?.match(/width:\s*(\d+)%/)?.[1] || 0);
            $bar.css('width', '0%').animate({ width: width + '%' }, 1500);
        });
    }, 500);

    // ========== AUTO-SUBMIT SELECTS ==========
    $('#reseau, #exercice').on('change', function(e) {
        e.preventDefault();

        const currentUrl = new URL(window.location.href);
        const params = new URLSearchParams(currentUrl.search);

        if ($(this).attr('name') === 'reseau') {
            params.set('reseau', $(this).val());
        } else if ($(this).attr('name') === 'annee') {
            params.set('annee', $(this).val());
        }

        $('body').append(`
            <div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white p-6 rounded-lg shadow-xl">
                    <i class="fas fa-spinner fa-spin text-3xl text-blue-600 mb-2"></i>
                    <p class="text-gray-700 font-semibold">Chargement...</p>
                </div>
            </div>
        `);

        window.location.href = currentUrl.pathname + '?' + params.toString();
    });

    console.log('✅ Initialisation complète terminée');
});
</script>
