<!-- Modal Saisie Facture -->
<div x-show="openSaisieModal" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 modal-overlay"
     @keydown.escape.window="resetSaisieModal()"
     @click.self="resetSaisieModal()">
    <div class="bg-white rounded-lg shadow-lg max-w-4xl w-full p-6 overflow-auto max-h-[90vh]">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">
                <i class="fas fa-file-invoice mr-2"></i>Saisie Facture - Courrier #<span x-text="selectedCourierId"></span>
            </h2>
            <button @click="resetSaisieModal()" class="text-gray-500 hover:text-gray-700 text-xl font-bold">&times;</button>
        </div>

        <!-- Loader ✅ -->
        <div x-show="loadingSaisie" class="flex items-center justify-center py-8">
            <div class="loader mr-3"></div> <!-- Ajoutez le CSS ci-dessous -->
            <span class="text-gray-600">Chargement du formulaire...</span>
        </div>

        <!-- Erreur ✅ -->
        <div x-show="!loadingSaisie && errorMessage" class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <span x-text="errorMessage"></span>
        </div>

        <!-- Contenu ✅ -->
        <div id="saisieModalContent" x-show="!loadingSaisie && !errorMessage"></div>

        <!-- Bouton fermer si erreur -->
        <div class="mt-6 flex justify-end" x-show="!loadingSaisie && errorMessage">
            <button @click="resetSaisieModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                <i class="fas fa-times mr-1"></i>Fermer
            </button>
        </div>
    </div>
</div>
