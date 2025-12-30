@extends('../layout/' . $layout)

@section('subhead')
    <title>Correction de Facture</title>
    <!-- 🔥 AJOUTÉ : Meta tag CSRF pour éviter l'erreur CSRF -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="//unpkg.com/alpinejs" defer></script>
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.tailwindcss.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.tailwindcss.min.js"></script>
@endsection

@section('subcontent')
<div x-cloak class="p-6 bg-gray-50 min-h-screen">
    @php
        $profilCode = Auth::user()->profil?->code_profil;
        // 🔥 MODIFIÉ : Autoriser l'admin
        if (!in_array($profilCode, ['RRSI', 'RRSTP', 'ADMIN'])) {
            abort(403, 'Accès refusé. Cette page est réservée aux responsables et admins.');
        }
    @endphp

    <div class="max-w-7xl mx-auto">
        <!-- Titre dynamique selon le profil -->
        <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
            <h2 class="text-3xl font-bold text-gray-800">
                @if($profilCode === 'RRSI')
                    Correction de montant
                @elseif($profilCode === 'RRSTP')
                    Correction de montant
                @elseif($profilCode === 'ADMIN')
                    Correction de Facture  <!-- 🔥 AJOUTÉ : Titre pour admin -->
                @endif
            </h2>
        </div>

        <!-- Tableau des factures -->
        <div class="bg-white shadow-lg rounded-lg ">
            <div class="p-6">
                <table id="correctionTable" class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                        <tr>
                            @if($profilCode === 'RRSI' || $profilCode === 'ADMIN')
                                <th class="px-6 py-3">Assuré</th>
                                <th class="px-6 py-3">Souscripteur</th>
                            @endif
                            @if($profilCode === 'RRSTP' || $profilCode === 'ADMIN')
                                <th class="px-6 py-3">Prestataire</th>
                                <th class="px-6 py-3">N° Facture</th>
                            @endif
                            <th class="px-6 py-3">N° Réception</th>
                            <th class="px-6 py-3">Période</th>
                            <th class="px-6 py-3">Montant Facture</th>
                            <th class="px-6 py-3">Montant Règlement</th>
                            <th class="px-6 py-3">Montant Rejeté</th>
                            <th class="px-6 py-3">Date Enregistrement</th>
                            <th class="px-6 py-3">N° Demande</th>
                            <th class="px-6 py-3">Date Demande</th>
                            <th class="px-6 py-3">Année Facture</th>
                            <th class="px-6 py-3">Opérations</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($factures as $facture)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            @if($profilCode === 'RRSI' || $profilCode === 'ADMIN')
                                <td class="px-6 py-4">{{ $facture->Nom_Assure }}</td>
                                <td class="px-6 py-4">{{ $facture->souscripteur?->nom }}</td>
                            @endif
                            @if($profilCode === 'RRSTP' || $profilCode === 'ADMIN')
                                <td class="px-6 py-4">{{ $facture->prestataire?->nom }}</td>
                                <td class="px-6 py-4">{{ $facture->Reference_Facture }}</td>
                            @endif
                            <td class="px-6 py-4">{{ $facture->Numero_Reception }}</td>
                            <td class="px-6 py-4">{{ $facture->Date_Debut->format('d/m/Y') }} au {{ $facture->Date_Fin->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">{{ number_format($facture->Montant_Ligne, 0, ',', ' ') }}</td>
                            <td class="px-6 py-4">{{ number_format($facture->Montant_Reglement ?? 0, 0, ',', ' ') }}</td>
                            <td class="px-6 py-4">{{ number_format(($facture->Montant_Ligne - ($facture->Montant_Reglement ?? 0)), 0, ',', ' ') }}</td>
                            <td class="px-6 py-4">{{ optional($facture->Date_Enregistrement)->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">{{ $facture->Numero_demande }}</td>
                            <td class="px-6 py-4">{{ optional($facture->Date_Demande)->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">{{ $facture->Annee_Facture }}</td>
                            <td class="px-6 py-4">
                                <button type="button"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-md transition-all duration-200"
                                        data-id="{{ $facture->Id_Ligne }}"
                                        data-prest="{{ $profilCode === 'RRSI' || $profilCode === 'ADMIN' ? $facture->Nom_Assure : $facture->prestataire?->nom }}"
                                        data-souscript="{{ $profilCode === 'RRSI' || $profilCode === 'ADMIN' ? $facture->souscripteur?->nom : $facture->Reference_Facture }}"
                                        data-montant="{{ $facture->Montant_Ligne }}"
                                        data-reglement="{{ $facture->Montant_Reglement ?? 0 }}"
                                        data-rejete="{{ $facture->Montant_Ligne - ($facture->Montant_Reglement ?? 0) }}"
                                        data-demande="{{ $facture->Numero_demande }}"
                                        data-datedemande="{{ optional($facture->Date_Demande)->format('d/m/Y') }}"
                                        data-dttrans="{{ optional($facture->Date_Transmission)->format('d/m/Y') }}"
                                        onclick="openCorrectionModal(this)">
                                    Mettre à Jour
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal pour la mise à jour -->
    <div id="correctionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden flex items-center justify-center" x-show="$store.correctionFacture.showModal" x-transition>
        <div class="mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Mise à jour des Montants de la Facture</h3>
                <p class="text-sm text-red-600 mb-4">
                    Cette opération est une mise à jour permettant de corriger les montants erronés saisis (montant de la facture ou montant du règlement).
                    Veuillez vérifier les informations avant la validation de l'opération.
                </p>
                <form id="correctionForm" action="{{ route('correction.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="modalId">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                @if($profilCode === 'RRSI' || $profilCode === 'ADMIN') Assuré @else Prestataire @endif
                            </label>
                            <input type="text" id="modalPrest" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                @if($profilCode === 'RRSI' || $profilCode === 'ADMIN') Souscripteur @else N° Facture @endif
                            </label>
                            <input type="text" id="modalSouscript" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date Enregistrement</label>
                            <input type="text" id="modalDtEnreg" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Montant Facture</label>
                            <input type="number" name="MontantF" id="modalMontantF" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Montant Règlement</label>
                            <input type="number" name="montantReglement" id="modalMontantReglement" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Montant Rejeté</label>
                            <input type="number" name="montrejete" id="modalMontrejete" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">N° Demande</label>
                            <input type="text" id="modalDemande" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date Demande</label>
                            <input type="text" id="modalDateDemande" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date Transmission</label>
                            <input type="text" id="modalDtTrans" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Motif / Observation</label>
                            <textarea name="motifcorretion" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end mt-6">
                        <button type="button" onclick="closeCorrectionModal()" class="bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 rounded-lg mr-2">Annuler</button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Valider</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('correctionFacture', { showModal: false });
});

function openCorrectionModal(button) {
    document.getElementById('modalId').value = button.getAttribute('data-id');
    document.getElementById('modalPrest').value = button.getAttribute('data-prest');
    document.getElementById('modalSouscript').value = button.getAttribute('data-souscript');
    document.getElementById('modalMontantF').value = button.getAttribute('data-montant');
    document.getElementById('modalMontantReglement').value = button.getAttribute('data-reglement');
    document.getElementById('modalMontrejete').value = button.getAttribute('data-rejete');
    document.getElementById('modalDemande').value = button.getAttribute('data-demande');
    document.getElementById('modalDateDemande').value = button.getAttribute('data-datedemande');
    document.getElementById('modalDtTrans').value = button.getAttribute('data-dttrans');
    document.getElementById('modalDtEnreg').value = new Date().toLocaleDateString('fr-FR');
    document.getElementById('correctionModal').classList.remove('hidden');
    Alpine.store('correctionFacture').showModal = true;
}

function closeCorrectionModal() {
    document.getElementById('correctionModal').classList.add('hidden');
    Alpine.store('correctionFacture').showModal = false;
}

function calculateRejete() {
    const montantF = parseFloat(document.getElementById('modalMontantF').value) || 0;
    const montantReglement = parseFloat(document.getElementById('modalMontantReglement').value) || 0;
    const montrejete = montantF - montantReglement;
    document.getElementById('modalMontrejete').value = montrejete;
}

// Wait for jQuery and DataTables to be available
if (typeof jQuery !== 'undefined') {  // 🔥 CORRIGÉ : !== 'undefined' au lieu de !== ''
    jQuery(document).ready(function($) {
        $('#correctionTable').DataTable({
            "scrollX": true,

            "order": [[8, 'desc']],
            "pageLength": 25
        });

        $('#modalMontantF').on('input', calculateRejete);
        $('#modalMontantReglement').on('input', calculateRejete);
    });
} else {
    // Fallback: use vanilla JS if jQuery fails
    document.getElementById('modalMontantF')?.addEventListener('input', calculateRejete);
    document.getElementById('modalMontantReglement')?.addEventListener('input', calculateRejete);
}
</script>


<style>
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            margin: 0 4px; /* Espacement horizontal */
            padding: 6px 12px; /* Padding interne */
            border: 1px solid #ddd;
            background: #fff;
            color: #333;
            border-radius: 4px;
            cursor: pointer;
            display: inline-block; /* Assure l'affichage en ligne */
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #f0f0f0;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        .dataTables_wrapper .dataTables_info {
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .dataTables_wrapper .dataTables_paginate {
            margin-top: 10px;
        }
    </style>

@endsection
