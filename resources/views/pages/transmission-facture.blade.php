@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title ?? 'Transmission et Retour des Factures' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection



@section('subcontent')
<div class="container mx-auto px-4">
    <div class="bg-white shadow-lg rounded-lg mb-6 overflow-hidden">
        {{-- Header --}}
        <div class="py-3 bg-gradient-to-r from-blue-600 to-blue-800 text-white shadow-md">
            <div class="flex items-center justify-center space-x-2">
                <i class="fas fa-exchange-alt"></i>
                <h6 class="text-lg font-semibold text-center">{{ $title ?? 'Transmission et Retour des Factures' }}</h6>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-6">
            @if($factures->isEmpty())
                <div class="bg-blue-100 border border-blue-300 rounded-lg shadow-sm flex items-center p-4 space-x-3 text-blue-700">
                    <i class="fas fa-info-circle text-xl"></i>
                    <span>Aucune facture trouvée pour votre profil. <a href="{{ url()->previous() }}" class="underline font-semibold hover:text-blue-800">Retour à la liste précédente</a>.</span>
                </div>
            @else
                {{-- Section Transmission Multiple --}}
                <div id="transmit-section" class="bg-gray-50 p-4 rounded-lg border border-gray-200 shadow-sm mb-6">
                    <h6 class="text-blue-700 font-semibold mb-4 flex items-center space-x-2">
                        <i class="fas fa-broadcast"></i>
                        <span>Transmission Multiple</span>
                    </h6>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="date_transmission" class="block font-semibold mb-2">Date de Transmission :</label>
                            <input type="date" id="date_transmission" required
                                value="{{ now()->format('Y-m-d') }}"
                                class="w-full rounded-full shadow-sm border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div class="flex items-end">
                            <button id="transmit-btn" disabled
                                class="w-full bg-blue-600 text-white font-semibold rounded-full shadow hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2 py-3">
                                <i class="fas fa-paper-plane"></i>
                                <span>Transmettre </span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Tableau Responsive --}}
                <div class="overflow-x-auto rounded-lg shadow-sm border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200 table-auto text-sm">
                        <thead class="bg-gray-800 text-white">
                            <tr>
                                <th class="px-3 py-2 text-center whitespace-nowrap">
                                    Sélectionner
                                </th>
                                @if($isAdmin || !$isIndividuel)
                                    <th class="px-3 py-2 font-semibold whitespace-nowrap">
                                        Prestataire / Assuré
                                    </th>
                                    @if($isAdmin)

                                    @endif
                                    <th class="px-3 py-2 font-mono font-semibold whitespace-nowrap">
                                         N° Facture
                                    </th>
                                @else
                                    <th class="px-3 py-2 font-semibold whitespace-nowrap">
                                         Assuré
                                    </th>
                                    <th class="px-3 py-2 font-semibold whitespace-nowrap">
                                         Souscripteur
                                    </th>
                                @endif
                                <th class="px-3 py-2 font-semibold whitespace-nowrap">
                                     Période
                                </th>
                                <th class="px-3 py-2 font-semibold  whitespace-nowrap">
                                     Montant
                                </th>
                                <th class="px-3 py-2 font-semibold whitespace-nowrap">
                                     Date Enregistrement
                                </th>
                                <th class="px-3 py-2 font-semibold whitespace-nowrap">
                                     Date Transmission Estimée
                                </th>
                                <th class="px-3 py-2 font-semibold  whitespace-nowrap">
                                     Date Transmission Effective
                                </th>
                                <th class="px-3 py-2 font-semibold whitespace-nowrap">
                                     Date Retour Estimée
                                </th>
                                <th class="px-3 py-2 font-semibold whitespace-nowrap">
                                     Date Retour Effective
                                </th>
                                <th class="px-3 py-2 font-semibold whitespace-nowrap">
                                     Statut
                                </th>
                                <th class="px-3 py-2 font-semibold whitespace-nowrap">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($factures as $facture)
                            <tr class="align-middle hover:bg-gray-50">
                                <td class="text-center px-3 py-2 whitespace-nowrap">
                                    @if(($facture->stLigne ?? 0) == 3)
                                        <input type="checkbox"
                                               class="select-facture form-check-input"
                                               value="{{ $facture->id }}"
                                               data-ref="{{ $facture->ref ?? '' }}"
                                               data-prest="{{ $facture->prest ?? '' }}"
                                               data-deb="{{ $facture->deb ?? '' }}"
                                               data-fin="{{ $facture->fin ?? '' }}"
                                               data-mont="{{ $facture->mtligne ?? 0 }}"
                                               data-recept="{{ $facture->Numero_Reception ?? '' }}"
                                               class="w-5 h-5 cursor-pointer text-blue-600 rounded border-gray-300 focus:ring-blue-500"/>
                                    @else
                                        <span class="text-gray-400"><i class="fas fa-minus"></i></span>
                                    @endif
                                </td>

                                @if($isAdmin || !$isIndividuel)
                                    <td class="px-3 py-2 font-semibold whitespace-nowrap">{{ $facture->prest ?? 'N/A' }}</td>

                                    <td class="px-3 py-2 font-mono whitespace-nowrap">{{ $facture->ref ?? 'N/A' }}</td>
                                @else
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $facture->prest ?? 'N/A' }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $facture->ref ?? 'N/A' }}</td>
                                @endif

                                <td class="px-3 py-2 whitespace-nowrap">{{ ($facture->deb ?? '') }} au {{ ($facture->fin ?? '') }}</td>
                                <td class="px-3 py-2 text-right font-semibold whitespace-nowrap">{{ number_format($facture->mtligne ?? 0, 0, ',', ' ') }} FCFA</td>
                                <td class="px-3 py-2 whitespace-nowrap">{{ $facture->dtEnreg ?? '' }}</td>
                                <td class="px-3 py-2 whitespace-nowrap">{{ $facture->datetransestim ?? '' }}</td>
                                <td class="px-3 py-2  font-semibold whitespace-nowrap">{{ $facture->dtTransMed ?? 'Non transmis' }}</td>
                                <td class="px-3 py-2 whitespace-nowrap">{{ $facture->dateretestim ?? '' }}</td>
                                <td class="px-3 py-2 whitespace-nowrap">{{ $facture->dtRetourMed ?? 'En attente' }}</td>

                                <td class="px-3 py-2 whitespace-nowrap">
                                    @php
                                        $stLigne = $facture->stLigne ?? 0;
                                        $statutLabel = $stLigne == 3 ? 'Validée' :
                                                       ($stLigne == 5 ? 'Transmise - En Attente Retour' : 'Retour Reçu');
                                        $badgeColor = $stLigne == 3 ? 'bg-yellow-400 text-yellow-900' : ($stLigne == 5 ? 'bg-blue-400 text-blue-900' : 'bg-green-500 text-green-100');
                                        $icon = $stLigne == 3 ? 'hourglass-start' : ($stLigne == 5 ? 'truck' : 'check-circle');
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-1 rounded {{ $badgeColor }} text-xs font-semibold">
                                        <i class="fas fa-{{ $icon }} mr-1"></i>
                                        {{ $statutLabel }}
                                    </span>
                                </td>

                                <td class="px-3 py-2 whitespace-nowrap space-x-1 flex  justify-center">
                                    {{-- Boutons conditionnels --}}
                                    @if(($facture->stLigne ?? 0) == 3)
                                        <a href="#"
                                           class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded shadow text-xs flex items-center space-x-1"
                                           data-toggle="modal" data-target="#modal_transmit_{{ $facture->id }}">
                                            <i class="fas fa-paper-plane"></i>
                                        </a>

                                     @elseif(($facture->stLigne ?? 0) == 5)
                                    <a href="#"
                                    class="retour bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded shadow text-xs flex items-center space-x-1"
                                    data-id="{{ $facture->id }}">
                                        <i class="fas fa-reply"></i><span>Saisir Retour</span>
                                    </a>
                                        @endif


                                    @if($isAdmin)
                                   <a href="{{ route('ligne_suivi.edit', $facture->id) }}"
                                       class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded shadow text-xs flex items-center space-x-1">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                </td>
                            </tr>

                            {{-- Modal Transmettre --}}
                            <div id="modal_transmit_{{ $facture->id }}"
                                 class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-300"
                                 aria-hidden="true"
                                 role="dialog"
                                 aria-modal="true"
                                 tabindex="-1">
                                <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
                                    <button type="button"
                                            class="absolute top-3 right-3 text-gray-600 hover:text-gray-900"
                                            onclick="document.getElementById('modal_transmit_{{ $facture->id }}').classList.add('opacity-0'); document.getElementById('modal_transmit_{{ $facture->id }}').classList.add('pointer-events-none');"
                                            aria-label="Fermer la fenêtre modale">
                                        <i class="fas fa-times"></i>
                                    </button>

                                    <h3 class="text-xl font-semibold mb-4">Transmettre la Facture {{ $facture->ref }}</h3>
                                    <p class="mb-6">Êtes-vous sûr de vouloir transmettre cette facture ?</p>

                                    <form action="/factures/{{ $facture->id }}/transmit" method="POST">
                                        @csrf
                                        <div class="mb-4">
                                            <label for="transmission_date_{{ $facture->id }}" class="block font-semibold mb-2">Date de Transmission :</label>
                                            <input type="date" id="transmission_date_{{ $facture->id }}" name="transmission_date" value="{{ now()->format('Y-m-d') }}" required
                                                   class="w-full border border-gray-300 rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                        </div>

                                        <div class="flex justify-end space-x-4">
                                            <button type="button"
                                                    class="bg-gray-400 hover:bg-gray-500 text-white rounded-full px-4 py-2"
                                                    onclick="document.getElementById('modal_transmit_{{ $facture->id }}').classList.add('opacity-0'); document.getElementById('modal_transmit_{{ $facture->id }}').classList.add('pointer-events-none');">
                                                Annuler
                                            </button>
                                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white rounded-full px-4 py-2">Transmettre</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            {{-- Modal Retour --}}

                            @empty
                            <tr>
                                <td colspan="13" class="text-center py-6 text-gray-500">
                                    Aucune facture disponible.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
<div id="retour_ici"></div>

@endsection

@push('styles')
<style>
    .dtHorizontalVerticalExampleWrapper { max-width: 300px; margin: 0 auto; }
    table.dataTable thead .sorting:after, table.dataTable thead .sorting:before,
    table.dataTable thead .sorting_asc:after, table.dataTable thead .sorting_asc:before,
    table.dataTable thead .sorting_asc_disabled:after, table.dataTable thead .sorting_asc_disabled:before,
    table.dataTable thead .sorting_desc:after, table.dataTable thead .sorting_desc:before,
    table.dataTable thead .sorting_desc_disabled:after, table.dataTable thead .sorting_desc_disabled:before {
        bottom: .5em;
    }
    #transmit-section { display: none; margin-top: 10px; animation: slideIn 0.3s ease-out; }
    @keyframes slideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    .action-cell { white-space: nowrap; }
    .btn-hover { transition: all 0.2s ease; }
    .btn-hover:hover { transform: translateY(-1px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    .badge-custom { font-size: 0.75rem; padding: 0.25rem 0.5rem; }
</style>
@endpush


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>


<script>
$(document).ready(function() {

    console.log('Page Transmission chargée. Factures count:', {{ $factures->count() ?? 0 }}); // Debug
        console.log('DataTables dispo ?', typeof $.fn.DataTable);
        var colCount = $('#dtHorizontalVerticalExample thead tr th').length;
            var nonOrderableTargets = [0]; // Toujours la 1ère colonne (checkbox)
            if (colCount > 13) {
                nonOrderableTargets.push(13);
            } else if (colCount === 13) {
                nonOrderableTargets.push(12);
            } else if (colCount === 12) {
                nonOrderableTargets.push(11);
            }
            // Initialiser DataTables (Responsive, Français, Colonnes Non-Triables)
            $('#dtHorizontalVerticalExample').DataTable({
                "scrollX": true,
                "scrollY": "60vh", // Hauteur adaptative
                "scrollCollapse": true,
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "order": [[7, 'desc']], // Trier par Date Transmission Effective
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json"

                },
                "columnDefs": [
                    { "orderable": false, "targets": nonOrderableTargets },
                    { "className": "text-center", "targets": [0] },
                    { "className": "text-right", "targets": [5] }
                ],
                "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
            });

            // CSRF Token pour AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // ✅ FIX Checkboxes : Délégation Event + Debug (pour stLigne==3 - Validée)
            let selectedIds = [];
            console.log('Checkboxes disponibles:', $('.select-facture').length); // Debug : Si 0, pas de lignes validées (stLigne=3)

            // ✅ DÉLÉGATION : Évite interférence DataTables (events sur lignes paginées)
            $(document).on('change', '.select-facture', function() {
                const $checkbox = $(this);
                const id = $checkbox.val();
                const isChecked = $checkbox.is(':checked');
                console.log('Checkbox changée (délégué):', {id, isChecked}); // Debug détaillé

                if (isChecked) {
                    if (!selectedIds.includes(id)) {
                        selectedIds.push(id);
                        console.log('Ajouté à selectedIds:', selectedIds); // Debug array
                    }
                } else {
                    selectedIds = selectedIds.filter(selectedId => selectedId != id);
                    console.log('Retiré de selectedIds:', selectedIds); // Debug array
                }

                const hasSelection = selectedIds.length > 0;
                console.log('Has selection?', hasSelection, 'Length:', selectedIds.length); // Debug activation

                // ✅ Toggle Section + Animation
                if (hasSelection) {
                    $('#transmit-section').fadeIn(300).show(); // Force show si caché
                } else {
                    $('#transmit-section').fadeOut(300).hide();
                }

                // ✅ Activation Bouton : Force disabled=false si hasSelection
                $('#transmit-btn').prop('disabled', !hasSelection)
                                .attr('data-factures', selectedIds.join(','))
                                .html(`<i class="fas fa-paper-plane mr-2"></i>Transmettre`);
                console.log('Bouton disabled?', $('#transmit-btn').prop('disabled')); // Debug bouton
            });

            // ✅ Check Initial : Si checkboxes déjà cochées au load (rare, mais robuste)
            $('.select-facture:checked').each(function() {
                const id = $(this).val();
                if (!selectedIds.includes(id)) {
                    selectedIds.push(id);
                }
            });
            if (selectedIds.length > 0) {
                console.log('Sélections initiales détectées:', selectedIds);
                $('#transmit-section').fadeIn(300).show();
                $('#transmit-btn').prop('disabled', false).attr('data-factures', selectedIds.join(','))
                                .html(`<i class="fas fa-paper-plane mr-2"></i>Transmettre ${selectedIds.length} sélectionnée(s)`);
            }

            // Si pas de checkboxes (toutes transmises/terminées), affiche un message
            if ($('.select-facture').length === 0) {
                console.log('Aucune ligne validée (stLigne=3) ; pas de checkboxes pour transmission.');
                // Optionnel : Alert ou message UI
                // alert('Aucune facture validée à transmettre. Focus sur retours (status 5) si disponibles.');
            }

            // Bouton Batch Transmission (avec Confirmation et Erreur Handling)
            $('#transmit-btn').click(function() {
                const factures = $(this).data('factures');
                const dateTransmission = $('#date_transmission').val();
                console.log('Batch Transmission cliquée:', {factures, dateTransmission, selectedIds}); // Debug

                if (!factures || factures === '' || selectedIds.length === 0) {
                    alert('Veuillez sélectionner au moins une facture validée.');
                    return;
                }
                if (!dateTransmission) {
                    alert('Veuillez sélectionner une date de transmission.');
                    $('#date_transmission').focus();
                    return;
                }

                if (!confirm(`Confirmez la transmission de ${selectedIds.length} facture(s) validée(s) le ${dateTransmission} ? `)) {
                    return;
                }

                $.ajax({
                url: "{{ route('transmit.batch') }}", // Fallback si route non définie
                    method: 'POST',
                    data: {
                        factures: factures,
                        date_transmission: dateTransmission
                    },
                    success: function(response) {
                        console.log('Transmission succès:', response); // Debug
                        if (response.success) {
                            alert(response.message || 'Transmission réussie !');
                            location.reload(); // Recharger pour mise à jour (stLigne=5)
                        } else {
                            alert('Erreur: ' + (response.message || 'Transmission échouée'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Erreur AJAX Transmission:', {status, error, response: xhr.responseText}); // Debug détaillé
                        const msg = xhr.responseJSON?.message || 'Erreur lors de la transmission (' + xhr.status + '). Vérifiez la console.';
                        alert(msg);
                    }
                });
            });

            // ✅ FIX Modals : Délégation AJAX Chargement avec Debug et Fallback
            // Transmission Individuelle (clic "Transmettre" - stLigne=3)
            $(document).on('click', '.trans', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                console.log('Clic Transmettre (délégué):', {id, target: '#transM'}); // Debug

                const url = "{{ route('transmission.modal', ['id' => ':id']) }}".replace(':id', id) || `/transmission/modal/${id}`; // Fallback
                $.ajax({
                    url: url,
                    method: 'GET',
                    data: { id: id },
                    success: function(result) {
                        console.log('Modal Transmission chargé:', result.length); // Debug
                        $("#trans_ici").html(result);
                        $('#transM').modal('show'); // Ouvrir modal
                    },
                    error: function(xhr) {
                        console.error('Erreur chargement Modal Transmission:', xhr.responseText);
                        alert('Erreur chargement modal (' + xhr.status + '). Vérifiez routes/controller.');
                        $("#trans_ici").html('<div class="alert alert-danger">Erreur chargement. ID: ' + id + '</div>');
                        $('#transM').modal('show');
                    }
                });
            });

        function showModal() {
            $('#retourM').removeClass('hidden');  // Retire la classe hidden pour afficher
        }
        function hideModal() {
            $('#retourM').addClass('hidden');  // Ajoute la classe hidden pour cacher
        }

        $(document).on('click', '.retour', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            console.log('🔵 Clic Saisir Retour:', {id});
            console.log('🔍 Conteneur #retour_ici existe?', $('#retour_ici').length > 0);

            const url = `/${id}/retour-modal`;
            $.ajax({
                url: url,
                method: 'GET',
                success: function(result) {
                    console.log('✅ Modal Retour chargé:', result.length, 'caractères');
                    console.log('📝 Contenu (100 premiers chars):', result.substring(0, 100));

                    $("#retour_ici").html(result);  // Injecte le contenu

                    console.log('🔍 Modal #retourM existe après injection?', $('#retourM').length > 0);

                    if ($('#retourM').length === 0) {
                        console.error('❌ ERREUR: Modal #retourM non trouvé dans le DOM après injection!');
                        console.log('Contenu de #retour_ici:', $('#retour_ici').html().substring(0, 200));
                        alert('Erreur: Modal non trouvé dans le HTML retourné');
                        return;
                    }

                    // ✅ AFFICHAGE DU MODAL
                    $('#retourM').removeClass('hidden opacity-0 pointer-events-none');
                    $('#retourM').addClass('flex');

                    console.log('👁️ Classes du modal après affichage:', $('#retourM').attr('class'));
                    console.log('📏 Position/Taille du modal:', {
                        display: $('#retourM').css('display'),
                        visibility: $('#retourM').css('visibility'),
                        zIndex: $('#retourM').css('z-index'),
                        width: $('#retourM').width(),
                        height: $('#retourM').height()
                    });
                },
                error: function(xhr) {
                    console.error('❌ Erreur AJAX Modal Retour:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        response: xhr.responseText.substring(0, 500)
                    });
                    alert('Erreur chargement modal retour (' + xhr.status + '): ' + xhr.statusText);
                }
            });
        });

// ✅ FERMETURE MODAL (Délégation)
$(document).on('click', '.close-retour-modal', function() {
    $('#retourM').addClass('hidden opacity-0 pointer-events-none');
    $('#retourM').removeClass('flex');
    $('#retour_ici').empty(); // Vide le contenu
    console.log('Modal fermé via bouton');
});

// ✅ SUBMIT RETOUR (Délégation avec data-ligne-id)
// ✅ SUBMIT RETOUR (Délégation avec data-ligne-id)
$(document).on('click', '.submit-retour-btn', function() {
    const form = $(this).closest('form');
    const ligneId = form.find('input[name="facture_id"]').val();

    if (!ligneId || !$.isNumeric(ligneId)) {
        console.error('❌ ID invalide:', ligneId);
        alert('Erreur: ID de la facture manquant ou invalide');
        return;
    }

    console.log('✅ Formulaire trouvé:', form.length > 0 ? form.attr('id') || 'form sans ID' : 'Aucun formulaire');

    // Vérifier si le formulaire existe et est valide
    if (form.length === 0) {
        alert('Erreur: Formulaire non trouvé.');
        return;
    }

    // ✅ FIX : Utiliser form[0] pour accéder à l'élément DOM natif
    if (!form[0].checkValidity()) {
        form[0].reportValidity();  // Affiche les erreurs natives du navigateur
        return;
    }

    const formData = new FormData(form[0]);
    const submitBtn = this;  // ✅ Référence directe au bouton cliqué

    // Désactiver le bouton
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> En cours...';

    console.log('📤 Envoi AJAX vers:', `/${ligneId}/retour`);

    $.ajax({
        url: `/${ligneId}/retour`,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            console.log('✅ Submit retour succès:', response);
            if (response.success) {
                $('#retourM').addClass('hidden opacity-0 pointer-events-none');
                $('#retourM').removeClass('flex');
                $('#retour_ici').empty();
                alert(response.message || 'Retour enregistré avec succès');
                setTimeout(() => location.reload(), 500);
            } else {
                alert('Erreur: ' + (response.message || 'Une erreur est survenue'));
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check mr-1"></i> Valider le Retour';
            }
        },
        error: function(xhr) {
            console.error('❌ Erreur submit retour:', xhr);
            const errorMsg = xhr.responseJSON?.message || xhr.statusText || 'Erreur inconnue';
            alert('Erreur:\n' + errorMsg);
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check mr-1"></i> Valider le Retour';
        }
    });
});

            // Modification Individuels (clic bouton edit pour #transMm)
            $(document).on('click', '[data-target="#transMm"]', function(e) {
                e.preventDefault();
                const id = $(this).data('refligne');
                console.log('Clic Modifier Indiv (délégué):', {id, target: '#transMm'}); // Debug

                const url = "{{ route('edit.modal', ['id' => ':id']) }}".replace(':id', id) || `/edit/modal/${id}`; // Fallback
                $.ajax({
                    url: url,
                    method: 'GET',
                    data: {
                        id: id,
                        tpe: 'indiv'
                    },
                    success: function(result) {
                        console.log('Modal Edit Indiv chargé'); // Debug
                        $("#transm_ici").html(result);
                        $('#transMm').modal('show');
                    },
                    error: function(xhr) {
                        console.error('Erreur chargement Modal Edit Indiv:', xhr.responseText);
                        alert('Erreur chargement modal modification (' + xhr.status + ').');
                    }
                });
            });

            // Modification Tiers-Payant (clic bouton edit pour #transMmp)
            $(document).on('click', '[data-target="#transMmp"]', function(e) {
                e.preventDefault();
                const id = $(this).data('refligne');
                console.log('Clic Modifier TP (délégué):', {id, target: '#transMmp'}); // Debug

                const url = "{{ route('edit.modal', ['id' => ':id']) }}".replace(':id', id) || `/edit/modal/${id}`; // Fallback
                $.ajax({
                    url: url,
                    method: 'GET',
                    data: {
                        id: id,
                        tpe: 'tp'
                    },
                    success: function(result) {
                        console.log('Modal Edit TP chargé'); // Debug
                        $("#transmp_ici").html(result);
                        $('#transMmp').modal('show');
                    },
                    error: function(xhr) {
                        console.error('Erreur chargement Modal Edit TP:', xhr.responseText);
                        alert('Erreur chargement modal modification (' + xhr.status + ').');
                    }
                });
            });

            // ✅ Submit pour Modals (Délégation - Fonctions Globales)
            // Transmission Submit (individuel)
            $(document).on('click', '#submit-trans', function() {
                const id = $(this).data('id') || null; // Si passé via data-id
                const form = $('#trans_ici form')[0]; // Formulaire dans modal
                if (!form) {
                    alert('Formulaire transmission non trouvé.');
                    return;
                }

                const formData = new FormData(form);
                if (id) formData.append('id', id);

                $.ajax({
                    url: "{{ route('transmit.individual', ':id') }}".replace(':id', id),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log('Submit Transmission succès:', response);
                        if (response.success) {
                            alert(response.message || 'Transmission réussie !');
                            $('#transM').modal('hide'); // Fermer modal
                            location.reload(); // Recharger pour mise à jour
                        } else {
                            alert('Erreur: ' + (response.message || 'Transmission échouée'));
                        }
                    },
                    error: function(xhr) {
                        console.error('Erreur Submit Transmission:', xhr.responseText);
                        alert('Erreur transmission (' + xhr.status + '): ' + (xhr.responseJSON?.message || 'Vérifiez la console.'));
                    }
                });
            });



            // Modification Submit (Générique pour edit-indiv/edit-tp)
            $(document).on('click', '#submit-edit-indiv, #submit-edit-tp', function() {
                const modal = $(this).closest('.modal');
                const formId = modal.find('form').attr('id') || 'edit-form';
                const form = $(`#${formId}`)[0] || modal.find('form')[0];
                if (!form) {
                    alert('Formulaire non trouvé.');
                    return;
                }

                    const formData = new FormData(form);
                const id = formData.get('id') || $(this).data('id'); // ID du formulaire ou data-id
                if (!id) {
                    alert('ID de facture manquant.');
                    return;
                }

                // URL avec ID dynamique (route pour update ligne_suivi)
                const url = "{{ route('ligne_suivi.update', ':id') }}".replace(':id', id) || `/ligne-suivi/${id}`; // Fallback si route non définie
                formData.append('_method', 'PUT'); // Spoofing pour PUT via POST

                console.log('Submit Edit cliqué (délégué):', {id, url, tpe: formData.get('tpe')}); // Debug

                $.ajax({
                    url: url,
                    method: 'POST', // PUT spoofé via POST
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log('Submit Edit succès:', response);
                        if (response.success) {
                            alert(response.message || 'Modifications enregistrées !');
                            modal.modal('hide'); // Fermer modal
                            location.reload(); // Recharger pour mise à jour
                        } else {
                            alert('Erreur: ' + (response.message || 'Mise à jour échouée'));
                        }
                    },
                    error: function(xhr) {
                        console.error('Erreur Submit Edit:', {status: xhr.status, response: xhr.responseText});
                        const msg = xhr.responseJSON?.message || 'Erreur lors de la mise à jour (' + xhr.status + '). Vérifiez la console.';
                        alert(msg);
                    }
                });
            });


});
</script>
