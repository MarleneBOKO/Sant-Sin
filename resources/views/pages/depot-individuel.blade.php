@extends('../layout/' . $layout)

@section('subhead')
    <title>Liste des Courriers Individuels</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="//unpkg.com/alpinejs" defer></script>
    <style>
        [x-cloak] { display: none !important; }
        .modal-overlay {
            backdrop-filter: blur(2px);
            animation: fadeIn 0.2s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .error-details {
            max-height: 200px;
            overflow-y: auto;
        }
        /* CSS pour le loader (ajouté pour le spinner de chargement) */
        .loader {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
@endsection

@section('subcontent')
<div x-data="{
    openCreateModal: false,
    openSaisieModal: false,
    loadingSaisie: false,
    selectedCourierId: null,
    errorMessage: null,

    // Méthode pour ouvrir le modal et charger le formulaire via AJAX
    openAndPopulateForm(numCour) {
        this.selectedCourierId = numCour;
        this.openSaisieModal = true;
        this.loadingSaisie = true;
        this.errorMessage = null;
        const contentDiv = document.getElementById('saisieModalContent');

        // Requête AJAX pour charger le partial
        fetch(`/courriers/${numCour}/saisie-facture-modal`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            return response.text(); // Retourne HTML brut pour injection
        })
        .then(html => {
            contentDiv.innerHTML = html;
            this.loadingSaisie = false;
            // Initialiser les scripts du partial si nécessaire (ex. : pour les selects)
            if (typeof initPartialScripts === 'function') {
                initPartialScripts(); // Optionnel : si vous avez des init JS dans le partial
            }
        })
        .catch(error => {
            console.error('Erreur chargement formulaire:', error);
            this.errorMessage = 'Impossible de charger le formulaire. Vérifiez votre connexion ou réessayez. Détails: ' + error.message;
            this.loadingSaisie = false;
            contentDiv.innerHTML = ''; // Vide le contenu en cas d'erreur
        });
    },

    // Méthode pour fermer/reset le modal saisie
    resetSaisieModal() {
        this.openSaisieModal = false;
        this.loadingSaisie = false;
        this.selectedCourierId = null;
        this.errorMessage = null;
        document.getElementById('saisieModalContent').innerHTML = ''; // Vide le contenu
    }
}">


    <!-- Header de la page -->
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-2xl font-semibold">Liste des Courriers Individuels en Instance</h3>
        <button @click="openCreateModal = true" class="btn btn-success flex items-center gap-2">
            <i class="fas fa-plus"></i> Nouveau Dépôt
        </button>
    </div>

    <!-- Messages de session -->
    @if(session('success'))
        <div class="alert alert-success mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <!-- Tableau des courriers -->
    <div class="overflow-auto bg-white rounded-lg shadow">
        <table class="min-w-full border text-center" id="courriersTable">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left">Numero Réception</th>
                    <th class="px-4 py-2 text-left">Objet</th>
                    <th class="px-4 py-2 text-left">Déposant</th>
                    <th class="px-4 py-2 text-left">Structure</th>
                    <th class="px-4 py-2 text-left">Pour le compte De</th>
                    <th class="px-4 py-2 text-center">Date Réception</th>
                    <th class="px-4 py-2 text-center">Date Enregistrement</th>
                    <th class="px-4 py-2 text-center" hidden>Date Cloture estimée</th>
                    <th class="px-4 py-2 text-center">Saisir Facture</th>
                    <th class="px-4 py-2 text-center">Imprimer la Fiche de dépôt</th>
                </tr>
            </thead>
            <tbody>
                @forelse($courriers as $courrier)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-4 py-2 font-medium">{{ $courrier->CodeCour }}</td>
                    <td class="px-4 py-2 text-left">
                        <div class="max-w-xs truncate" title="{{ $courrier->motif }}">
                            {{ $courrier->motif }}
                        </div>
                    </td>
                    <td class="px-4 py-2">{{ $courrier->NomDeposant }}</td>
                    <td class="px-4 py-2">{{ $courrier->structure }}</td>
                    <td class="px-4 py-2">
                        <div class="max-w-xs truncate" title="{{ $courrier->Comptede }}">
                            {{ $courrier->Comptede }}
                        </div>
                    </td>
                    <td class="px-4 py-2 text-center">
                        {{ $courrier->datereception ? $courrier->datereception->format('d/m/Y') : 'N/A' }}
                    </td>
                    <td class="px-4 py-2 text-center">
                        {{ $courrier->datesysteme ? $courrier->datesysteme->format('d/m/Y') : 'N/A' }}
                    </td>
                    <td class="px-4 py-2 text-center" hidden>
                        {{ $courrier->datereception ? $courrier->datereception->addDays(60)->format('d/m/Y') : 'N/A' }}
                    </td>
                    <td class="px-4 py-2 text-center">
                        <button @click="openAndPopulateForm({{ $courrier->NumCour }})"
                                class="btn btn-primary text-sm px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700"
                                title="Saisir une facture pour ce courrier">
                            <span class="fa fa-edit"></span>
                        </button>
                    </td>
                    <td class="px-4 py-2 text-center">
                        <a href="{{ route('courriers.printFiche', ['numCour' => $courrier->NumCour]) }}"
                           target="_blank"
                           class="btn btn-warning text-sm px-3 py-1 bg-yellow-600 text-white rounded hover:bg-yellow-700"
                           title="Imprimer la fiche de dépôt">
                            <span class="fa fa-print"></span>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-3xl mb-2 block"></i>
                        Aucun courrier en instance trouvé
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
         <div class="mt-4 flex justify-center">
    {{ $courriers->links() }}
</div>

    <!-- Modal Création Courrier -->
    <div x-show="openCreateModal" x-cloak
         class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-50 modal-overlay"
         @keydown.escape.window="openCreateModal = false"
         @click.self="openCreateModal = false">
        <div class="bg-white rounded-lg shadow-lg max-w-4xl w-full p-6 overflow-auto max-h-[90vh]">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">FICHE DE DEPOT DE COURRIER - DIRECTION SANTE</h2>
                <button @click="openCreateModal = false" class="text-gray-500 hover:text-gray-700 text-xl">&times;</button>
            </div>

            @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded border border-red-200">
                <h6 class="font-semibold mb-2">Erreurs de validation :</h6>
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('courriers.store') }}">
                @csrf

                <!-- Section Déposant -->
                <fieldset class="border border-primary p-4 mb-6 rounded">
                    <legend class="text-primary font-semibold px-2">Déposant <i class="fa fa-user"></i></legend>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-2">
                        <div>
                            <label class="block mb-1 font-medium" for="edition">Date de Dépôt <span class="text-red-500">*</span></label>
                            <input type="date" id="edition" name="edition"
                                   max="{{ date('Y-m-d') }}"
                                   value="{{ old('edition') }}"
                                   required
                                   class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="block mb-1 font-medium" for="nomd">Noms <span class="text-red-500">*</span></label>
                            <input type="text" id="nomd" name="nomd"
                                   value="{{ old('nomd') }}"
                                   required
                                   class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="block mb-1 font-medium" for="prenomd">Prénoms <span class="text-red-500">*</span></label>
                            <input type="text" id="prenomd" name="prenomd"
                                   value="{{ old('prenomd') }}"
                                   required
                                   class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="block mb-1 font-medium" for="nbetatd">Nombre d'état(s) <span class="text-red-500">*</span></label>
                            <input type="number" id="nbetatd" name="nbetatd"
                                   min="1"
                                   value="{{ old('nbetatd', 1) }}"
                                   required
                                   class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="block mb-1 font-medium" for="struct">Structures <span class="text-red-500">*</span></label>
                            <input type="text" id="struct" name="struct"
                                   value="{{ old('struct') }}"
                                   required
                                   class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block mb-1 font-medium" for="nomcompte">Pour le compte de <span class="text-red-500">*</span></label>
                            <textarea id="nomcompte" name="nomcompte"
                                      required rows="2"
                                      class="w-full border rounded px-3 py-2 resize-none focus:ring-2 focus:ring-blue-500">{{ old('nomcompte') }}</textarea>
                        </div>
                        <div class="md:col-span-4">
                            <label class="block mb-1 font-medium" for="motif">Motif <span class="text-red-500">*</span></label>
                            <textarea id="motif" name="motif"
                                      required rows="3"
                                      class="w-full border rounded px-3 py-2 resize-none focus:ring-2 focus:ring-blue-500">{{ old('motif') }}</textarea>
                        </div>
                    </div>
                </fieldset>

                <!-- Section Réceptionniste -->
                <fieldset class="border border-primary p-4 rounded">
                    <legend class="text-primary font-semibold px-2">Réceptionniste <i class="fa fa-user"></i></legend>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-2">
                        <div>
                            <label class="block mb-1 font-medium" for="enreg">Date enregistrement</label>
                            <input type="text" id="enreg" name="enreg"
                                   value="{{ date('d/m/Y') }}" readonly
                                   class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed" />
                        </div>
                        <div>
                            <label class="block mb-1 font-medium" for="nometarec">Nombre d'état(s) reçu <span class="text-red-500">*</span></label>
                            <input type="number" id="nometarec" name="nometarec"
                                   min="1"
                                   value="{{ old('nometarec', 1) }}"
                                   required
                                   class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="block mb-1 font-medium" for="nomrec">Noms Réceptionniste <span class="text-red-500">*</span></label>
                            <input type="text" id="nomrec" name="nomrec"
                                   value="{{ old('nomrec') }}"
                                   required
                                   class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="block mb-1 font-medium" for="prenomrec">Prénoms Réceptionniste <span class="text-red-500">*</span></label>
                            <input type="text" id="prenomrec" name="prenomrec"
                                   value="{{ old('prenomrec') }}"
                                   required
                                   class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500" />
                        </div>
                    </div>
                    <input type="hidden" id="usersaisie" name="usersaisie" value="{{ Auth::user()->name ?? 'Système' }}" />
                </fieldset>

                <!-- Boutons -->
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" @click="openCreateModal = false"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                        <i class="fas fa-times mr-1"></i>Annuler
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        <i class="fas fa-save mr-1"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Saisie Facture -->
    <div x-show="openSaisieModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 modal-overlay"
         @keydown.escape.window="resetSaisieModal()"
         @click.self="resetSaisieModal()">
        <div class="bg-white rounded-lg shadow-lg max-w-4xl w-full p-6 overflow-auto max-h-[90vh]">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">
                    <i class="fas fa-file-invoice mr-2"></i>Enregistrement de Facture
                </h2>
                <button @click="resetSaisieModal()" class="text-gray-500 hover:text-gray-700 text-xl font-bold">&times;</button>
            </div>

            <!-- Indicateur de chargement -->
            <div x-show="loadingSaisie" class="flex items-center justify-center py-8">
                <div class="loader mr-3"></div>
                <span class="text-gray-600">Chargement du formulaire...</span>
            </div>

            <!-- Contenu dynamique (chargé via AJAX au clic) -->
            <div id="saisieModalContent" x-show="!loadingSaisie"></div>

            <!-- Bouton de fermeture (affiché seulement si pas de chargement et pas de contenu) -->
            <div class="mt-6 flex justify-end" x-show="!loadingSaisie && errorMessage">
                <button @click="resetSaisieModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                    <i class="fas fa-times mr-1"></i>Fermer
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    // Vérification de l'existence du token CSRF
    document.addEventListener('DOMContentLoaded', function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            console.warn('Token CSRF non trouvé. Ajoutez <meta name="csrf-token" content="{{ csrf_token() }}"> dans votre layout.');
        }
    });

    // Initialisation DataTables
    $(document).ready(function() {
        $('#courriersTable').DataTable({
            scrollX: true,
            scrollY: 400,
            order: [[ 6, 'desc' ]], // Trier par date d'enregistrement décroissante
            pageLength: 25,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
            },
            columnDefs: [
                { targets: [1, 4], orderable: false }, // Désactiver tri sur colonnes motif et compte de
                { targets: [8, 9], searchable: false, orderable: false } // Actions non triables/recherchables
            ]
        });
    });

    // Fonction globale améliorée pour fermer le modal
    function closeSaisieModal() {
        try {
            const alpineData = Alpine.$data(document.querySelector('[x-data]'));
            if (alpineData && typeof alpineData.resetSaisieModal === 'function') {
                alpineData.resetSaisieModal();
            } else {
                console.error('Données Alpine.js non trouvées ou méthode resetSaisieModal manquante');
                // Fallback : fermer manuellement
                const modal = document.querySelector('[x-show="openSaisieModal"]');
                if (modal) {
                    modal.style.display = 'none';
                }
            }
        } catch (error) {
            console.error('Erreur lors de la fermeture du modal:', error);
            // Fallback ultime
            window.location.reload();
        }
    }
    window.closeSaisieModal = closeSaisieModal;

    // Fonction pour recharger automatiquement après succès
    window.addEventListener('message', function(event) {
        if (event.data === 'facture-saved') {
            // Fermer le modal
            closeSaisieModal();
            // Recharger la page après 1 seconde
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }
    });
</script>
@endpush
