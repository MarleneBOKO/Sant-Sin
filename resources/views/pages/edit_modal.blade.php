<!-- Header du modal -->
<div class="flex justify-between items-center border-b pb-3 mb-6">
    <h4 class="text-xl font-semibold text-gray-800">
        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
        </svg>
        Modification de la facture
    </h4>

</div>

<!-- Formulaire -->
<form id="editFactureForm" method="POST" action="{{ route('ligne_suivi.update', $ligne->Id_Ligne) }}">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Colonne gauche -->
        <div class="space-y-6">
            <div class="border-l-4 border-blue-500 pl-4">
                <h5 class="text-lg font-medium text-blue-600 mb-4">
                    <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Informations générales
                </h5>

                @if($ligne->Code_Prestataire)

                <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Prestataire :</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                name="Code_Prestataire">
                            <option value="">Sélectionner un Prestataire</option>
                            @foreach($prestataires as $prestataire)
                                <option value="{{ $prestataire->id }}"
                                        {{ $ligne->Code_Prestataire == $prestataire->id ? 'selected' : '' }}>
                                    {{ $prestataire->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                @else
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nom de l'Assuré :</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                               name="Nom_Assure" value="{{ $ligne->Nom_Assure }}" placeholder="Nom de l'assuré">
                    </div>
                @endif

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">N° de Facture :</label>
                    <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                           name="Reference_Facture" value="{{ $ligne->Reference_Facture }}" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">N° de Recepetion :</label>
                    <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                           name="Reference_Facture" value="{{ $ligne->Numero_Reception }}" required>
                </div>

                        @php
                // S'assurer que Carbon utilise la locale française
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


                @if($ligne->Code_Souscripteur)
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Souscripteur :</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                name="Code_Souscripteur">
                            <option value="">Sélectionner un souscripteur</option>
                            @foreach($souscripteurs as $souscripteur)
                                <option value="{{ $souscripteur->id }}"
                                        {{ $ligne->Code_Souscripteur == $souscripteur->id ? 'selected' : '' }}>
                                    {{ $souscripteur->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
        </div>

        <!-- Colonne droite -->
        <div class="space-y-6">
            <div class="border-l-4 border-green-500 pl-4">
                <h5 class="text-lg font-medium text-green-600 mb-4">
                    <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Période et Montant
                </h5>

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
                    <div class="relative">
                        <input type="number" class="w-full px-3 py-2 pr-16 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                               name="Montant_Ligne" value="{{ $ligne->Montant_Ligne }}" min="0" step="1" required>

                    </div>
                </div>
                 <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Rédacteur :</label>
                    <div class="relative">
                       <input type="text" class="w-full px-3 py-2 pr-16 border border-gray-300 rounded-md bg-gray-100 text-gray-700"
       name="Redacteur" value="{{ $ligne->redacteur?->name }}" disabled>


                    </div>
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

    // Soumission du formulaire
    $('#editFactureForm').on('submit', function(e) {
        e.preventDefault();
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

                var errors = xhr.responseJSON?.errors;
                var message = 'Erreur lors de la modification';

                if (errors) {
                    var errorMessages = [];
                    Object.keys(errors).forEach(function(key) {
                        errorMessages.push(errors[key][0]);
                    });
                    message = errorMessages.join(', ');
                }

                // Notification d'erreur
                const errorNotification = $(`
                    <div id="error-notification" class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            ${message}
                        </div>
                    </div>
                `);

                $('body').append(errorNotification);
                setTimeout(() => errorNotification.fadeOut(), 5000);

                submitBtn.prop('disabled', false).html(originalContent);
            }
        });
    });
});
</script>
