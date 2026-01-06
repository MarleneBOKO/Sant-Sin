@extends('../layout/' . $layout)

@section('subhead')
    <title>Annulation de Facture</title>
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
                    Annulation de Facture
                </h2>
                <p class="text-slate-500 mt-1">Sélectionnez une facture pour procéder à son annulation définitive.</p>
            </div>
           
        </div>

        <div class="bg-white shadow-sm border border-slate-200 rounded-2xl overflow-hidden">
            <div class="p-6">
                <table id="annulationTable" class="w-full text-sm text-left">
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
                            <th class="px-6 py-4 font-semibold border-b text-center">Règlement</th>
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
                            <td class="px-6 py-4 text-center">{{ $facture->Annee_Facture }}</td>
                            <td class="px-6 py-4 text-center">
                                <button type="button"
                                    class="inline-flex items-center gap-2 bg-indigo-500 hover:bg-rose-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-md shadow-rose-100"
                                    data-id="{{ $facture->Id_Ligne }}"
                                    data-prest="{{ in_array($profilCode, ['RRSI', 'ADMIN']) ? $facture->Nom_Assure : $facture->prestataire?->nom }}"
                                    data-souscript="{{ in_array($profilCode, ['RRSI', 'ADMIN']) ? $facture->souscripteur?->nom : $facture->Reference_Facture }}"
                                    data-montant="{{ $facture->Montant_Ligne }}"
                                    data-reglement="{{ $facture->Montant_Reglement ?? 0 }}"
                                    data-rejete="{{ $facture->Montant_Ligne - ($facture->Montant_Reglement ?? 0) }}"
                                    data-demande="{{ $facture->Numero_demande }}"
                                    data-datedemande="{{ optional($facture->Date_Demande)->format('d/m/Y') }}"
                                    data-dttrans="{{ optional($facture->Date_Transmission)->format('d/m/Y') }}"
                                    onclick="openAnnulationModal(this)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    Annuler
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="annulationModal" class="fixed inset-0 z-50 overflow-y-auto hidden flex items-center justify-center p-4" x-show="$store.annulationFacture.showModal" x-transition>
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeAnnulationModal()"></div>
        
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden border border-slate-200">
            <div class="px-8 py-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="text-xl font-bold text-slate-900">Confirmation d'annulation</h3>
                <button onclick="closeAnnulationModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="p-8">
                <div class="p-4 mb-8 bg-rose-50 border border-rose-100 rounded-xl text-xs text-rose-800 font-medium flex items-center gap-3">
                    <svg class="h-5 w-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span>Attention : Cette action est irréversible. Les données de cette facture seront annulées.</span>
                </div>

                <form id="annulationForm" action="{{ route('annulation.update') }}" method="POST">
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
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Montant Facture</label>
                            <input type="text" id="modalMontantF" class="w-full bg-gray-200 border border-slate-200 rounded-xl text-sm py-2.5 px-4 text-slate-400 cursor-not-allowed outline-none" readonly>
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
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Motif de l'annulation *</label>
                            <textarea name="motifcorretion" class="w-full border-2 border-slate-300 focus:border-rose-500 focus:ring-4 focus:ring-rose-100 rounded-xl text-sm p-4 transition-all outline-none bg-white" rows="3" placeholder="Saisir obligatoirement la raison de l'annulation..." required></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end mt-8 gap-3">
                        <button type="button" onclick="closeAnnulationModal()" class="px-6 py-2.5 rounded-xl text-slate-500 hover:bg-gray-200 font-bold transition-all">Retour</button>
                        <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white px-8 py-2.5 rounded-xl font-bold shadow-lg shadow-rose-100 transition-all active:scale-95">Confirmer l'annulation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('annulationFacture', { showModal: false });
});

function openAnnulationModal(button) {
    document.getElementById('modalId').value = button.getAttribute('data-id');
    document.getElementById('modalPrest').value = button.getAttribute('data-prest');
    document.getElementById('modalSouscript').value = button.getAttribute('data-souscript');
    document.getElementById('modalMontantF').value = button.getAttribute('data-montant');
    document.getElementById('modalDemande').value = button.getAttribute('data-demande');
    document.getElementById('modalDateDemande').value = button.getAttribute('data-datedemande');
    document.getElementById('modalDtTrans').value = button.getAttribute('data-dttrans');
    document.getElementById('modalDtEnreg').value = new Date().toLocaleDateString('fr-FR');
    
    document.getElementById('annulationModal').classList.remove('hidden');
    Alpine.store('annulationFacture').showModal = true;
}

function closeAnnulationModal() {
    document.getElementById('annulationModal').classList.add('hidden');
    Alpine.store('annulationFacture').showModal = false;
}

jQuery(document).ready(function($) {
    $('#annulationTable').DataTable({
        "scrollX": true,
        "language": { "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json" },
        "order": [[6, 'desc']],
        "pageLength": 10
    });
});
</script>

<style>
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.4rem 0.8rem !important;
        border-radius: 0.5rem !important;
        border: 1px solid #0f0f0fff !important;
        background: white !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #e11d48 !important; /* Rose-600 */
        color: white !important;
        border-color: #e11d48 !important;
    }
</style>
@endsection