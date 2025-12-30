@extends('../layout/' . $layout)  {{-- Ajustez votre layout principal (ex. 'app' ou 'side-menu') --}}

@section('subhead')
    <title>Courriers Santé en Instance - Gestion Factures</title>
    <script src="https://cdn.tailwindcss.com"></script>  {{-- Si pas dans layout --}}
    <script src="//unpkg.com/alpinejs" defer></script>  {{-- Optionnel pour modals avancés --}}
@endsection

@section('subcontent')
<div class="container mx-auto px-4 py-8 max-w-7xl">
    <!-- En-tête Page (Optionnel : comme vos dashboards précédents) -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white rounded-lg p-6 mb-8 text-center shadow-xl">
        <h1 class="text-3xl md:text-4xl font-bold flex items-center justify-center">
            <i class="fas fa-inbox mr-3 text-xl"></i>
            Courriers Santé en Instance
        </h1>
        <p class="text-blue-100 mt-2">Suivi des courriers non traités ou partiellement saisis</p>
    </div>

    <div class="grid grid-cols-1 gap-6">
        <!-- Card Principale avec Tableau -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header Card -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <h4 class="text-white font-bold text-xl md:text-2xl">Liste des Courriers Santé en Instance</h4>
                    <div class="mt-2 md:mt-0 md:w-1/3">
                        {{-- Bouton filtre/export si besoin : <button class="w-full md:w-auto bg-white bg-opacity-20 text-white px-4 py-2 rounded-lg">Filtrer</button> --}}
                    </div>
                </div>
            </div>

            <!-- Corps : Tableau Responsive -->
            <div class="overflow-x-auto">
                <table class="w-full table-auto border-collapse divide-y divide-gray-200" id="dtHorizontalVerticalExample">
                    <thead class="bg-blue-600 text-white">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Numéro Réception</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Référence</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Objet</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Expéditeur</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Date Réception</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Date Enregistrement</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Date Clôture Estimée</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Nbr. Jours de Retard</th>
                            <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($courriers as $cour)
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $cour->NumCour }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $cour->RefCour }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate" title="{{ $cour->objet }}">{{ $cour->Objet }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $cour->expediteur }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $cour->DateRecep }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $cour->DateEnreg }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $cour->DateClotureEstime }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $cour->statut < 0 ? 'text-red-600 font-bold' : 'text-green-600' }}">
                                    {{ $cour->nbr ?: 'À jour' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <button
    type="button"
    class="trans bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-lg transition-all duration-200 flex items-center justify-center mx-auto"
    data-refligne="{{ $cour->NumCour }}"
    data-prest="{{ $cour->expediteur }}"
    data-deb="{{ $cour->Objet }}"
    data-an="{{ $cour->annee }}"
    data-reffact="{{ $cour->CodeCour }}"
    title="Sélectionner ligne"
>
    <i class="fas fa-edit mr-1"></i> Éditer
</button>

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-gray-500 text-lg">Aucun courrier en instance pour le moment.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>

{{-- Modal Custom Tailwind (Fixed overlay, centered, backdrop) --}}
<div id="transM" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full max-h-screen overflow-y-auto">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div id="trans_ici" class="text-center">
                    {{-- Chargé via AJAX : Le formulaire sera inséré ici --}}
                    <div class="flex flex-col items-center justify-center p-8">
                        <i class="fas fa-spinner fa-spin text-3xl text-blue-600 mb-4"></i>
                        <p class="text-gray-600">Chargement du formulaire de saisie...</p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="$('#transM').addClass('hidden').fadeOut();">
                    Fermer
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Déconnexion (Simple, Tailwind) --}}
<div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <div class="flex justify-between items-center mb-4">
                <h5 class="text-lg font-bold text-gray-900">Voulez-vous vous déconnecter ?</h5>
                <button onclick="$('#logoutModal').removeClass('hidden').fadeOut();" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <p class="text-gray-600 mb-6">Cliquer sur "Déconnexion" pour fermer la session ouverte.</p>
            <div class="flex justify-end space-x-3">
                <button onclick="$('#logoutModal').removeClass('hidden').fadeOut();" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">Annuler</button>
                <a href="{{ route('logout') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Déconnexion</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- Styles Custom pour DataTables et Animations (Tailwind Compatible) -->
<style>
    /* DataTables Wrapper (Tailwind-like) */
    .dataTables_wrapper {
        @apply bg-white rounded-lg shadow overflow-hidden;
    }
    .dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate {
        @apply my-4;
    }
    .dataTables_length select, .dataTables_filter input {
        @apply px-2 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500;
    }
    .dataTables_paginate .paginate_button {
        @apply px-3 py-2 mx-1 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-blue-50 hover:border-blue-300;
    }
    .dataTables_paginate .paginate_button.current {
        @apply bg-blue-600 text-white border-blue-600;
    }
    .dataTables_wrapper .dataTables_scrollHead table thead th {
        @apply bg-blue-600 text-white;
    }
    .dataTables_wrapper .dataTables_scrollBody table tbody tr:hover {
        @apply bg-gray-50;
    }

    /* Sorting Icons (comme votre CSS original) */
    table.dataTable thead .sorting:after,
    table.dataTable thead .sorting:before,
    table.dataTable thead .sorting_asc:after,
    table.dataTable thead .sorting_asc:before,
    table.dataTable thead .sorting_asc_disabled:after,
    table.dataTable thead .sorting_asc_disabled:before,
    table.dataTable thead .sorting_desc:after,
    table.dataTable thead .sorting_desc:before,
    table.dataTable thead .sorting_desc_disabled:after,
    table.dataTable thead .sorting_desc_disabled:before {
        bottom: .5em;
        content: "";
        position: absolute;
        right: 1em;
        display: block;
        width: 0;
        height: 0;
        border-style: solid;
    }
    table.dataTable thead .sorting:before { border: 4px solid transparent; border-bottom-color: #bbb; top: 1.1em; }
    table.dataTable thead .sorting:after { border: 4px solid transparent; border-top-color: #bbb; top: 0.9em; }
    table.dataTable thead .sorting_asc:before { border-bottom-color: #333; }
    table.dataTable thead .sorting_asc:after { border-top-color: #333; }
    table.dataTable thead .sorting_desc:before { border-top-color: #333; }
    table.dataTable thead .sorting_desc:after { border-bottom-color: #333; }

    /* Animation Modal FadeIn */
    #transM:not(.hidden) {
        animation: fadeIn 0.3s ease-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }

    /* Responsive Table (Scroll horizontal mobile) */
    @media (max-width: 768px) {
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .dataTables_wrapper { font-size: 0.875rem; }
    }
</style>
@endpush

<!-- jQuery et DataTables (CDN comme avant) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>

<script>
/**
    * Script de gestion des courriers en instance
    * Gère l'ouverture du modal et le chargement AJAX du formulaire de saisie
    */

$(document).ready(function() {
         console.log('✅ Script courrier-instance chargé avec succès');

         // Configuration
         const CONFIG = {
              selectors: {
                       table: '#dtHorizontalVerticalExample',
                       modal: '#transM',
                       modalContent: '#trans_ici',
                       editButton: '.trans'
              },
              ajax: {
                       timeout: 10000,
                       retryAttempts: 2
              }
         };

         // ============================================
         // INITIALISATION DATATABLE
         // ============================================
         const dataTable = $(CONFIG.selectors.table).DataTable({
              scrollX: true,
              scrollY: "400px",
              scrollCollapse: true,
              paging: true,
              searching: true,
              ordering: true,
              info: true,
              responsive: true,
              pageLength: 25,
              lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Tous"]],
              order: [[6, 'desc']],
              language: {
                       url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
              },
              dom: '<"top"lf>rt<"bottom"ip><"clear">',
              drawCallback: function() {
                       // Réapplique les styles Tailwind après redraw
                       $('tbody tr').hover(
                            function() { $(this).addClass('bg-gray-50'); },
                            function() { $(this).removeClass('bg-gray-50'); }
                       );
              }
         });

         console.log('✅ DataTable initialisé avec', $(CONFIG.selectors.table + ' tbody tr').length, 'lignes');

         // ============================================
         // GESTION DU MODAL
         // ============================================

         /**
           * Affiche le modal avec un loader
           */
         function showModalWithLoader() {
              $(CONFIG.selectors.modalContent).html(`
                       <div class="flex flex-col items-center justify-center p-8">
                            <i class="fas fa-spinner fa-spin text-3xl text-blue-600 mb-4 animate-spin"></i>
                            <p class="text-gray-600 text-lg">Chargement du formulaire de saisie...</p>
                       </div>
              `);
              $(CONFIG.selectors.modal).removeClass('hidden').fadeIn(300);
         }

         /**
           * Affiche une erreur dans le modal
           */
         function showModalError(message, details = '') {
              $(CONFIG.selectors.modalContent).html(`
                       <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                            <i class="fas fa-exclamation-triangle text-red-500 text-3xl mb-3"></i>
                            <p class="text-red-700 font-medium text-lg">${message}</p>
                            ${details ? `<p class="text-gray-600 text-sm mt-2">${details}</p>` : ''}
                            <button onclick="$('#transM').addClass('hidden').fadeOut();"
                                          class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                     Fermer
                            </button>
                       </div>
              `);
         }

         /**
           * Charge le formulaire via AJAX
           */
         function loadCourrierForm(data) {
              console.log('📡 Envoi requête AJAX avec données:', data);

              // Construction de l'URL avec les paramètres
              const url = `/courriers/${data.numCour}/saisie-modal`;
              const params = new URLSearchParams({
                       expe: data.expe || '',
                       objet: data.objet || '',
                       annee: data.annee || new Date().getFullYear(),
                       codecour: data.codecour || ''
              }).toString();

              const fullUrl = `${url}?${params}`;
              console.log('🔗 URL complète:', fullUrl);

              $.ajax({
                       url: fullUrl,
                       type: 'GET',
                       timeout: CONFIG.ajax.timeout,
                       cache: false,
                       beforeSend: function(xhr) {
                            console.log('⏳ Requête AJAX en cours...');
                       },
                       success: function(result) {
                            console.log('✅ Réponse AJAX reçue');
                            $(CONFIG.selectors.modalContent).html(result);

                            // Scroll to top du modal
                            $(CONFIG.selectors.modal + ' .overflow-y-auto').animate({
                                     scrollTop: 0
                            }, 300);
                       },
                       error: function(xhr, status, error) {
                            console.error('❌ Erreur AJAX:', {
                                     status: xhr.status,
                                     statusText: xhr.statusText,
                                     error: error,
                                     response: xhr.responseText
                            });

                            let errorMessage = 'Erreur de chargement du formulaire';
                            let errorDetails = '';

                            switch (xhr.status) {
                                     case 404:
                                          errorMessage = 'Courrier non trouvé';
                                          errorDetails = 'Le courrier demandé n\'existe pas dans la base de données.';
                                          break;
                                     case 500:
                                          errorMessage = 'Erreur serveur';
                                          errorDetails = 'Une erreur s\'est produite sur le serveur. Veuillez réessayer.';
                                          break;
                                     case 0:
                                          errorMessage = 'Erreur de connexion';
                                          errorDetails = 'Impossible de contacter le serveur. Vérifiez votre connexion.';
                                          break;
                                     default:
                                          errorDetails = `Code erreur: ${xhr.status} - ${error}`;
                            }

                            showModalError(errorMessage, errorDetails);
                       }
              });
         }

         // ============================================
         // ÉVÉNEMENT CLIC SUR BOUTON ÉDITER
         // ============================================

         // ⚠️ IMPORTANT : Utiliser la délégation d'événements pour les éléments dynamiques
         $(document).on('click', CONFIG.selectors.editButton, function(e) {
              e.preventDefault();
              e.stopPropagation();

              console.log('🖱️ Bouton "Éditer" cliqué');

              // ✅ CORRECTION : Récupération correcte des attributs data-*
              // Les attributs HTML data-Refligne deviennent data-refligne en jQuery
              const $button = $(this);
              const courrierData = {
                       numCour: $button.attr('data-refligne') || $button.data('refligne'),
                       expe: $button.attr('data-prest') || $button.data('prest'),
                       objet: $button.attr('data-deb') || $button.data('deb'),
                       annee: $button.attr('data-an') || $button.data('an'),
                       codecour: $button.attr('data-reffact') || $button.data('reffact')
              };

              console.log('📋 Données récupérées du bouton:', courrierData);

              // Validation des données
              if (!courrierData.numCour) {
                       console.error('❌ Numéro de courrier manquant!');
                       alert('Erreur : Numéro de courrier manquant. Veuillez rafraîchir la page.');
                       return;
              }

              // Afficher le modal et charger le formulaire
              showModalWithLoader();
              loadCourrierForm(courrierData);
         });

         // ============================================
         // FERMETURE DU MODAL
         // ============================================

         // Fermeture au clic sur le fond
         $(CONFIG.selectors.modal).on('click', function(e) {
              if (e.target === this) {
                       $(this).addClass('hidden').fadeOut(300);
              }
         });

         // Fermeture avec la touche Échap
         $(document).on('keydown', function(e) {
              if (e.key === 'Escape' && !$(CONFIG.selectors.modal).hasClass('hidden')) {
                       $(CONFIG.selectors.modal).addClass('hidden').fadeOut(300);
              }
         });

         // ============================================
         // TOOLTIPS (Optionnel)
         // ============================================

         if (typeof $.fn.tooltip !== 'undefined') {
              $('[data-toggle="tooltip"]').tooltip();
         }

         console.log('✅ Tous les événements sont initialisés');
});

// ============================================
// FONCTIONS GLOBALES (pour les boutons inline)
// ============================================

/**
    * Ferme le modal (appelé depuis les boutons HTML)
    */
window.closeCourrierModal = function() {
         $('#transM').addClass('hidden').fadeOut(300);
};

/**
    * Ferme le modal de déconnexion
    */
window.closeLogoutModal = function() {
         $('#logoutModal').addClass('hidden').fadeOut(300);
};

/**
    * Ouvre le modal de déconnexion
    */
window.openLogoutModal = function() {
         $('#logoutModal').removeClass('hidden').fadeIn(300);
};
</script>
