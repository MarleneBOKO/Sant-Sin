@extends('../layout/' . $layout)

@section('subhead')
    <title>Correction de Facture</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="//unpkg.com/alpinejs" defer></script>
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.tailwindcss.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.tailwindcss.min.js"></script>
@endsection

@section('subcontent')
<div x-cloak class="p-6 bg-slate-50 min-h-screen text-slate-700 font-sans">
    @php
        $profilCode = Auth::user()->profil?->code_profil;
        if (!in_array($profilCode, ['RRSI', 'RRSTP', 'ADMIN'])) {
            abort(403, 'Accès refusé.');
        }
    @endphp

    <div class="max-w-full mx-auto">
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">
                    @if($profilCode === 'RRSI' || $profilCode === 'RRSTP') Correction de montant
                    @elseif($profilCode === 'ADMIN') Correction de Facture @endif
                </h2>
            </div>
        </div>

        <div class="bg-white shadow-sm border border-slate-200 rounded-2xl overflow-hidden">
            <div class="p-6">
                <table id="correctionTable" class="w-full text-sm text-left">
                    <thead class="text-slate-500 uppercase text-xs tracking-widest bg-slate-50/50">
                        <tr>
                            @if($profilCode === 'RRSI' || $profilCode === 'ADMIN')
                                <th class="px-6 py-4 font-semibold border-b">Assuré</th>
                                <th class="px-6 py-4 font-semibold border-b">Souscripteur</th>
                            @endif
                            @if($profilCode === 'RRSTP' || $profilCode === 'ADMIN')
                                <th class="px-6 py-4 font-semibold border-b">Prestataire</th>
                                <th class="px-6 py-4 font-semibold border-b">N° Facture</th>
                            @endif
                            <th class="px-6 py-4 font-semibold border-b">N° Réception</th>
                            <th class="px-6 py-4 font-semibold border-b text-center">Montant Facture</th>
                            <th class="px-6 py-4 font-semibold border-b text-center">Montant Règlement</th>
                            <th class="px-6 py-4 font-semibold border-b text-center">Montant Rejeté</th>
                            <th class="px-6 py-4 font-semibold border-b text-center">Année</th>
                            <th class="px-6 py-4 font-semibold border-b text-center">Opérations</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($factures as $facture)
                        <tr class="hover:bg-slate-50/80 transition-all">
                            @if($profilCode === 'RRSI' || $profilCode === 'ADMIN')
                                <td class="px-6 py-4 font-medium text-slate-900">{{ $facture->Nom_Assure }}</td>
                                <td class="px-6 py-4">{{ $facture->souscripteur?->nom }}</td>
                            @endif
                            @if($profilCode === 'RRSTP' || $profilCode === 'ADMIN')
                                <td class="px-6 py-4">{{ $facture->prestataire?->nom }}</td>
                                <td class="px-6 py-4 font-mono text-xs text-indigo-600 uppercase">{{ $facture->Reference_Facture }}</td>
                            @endif
                            <td class="px-6 py-4 text-slate-500">{{ $facture->Numero_Reception }}</td>
                            <td class="px-6 py-4 font-bold text-slate-800 text-center">{{ number_format($facture->Montant_Ligne, 0, ',', ' ') }}</td>
                            <td class="px-6 py-4 font-bold text-emerald-600 text-center">{{ number_format($facture->Montant_Reglement ?? 0, 0, ',', ' ') }}</td>
                            <td class="px-6 py-4 font-bold text-rose-600 text-center">{{ number_format(($facture->Montant_Ligne - ($facture->Montant_Reglement ?? 0)), 0, ',', ' ') }}</td>
                            <td class="px-6 py-4 text-center">{{ $facture->Annee_Facture }}</td>
                            <td class="px-6 py-4 text-center">
                                <button type="button"
                                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-md shadow-indigo-100"
                                    data-id="{{ $facture->Id_Ligne }}"
                                    data-prest="{{ in_array($profilCode, ['RRSI', 'ADMIN']) ? $facture->Nom_Assure : $facture->prestataire?->nom }}"
                                    data-souscript="{{ in_array($profilCode, ['RRSI', 'ADMIN']) ? $facture->souscripteur?->nom : $facture->Reference_Facture }}"
                                    data-montant="{{ $facture->Montant_Ligne }}"
                                    data-reglement="{{ $facture->Montant_Reglement ?? 0 }}"
                                    data-rejete="{{ $facture->Montant_Ligne - ($facture->Montant_Reglement ?? 0) }}"
                                    data-demande="{{ $facture->Numero_demande }}"
                                    data-datedemande="{{ optional($facture->Date_Demande)->format('d/m/Y') }}"
                                    data-dttrans="{{ optional($facture->Date_Transmission)->format('d/m/Y') }}"
                                    onclick="openCorrectionModal(this)">
                                    Mise à Jour
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="correctionModal" class="fixed inset-0 z-50 overflow-y-auto hidden flex items-center justify-center p-4" x-show="$store.correctionFacture.showModal" x-transition>
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeCorrectionModal()"></div>
        
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden border border-slate-200">
            <div class="px-8 py-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="text-xl font-bold text-slate-900">Mise à jour des Montants</h3>
                <button onclick="closeCorrectionModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="p-8">
                <div class="p-4 mb-8 bg-amber-50 border border-amber-100 rounded-xl text-xs text-amber-800 font-medium flex items-center gap-3">
                    <svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>Les champs grisés sont informatifs et ne peuvent pas être modifiés.</span>
                </div>

                <form id="correctionForm" action="{{ route('correction.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="modalId">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-1">
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">@if($profilCode === 'RRSI' || $profilCode === 'ADMIN') Assuré @else Prestataire @endif</label>
                            <input type="text" id="modalPrest" class="w-full bg-gray-200 border border-slate-200 rounded-xl text-sm font-semibold py-2.5 px-4 text-slate-400 cursor-not-allowed outline-none" readonly>
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">@if($profilCode === 'RRSI' || $profilCode === 'ADMIN') Souscripteur @else N° Facture @endif</label>
                            <input type="text" id="modalSouscript" class="w-full bg-gray-200 border border-slate-200 rounded-xl text-sm font-semibold py-2.5 px-4 text-slate-400 cursor-not-allowed outline-none" readonly>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Date Enregistrement</label>
                            <input type="text" id="modalDtEnreg" class="w-full bg-gray-200 border border-slate-200 rounded-xl text-sm py-2.5 px-4 text-slate-400 cursor-not-allowed outline-none" readonly>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-indigo-600 uppercase mb-2 font-black">Montant Facture *</label>
                            <input type="number" name="MontantF" id="modalMontantF" class="w-full border-2 border-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 rounded-xl text-sm py-2.5 px-4 transition-all font-bold outline-none bg-white" required>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-emerald-600 uppercase mb-2 font-black">Montant Règlement *</label>
                            <input type="number" name="montantReglement" id="modalMontantReglement" class="w-full border-2 border-slate-300 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 rounded-xl text-sm py-2.5 px-4 transition-all font-bold outline-none bg-white" required>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-rose-600 uppercase mb-2">Montant Rejeté (Auto)</label>
                            <input type="number" name="montrejete" id="modalMontrejete" class="w-full bg-rose-50 border border-rose-100 rounded-xl text-sm font-black text-rose-400 py-2.5 px-4 text-center cursor-not-allowed outline-none" readonly>
                        </div>

                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-3 p-4  rounded-xl">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">N° Demande</label>
                                <input type="text" id="modalDemande" class="w-full bg-transparent border border-slate-200 bg-gray-200 p-0 text-sm font-semibold text-slate-400 cursor-not-allowed" readonly>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Date Demande</label>
                                <input type="text" id="modalDateDemande" class="w-full bg-transparent border border-slate-200 bg-gray-200 p-0 text-sm font-semibold text-slate-400 cursor-not-allowed" readonly>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Transmission</label>
                                <input type="text" id="modalDtTrans" class="w-full bg-transparent border border-slate-200 bg-gray-200 p-0 text-sm font-semibold text-slate-400 cursor-not-allowed" readonly>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Observation / Motif *</label>
                            <textarea name="motifcorretion" class="w-full border-2 border-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 rounded-xl text-sm p-4 transition-all outline-none bg-white" rows="3" placeholder="Saisir la raison obligatoire de cette correction..." required></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end mt-8 gap-3">
                        <button type="button" onclick="closeCorrectionModal()" class="px-6 py-2.5 rounded-xl text-slate-500 hover:bg-gray-200 font-bold transition-all">Annuler</button>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-2.5 rounded-xl font-bold shadow-lg transition-all active:scale-95">Valider</button>
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
    document.getElementById('modalMontrejete').value = montantF - montantReglement;
}

jQuery(document).ready(function($) {
    $('#correctionTable').DataTable({
        "scrollX": true,
        "language": { "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json" },
        "order": [[4, 'desc']], // Tri par montant par défaut
        "pageLength": 25
    });

    $('#modalMontantF, #modalMontantReglement').on('input', calculateRejete);
});
</script>

<style>
    /* Style DataTables */
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.4rem 0.8rem !important;
        border-radius: 0.5rem !important;
        border: 1px solid #e2e8f0 !important;
        background: white !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #4f46e5 !important;
        color: white !important;
        border-color: #4f46e5 !important;
    }
</style>
@endsection