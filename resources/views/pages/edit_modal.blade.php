<!-- resources/views/pages/edit_modal.blade.php -->

<div class="flex justify-between items-center border-b pb-3 mb-6">
    <h4 class="text-xl font-semibold text-gray-800">
        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
        </svg>
        Modification de la facture
    </h4>
    <button type="button" class="text-gray-500 hover:text-red-500 text-2xl" onclick="closeModal()">
        &times;
    </button>
</div>

<form id="editFactureForm" method="POST" action="{{ route('ligne_suivi.update', $ligne->Id_Ligne) }}">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Colonne gauche -->
        <div class="space-y-6">
            <div class="border-l-4 border-blue-500 pl-4">
                <h5 class="text-lg font-medium text-blue-600 mb-4">Informations générales</h5>
                @if($typePartenaireActuel == 'prestataire')
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Prestataire (Tiers-Payant) :</label>
                            <select class="select2-edit w-full" name="Code_partenaire" required>
                                <option value="">Sélectionner un Prestataire</option>
                                @foreach($prestataires as $p)
                                    <option value="{{ $p->id }}" {{ $ligne->Code_partenaire == $p->id ? 'selected' : '' }}>
                                        {{ $p->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="Nom_Assure" value="">
                    @else
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Souscripteur :</label>
                            <select class="select2-edit w-full" name="Code_partenaire" required>
                                <option value="">Sélectionner un Souscripteur</option>
                                @foreach($souscripteurs as $s)
                                    <option value="{{ $s->id }}" {{ $ligne->Code_partenaire == $s->id ? 'selected' : '' }}>
                                        {{ $s->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nom de l'Assuré :</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                name="Nom_Assure" value="{{ $ligne->Nom_Assure }}" placeholder="Nom complet de l'assuré">
                        </div>
                    @endif

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">N° de Facture :</label>
                    <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                           name="Reference_Facture" value="{{ $ligne->Reference_Facture }}" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">N° de Réception :</label>
                    <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                           name="Numero_Reception" value="{{ $ligne->Numero_Reception }}">
                </div>

                @php
                    \Carbon\Carbon::setLocale('fr');
                @endphp

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Mois de Facture :</label>
                    <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                            name="Mois_Facture" required>
                        <option value="">Sélectionner un mois</option>
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $ligne->Mois_Facture == $i ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::createFromDate(null, $i, 1)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>

        <!-- Colonne droite -->
        <div class="space-y-6">
            <div class="border-l-4 border-green-500 pl-4">
                <h5 class="text-lg font-medium text-green-600 mb-4">Période et Montant</h5>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Date de Début :</label>
                    <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                           name="Date_Debut" value="{{ $ligne->Date_Debut?->format('Y-m-d') }}" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Date de Fin :</label>
                    <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                           name="Date_Fin" value="{{ $ligne->Date_Fin?->format('Y-m-d') }}" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Montant (FCFA) :</label>
                    <input type="number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                           name="Montant_Ligne" value="{{ $ligne->Montant_Ligne }}" min="0" step="1" required>
                </div>

                <!-- ✅ CHAMP RÉDACTEUR INTELLIGENT -->
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Rédacteur :

                    </label>
                    <div class="relative">
                        <input type="text"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-700 cursor-not-allowed"
                               value="{{ $ligne->redacteur_nom }}"
                               disabled
                               title="Le rédacteur sera automatiquement mis à jour lors de la sauvegarde">

                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                    </div>

                    @if(!$ligne->has_valid_redacteur && !empty($ligne->Redacteur))
                        <p class="mt-1 text-xs text-orange-600 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>

                        </p>
                    @else
                        <p class="mt-1 text-xs text-gray-500 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Sera automatiquement mis à jour avec: {{ auth()->user()->name }}
                        </p>
                    @endif
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Année de Facture :</label>
                    <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                            name="Annee_Facture">
                        @for($year = date('Y'); $year >= date('Y')-5; $year--)
                            <option value="{{ $year }}"
                                    {{ $ligne->Annee_Facture == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>
    </div>



    <!-- Actions -->
    <div class="flex justify-end gap-3 mt-8 pt-6 border-t">
        <button type="button" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-md transition-colors"
                onclick="closeModal()">
            <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            Annuler
        </button>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md transition-colors flex items-center"
                id="submitBtn">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3-3-3m3-3v12"></path>
            </svg>
            Enregistrer
        </button>
    </div>
</form>

<script>
$(document).ready(function() {
    console.log('Form modal script loaded');


    function initSelect2() {
        if ($('.select2-edit').length > 0) {
            $('.select2-edit').select2({
                width: '100%',
                dropdownParent: $('#editFactureModal') // IMPORTANT pour que le menu s'affiche par-dessus le modal
            });
        }
    }

    // 2. Initialisation au chargement du modal
    initSelect2();

    // 3. Mise à jour de initializeEditModal
    const originalInit = window.initializeEditModal;
    window.initializeEditModal = function(buttonData) {
        if (typeof originalInit === 'function') originalInit(buttonData);

        // On ré-initialise après avoir appliqué les restrictions
        initSelect2();
    };
    // Fonction pour gérer les champs modifiables selon le statut
    function applyFieldRestrictions(statut, estRejetee) {
        console.log('Applying restrictions for status:', statut, 'rejected:', estRejetee);

        // Réinitialiser tous les champs
        $('input, select, textarea').prop('disabled', false).removeClass('bg-gray-100 cursor-not-allowed');

        // Appliquer les restrictions selon le statut
        if (statut == 4 || estRejetee == '1') {
            // Clôturé ou rejeté - tout désactiver
            $('input, select, textarea').prop('disabled', true).addClass('bg-gray-100 cursor-not-allowed');
            $('#submitBtn').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
            return;
        }

        // Restrictions selon le statut
        switch(parseInt(statut)) {
            case 0: // Non traité - tout est modifiable
                break;

            case 1: // Traité - certains champs deviennent non modifiables
                $('input[name="Nom_Assure"], select[name="Code_partenaire"]').prop('disabled', true).addClass('bg-gray-100 cursor-not-allowed');
                $('input[name="Reference_Facture"]').prop('disabled', true).addClass('bg-gray-100 cursor-not-allowed');
                $('input[name="Numero_Reception"]').prop('disabled', true).addClass('bg-gray-100 cursor-not-allowed');
                break;

            case 2: // Transmis à la trésorerie - encore plus restrictif
                $('input[name="Nom_Assure"], select[name="Code_partenaire"]').prop('disabled', true).addClass('bg-gray-100 cursor-not-allowed');
                $('input[name="Reference_Facture"]').prop('disabled', true).addClass('bg-gray-100 cursor-not-allowed');
                $('input[name="Numero_Reception"]').prop('disabled', true).addClass('bg-gray-100 cursor-not-allowed');
                $('input[name="Date_Debut"], input[name="Date_Fin"]').prop('disabled', true).addClass('bg-gray-100 cursor-not-allowed');
                $('input[name="Montant_Ligne"]').prop('disabled', true).addClass('bg-gray-100 cursor-not-allowed');
                break;

            case 3: // Réglé - très restrictif
                $('input[name="Nom_Assure"], select[name="Code_partenaire"]').prop('disabled', true).addClass('bg-gray-100 cursor-not-allowed');
                $('input[name="Reference_Facture"]').prop('disabled', true).addClass('bg-gray-100 cursor-not-allowed');
                $('input[name="Numero_Reception"]').prop('disabled', true).addClass('bg-gray-100 cursor-not-allowed');
                $('input[name="Date_Debut"], input[name="Date_Fin"]').prop('disabled', true).addClass('bg-gray-100 cursor-not-allowed');
                $('input[name="Montant_Ligne"]').prop('disabled', true).addClass('bg-gray-100 cursor-not-allowed');
                $('select[name="Mois_Facture"], select[name="Annee_Facture"]').prop('disabled', true).addClass('bg-gray-100 cursor-not-allowed');
                break;

            case 5: // Transmis médecin
                $('input[name="Nom_Assure"], select[name="Code_partenaire"]').prop('disabled', true).addClass('bg-gray-100 cursor-not-allowed');
                $('input[name="Reference_Facture"]').prop('disabled', true).addClass('bg-gray-100 cursor-not-allowed');
                $('input[name="Numero_Reception"]').prop('disabled', true).addClass('bg-gray-100 cursor-not-allowed');
                break;

            case 6: // Retour médecin reçu - peut modifier certains champs pour traitement
                $('input[name="Nom_Assure"], select[name="Code_partenaire"]').prop('disabled', true).addClass('bg-gray-100 cursor-not-allowed');
                $('input[name="Reference_Facture"]').prop('disabled', true).addClass('bg-gray-100 cursor-not-allowed');
                $('input[name="Numero_Reception"]').prop('disabled', true).addClass('bg-gray-100 cursor-not-allowed');
                // Les dates et montant peuvent encore être ajustés si nécessaire
                break;
        }

        // Ajouter des tooltips explicatifs pour les champs désactivés
        $('input:disabled, select:disabled').each(function() {
            var currentTitle = $(this).attr('title') || '';
            var restrictionText = 'Champ non modifiable dans ce statut';
            if (currentTitle) {
                $(this).attr('title', currentTitle + ' - ' + restrictionText);
            } else {
                $(this).attr('title', restrictionText);
            }
        });
    }

    // Validation des dates
    $('input[name="Date_Fin"]').on('change', function() {
        var dateDebut = $('input[name="Date_Debut"]').val();
        var dateFin = $(this).val();

        if (dateDebut && dateFin && dateFin < dateDebut) {
            alert('La date de fin ne peut pas être antérieure à la date de début');
            $(this).val('');
        }
    });

    // Formatage du montant
    $('input[name="Montant_Ligne"]').on('input', function() {
        var value = $(this).val().replace(/[^\d]/g, '');
        $(this).val(value);
    });

    // Fonction pour initialiser les restrictions quand le modal s'ouvre
    window.initializeEditModal = function(buttonData) {
        console.log('Initializing edit modal with data:', buttonData);

        // Appliquer les restrictions de champs selon le statut
        if (buttonData && buttonData.statut !== undefined) {
            applyFieldRestrictions(buttonData.statut, buttonData.rejetee);
        }

        // Ajouter un indicateur visuel du statut
        var statusIndicator = '';
        var statusText = '';

        switch(parseInt(buttonData.statut)) {
            case 0: statusText = 'Non traité'; statusIndicator = '<span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Non traité</span>'; break;
            case 1: statusText = 'Traité'; statusIndicator = '<span class="bg-teal-100 text-teal-800 px-2 py-1 rounded text-xs">Traité</span>'; break;
            case 2: statusText = 'Transmis Tréso'; statusIndicator = '<span class="bg-indigo-100 text-indigo-800 px-2 py-1 rounded text-xs">Transmis Tréso</span>'; break;
            case 3: statusText = 'Réglé'; statusIndicator = '<span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Réglé</span>'; break;
            case 4: statusText = 'Clôturé'; statusIndicator = '<span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">Clôturé</span>'; break;
            case 5: statusText = 'Transmis Médecin'; statusIndicator = '<span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">Transmis Médecin</span>'; break;
            case 6: statusText = 'Retour Médecin'; statusIndicator = '<span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs">Retour Médecin</span>'; break;
        }

        if (buttonData.rejetee === '1') {
            statusIndicator = '<span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Rejeté</span>';
            statusText = 'Rejeté';
        }

        // Ajouter l'indicateur dans le titre du modal
        $('.flex.justify-between.items-center.border-b.pb-3.mb-6 h4').append(' ' + statusIndicator);

        // Ajouter une note explicative
        if (!$('#status-note').length) {
            var noteText = '';
            switch(parseInt(buttonData.statut)) {
                case 0: noteText = 'Tous les champs sont modifiables pour cette facture non traitée.'; break;
                case 1: noteText = 'Certains champs ne peuvent plus être modifiés car la facture est traitée.'; break;
                case 2: noteText = 'La plupart des champs sont verrouillés car la facture est transmise à la trésorerie.'; break;
                case 3: noteText = 'Presque tous les champs sont verrouillés car la facture est réglée.'; break;
                case 4: noteText = 'Cette facture est clôturée et ne peut plus être modifiée.'; break;
                case 5: noteText = 'Certains champs sont verrouillés car la facture est transmise au médecin.'; break;
                case 6: noteText = 'Certains champs sont verrouillés, mais vous pouvez ajuster les dates et montants si nécessaire.'; break;
            }

            if (buttonData.rejetee === '1') {
                noteText = 'Cette facture est rejetée et ne peut plus être modifiée.';
            }

            if (noteText) {
                $('.flex.justify-between.items-center.border-b.pb-3.mb-6').after(`
                    <div id="status-note" class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">${noteText}</p>
                            </div>
                        </div>
                    </div>
                `);
            }
        }
    };

    // Soumission du formulaire
    $('#editFactureForm').on('submit', function(e) {
        e.preventDefault();
    // 1. NETTOYAGE : Supprimer les anciens messages d'erreur et styles
    $('.error-msg').remove();
    $('input, select').removeClass('border-red-500 ring-1 ring-red-500');
        console.log('Form submitted');

        var submitBtn = $('#submitBtn');
        var originalContent = submitBtn.html();

        submitBtn.prop('disabled', true).html(`
            <svg class="animate-spin w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Enregistrement...
        `);


        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                console.log('Update success:', response);

                // Fermer le modal
                closeModal();

                // Message de succès
                const message = response.message || 'Facture modifiée avec succès';

                // Créer une notification Tailwind
                const notification = $(`
                    <div id="success-notification" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            ${message}
                        </div>
                    </div>
                `);

                $('body').append(notification);

                setTimeout(() => {
                    notification.fadeOut();
                    location.reload();
                }, 2000);
            },
           error: function(xhr) {
            console.error('Update error:', xhr);
            submitBtn.prop('disabled', false).html(originalContent);

            if (xhr.status === 422) { // Erreur de validation Laravel
                var errors = xhr.responseJSON.errors;

                // 2. AFFICHAGE DES ERREURS PAR CHAMP
                Object.keys(errors).forEach(function(field) {
                    // Trouver l'input ou le select par son nom
                    var input = $(`[name="${field}"]`);

                    // Ajouter une bordure rouge
                    input.addClass('border-red-500 ring-1 ring-red-500');

                    // Insérer le message d'erreur juste après le champ
                    input.after(`<p class="error-msg text-red-500 text-xs mt-1 font-medium">${errors[field][0]}</p>`);
                });

                // Optionnel : Scroll vers la première erreur
                $('.border-red-500').first().focus();

            } else {
                // Erreur système générique
                alert("Une erreur imprévue est survenue.");
            }
        }
        });
    });
});
</script>
