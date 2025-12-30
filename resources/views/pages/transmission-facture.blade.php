@extends('../layout/' . $layout)

@section('subhead')
    <title>Transmission et Retour des Factures</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
@endsection

@section('subcontent')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white shadow-lg rounded-lg mb-6 overflow-hidden">
        {{-- Header --}}
        <div class="py-4 bg-gradient-to-r from-blue-600 to-blue-800 text-white shadow-md">
            <div class="flex items-center justify-between px-6">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-exchange-alt text-2xl"></i>
                    <h6 class="text-xl font-semibold">Transmission et Retour des Factures</h6>
                </div>

                {{-- Boutons d'action (désactivés par défaut) --}}
                <div class="flex space-x-3">
                    <button id="btn-transmit-batch" disabled
                        class="bg-blue-500 hover:bg-blue-600 text-white font-bold px-6 py-2 rounded-lg shadow-lg disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-paper-plane"></i>
                        <span>Transmettre </span>
                    </button>

                    <button id="btn-retour-batch" disabled
                        class="bg-green-500 hover:bg-green-600 text-white font-bold px-6 py-2 rounded-lg shadow-lg disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-reply"></i>
                        <span>Retour </span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-6">
            @if($factures->isEmpty())
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-blue-400 text-xl mr-3"></i>
                        <p class="text-blue-700">Aucune facture en attente de transmission ou de retour.</p>
                    </div>
                </div>
            @else
                {{-- Tableau des factures --}}
                <div class="overflow-x-auto rounded-lg shadow border border-gray-200">
                    <table id="facturesTable" class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-800 text-white">
                            <tr>
                                <th class="px-4 py-3 text-center whitespace-nowrap">
                                    <i class="fas fa-check-square"></i>
                                </th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Prestataire/Assuré</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">N° Facture</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Période</th>
                                <th class="px-4 py-3 font-semibold text-right whitespace-nowrap">Montant</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Date Enreg.</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Date Transmission</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Date Retour</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($factures as $facture)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-center">
                                    @if($facture->Statut_Ligne == 0)
                                        <input type="checkbox"
                                               class="checkbox-transmission w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 cursor-pointer"
                                               value="{{ $facture->Id_Ligne }}"
                                               data-ref="{{ $facture->Reference_Facture }}">
                                    @elseif($facture->Statut_Ligne == 5)
                                        <input type="checkbox"
                                               class="checkbox-retour w-5 h-5 text-green-600 rounded border-gray-300 focus:ring-green-500 cursor-pointer"
                                               value="{{ $facture->Id_Ligne }}"
                                               data-ref="{{ $facture->Reference_Facture }}">
                                    @else
                                        <span class="text-gray-300"><i class="fas fa-minus"></i></span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-medium">{{ $facture->partenaire->nom ?? $facture->Nom_Assure ?? 'N/A' }}</td>
                                <td class="px-4 py-3 font-mono text-blue-600">{{ $facture->Reference_Facture ?? 'N/A' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($facture->Date_Debut)->format('d/m/Y') }}
                                    <i class="fas fa-arrow-right text-xs text-gray-400 mx-1"></i>
                                    {{ \Carbon\Carbon::parse($facture->Date_Fin)->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-gray-700">
                                    {{ number_format($facture->Montant_Ligne ?? 0, 0, ',', ' ') }} <span class="text-xs">FCFA</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{ $facture->Date_Enregistrement ? \Carbon\Carbon::parse($facture->Date_Enregistrement)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-4 py-3 font-semibold whitespace-nowrap">
                                    @if($facture->datetransMedecin)
                                        <span class="text-blue-600">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            {{ \Carbon\Carbon::parse($facture->datetransMedecin)->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($facture->dateRetourMedecin)
                                        <span class="text-green-600">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            {{ \Carbon\Carbon::parse($facture->dateRetourMedecin)->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @php
                                        $statut = $facture->Statut_Ligne;
                                        if ($statut == 0) {
                                            $badge = 'bg-yellow-100 text-yellow-800 border-yellow-300';
                                            $icon = 'clock';
                                            $label = 'En attente';
                                        } elseif ($statut == 5) {
                                            $badge = 'bg-blue-100 text-blue-800 border-blue-300';
                                            $icon = 'paper-plane';
                                            $label = 'Transmise';
                                        } else {
                                            $badge = 'bg-green-100 text-green-800 border-green-300';
                                            $icon = 'check-double';
                                            $label = 'Retour reçu';
                                        }
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 rounded-full border {{ $badge }} text-xs font-semibold">
                                        <i class="fas fa-{{ $icon }} mr-1"></i>{{ $label }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

 {{-- MODAL UNIQUE pour Transmission et Retour --}}
<div id="modal-action" class="fixed inset-0 bg-black bg-opacity-60 hidden z-50 transition-opacity duration-300">
    <div class="absolute inset-0 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full mx-4 transform transition-all duration-300 scale-95" id="modal-content">
            {{-- Header du Modal --}}
            <div id="modal-header" class="flex items-center justify-between p-5 border-b">
                <h3 id="modal-title" class="text-xl font-bold flex items-center"></h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            {{-- Body du Modal --}}
            <form id="form-action">
                @csrf
                <input type="hidden" id="action-type" name="action_type">
                <input type="hidden" id="facture-ids" name="facture_ids">

                <div class="p-6 space-y-4">
                    {{-- Info sur les factures sélectionnées --}}
                    <div class="bg-gray-50 border-l-4 p-4 rounded" id="facture-info">
                        <p class="text-sm text-gray-700">
                            <i class="fas fa-file-invoice mr-2"></i>
                            <strong id="facture-count"></strong>
                        </p>
                        <ul id="facture-refs" class="mt-2 text-xs text-gray-600 max-h-32 overflow-y-auto"></ul>
                    </div>

                    {{-- Champ Date --}}
                    <div>
                        <label for="modal-date" class="block font-semibold mb-2 text-gray-700 text-base">
                            <i id="modal-date-icon" class="fas mr-2"></i>
                            <span id="modal-date-label"></span>
                        </label>
                        <input type="date" id="modal-date" name="action_date" required
                            value="{{ now()->format('Y-m-d') }}"
                             max="{{ now()->format('Y-m-d') }}"
                            class="w-full border-2 rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 transition-all">
                    </div>
                </div>

                {{-- Footer du Modal --}}
                <div class="flex justify-end space-x-3 p-5 border-t bg-gray-50 rounded-b-xl">
                    <button type="button" onclick="closeModal()"
                        class="px-6 py-2.5 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg font-semibold transition-colors">
                        <i class="fas fa-times mr-2"></i>Annuler
                    </button>
                    <button type="submit" id="modal-submit-btn"
                        class="px-6 py-2.5 rounded-lg font-semibold text-white transition-all shadow-lg hover:shadow-xl">
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.5rem 1rem !important;
        margin: 0 0.2rem !important;
        border-radius: 0.375rem !important;
    }

    #modal-action.show {
        display: flex !important;
    }

    #modal-action.show #modal-content {
        transform: scale(1);
    }
</style>
@endpush

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
    // Configuration AJAX
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // Initialiser DataTables
    $('#facturesTable').DataTable({
        "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json" },
        "order": [[5, 'desc']],
        "pageLength": 10,
        "columnDefs": [{ "orderable": false, "targets": [0] }]
    });

    // Variables globales
    let selectedTransmission = [];
    let selectedRetour = [];

    // ============================================
    // GESTION DES CHECKBOXES TRANSMISSION (Statut 0)
    // ============================================
    $(document).on('change', '.checkbox-transmission', function() {
        const id = $(this).val();
        const ref = $(this).data('ref');

        if ($(this).is(':checked')) {
            selectedTransmission.push({id: id, ref: ref});
        } else {
            selectedTransmission = selectedTransmission.filter(item => item.id != id);
        }

        updateButtonState('transmission');
    });

    // ============================================
    // GESTION DES CHECKBOXES RETOUR (Statut 5)
    // ============================================
    $(document).on('change', '.checkbox-retour', function() {
        const id = $(this).val();
        const ref = $(this).data('ref');

        if ($(this).is(':checked')) {
            selectedRetour.push({id: id, ref: ref});
        } else {
            selectedRetour = selectedRetour.filter(item => item.id != id);
        }

        updateButtonState('retour');
    });

    // ============================================
    // MISE À JOUR DE L'ÉTAT DES BOUTONS
    // ============================================
    function updateButtonState(type) {
        if (type === 'transmission') {
            const count = selectedTransmission.length;
            $('#count-transmission').text(count);
            $('#btn-transmit-batch').prop('disabled', count === 0);
        } else {
            const count = selectedRetour.length;
            $('#count-retour').text(count);
            $('#btn-retour-batch').prop('disabled', count === 0);
        }
    }

    // ============================================
    // CLIC BOUTON TRANSMISSION
    // ============================================
    $('#btn-transmit-batch').click(function() {
        if (selectedTransmission.length === 0) return;
        openModal('transmission', selectedTransmission);
    });

    // ============================================
    // CLIC BOUTON RETOUR
    // ============================================
    $('#btn-retour-batch').click(function() {
        if (selectedRetour.length === 0) return;
        openModal('retour', selectedRetour);
    });

    // ============================================
    // OUVERTURE DU MODAL
    // ============================================
    function openModal(type, factures) {
        $('#action-type').val(type);

        // Stocker les IDs
        const ids = factures.map(f => f.id).join(',');
        $('#facture-ids').val(ids);

        // Afficher le nombre et les références
        $('#facture-count').text(`${factures.length} facture(s) sélectionnée(s) :`);

        let refsList = '';
        factures.forEach(f => {
            refsList += `<li class="ml-4">• ${f.ref}</li>`;
        });
        $('#facture-refs').html(refsList);

        // Personnaliser selon le type
        if (type === 'transmission') {
            $('#modal-header').removeClass('border-green-500').addClass('border-blue-500');
            $('#facture-info').removeClass('border-green-500').addClass('border-blue-500');
            $('#modal-title').html('<i class="fas fa-paper-plane text-blue-600 mr-2"></i>Transmission au Médecin');
            $('#modal-date-icon').removeClass('fa-calendar-check text-green-600').addClass('fa-calendar-alt text-blue-600');
            $('#modal-date-label').text('Date de Transmission :');
            $('#modal-date').removeClass('border-green-300 focus:ring-green-500').addClass('border-blue-300 focus:ring-blue-500');
            $('#modal-submit-btn').removeClass('bg-green-600 hover:bg-green-700')
                .addClass('bg-blue-600 hover:bg-blue-700')
                .html('<i class="fas fa-paper-plane mr-2"></i>Transmettre');
        } else {
            $('#modal-header').removeClass('border-blue-500').addClass('border-green-500');
            $('#facture-info').removeClass('border-blue-500').addClass('border-green-500');
            $('#modal-title').html('<i class="fas fa-reply text-green-600 mr-2"></i>Retour du Médecin');
            $('#modal-date-icon').removeClass('fa-calendar-alt text-blue-600').addClass('fa-calendar-check text-green-600');
            $('#modal-date-label').text('Date de Retour :');
            $('#modal-date').removeClass('border-blue-300 focus:ring-blue-500').addClass('border-green-300 focus:ring-green-500');
            $('#modal-submit-btn').removeClass('bg-blue-600 hover:bg-blue-700')
                .addClass('bg-green-600 hover:bg-green-700')
                .html('<i class="fas fa-check-circle mr-2"></i>Enregistrer le Retour');
        }

        // Afficher le modal
        $('#modal-action').removeClass('hidden').addClass('show');
    }

    // ============================================
    // SOUMISSION DU FORMULAIRE
    // ============================================
    $('#form-action').submit(function(e) {
        e.preventDefault();

        const type = $('#action-type').val();
        const date = $('#modal-date').val();
        const ids = $('#facture-ids').val();

        if (!date) {
            alert('Veuillez sélectionner une date.');
            return;
        }

        const count = ids.split(',').length;
        const action = type === 'transmission' ? 'transmettre' : 'enregistrer le retour de';

        if (!confirm(`Confirmer l'action sur ${count} facture(s) ?`)) {
            return;
        }

        // Désactiver le bouton pendant le traitement
        $('#modal-submit-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>En cours...');

        const url = type === 'transmission'
            ? "{{ route('transmit.batch') }}"
            : "{{ route('retour.batch') }}";

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                factures: ids,
                date_action: date
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    closeModal();
                    location.reload();
                } else {
                    alert('Erreur : ' + response.message);
                    $('#modal-submit-btn').prop('disabled', false).html(type === 'transmission' ? '<i class="fas fa-paper-plane mr-2"></i>Transmettre' : '<i class="fas fa-check-circle mr-2"></i>Enregistrer');
                }
            },
            error: function(xhr) {
                console.error('Erreur AJAX:', xhr);
                alert('Erreur : ' + (xhr.responseJSON?.message || 'Une erreur est survenue'));
                $('#modal-submit-btn').prop('disabled', false).html(type === 'transmission' ? '<i class="fas fa-paper-plane mr-2"></i>Transmettre' : '<i class="fas fa-check-circle mr-2"></i>Enregistrer');
            }
        });
    });
});

// ============================================
// FERMETURE DU MODAL
// ============================================
function closeModal() {
    $('#modal-action').removeClass('show').addClass('hidden');
    $('#form-action')[0].reset();
    setTimeout(() => {
        $('#modal-content').css('transform', 'scale(0.95)');
    }, 300);
}

// Fermeture au clic sur le fond
$(document).on('click', '#modal-action', function(e) {
    if (e.target.id === 'modal-action') {
        closeModal();
    }
});
</script>
