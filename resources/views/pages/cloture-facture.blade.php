<!-- Modal de Clôture - cloture-facture.blade.php -->
<div id="clotureModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
    <div class="bg-white w-full max-w-md rounded-lg shadow-xl p-6">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h2 class="text-lg font-bold text-blue-700 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Clôturer la Facture
            </h2>
            <button type="button" onclick="closeClotureModal()" class="text-gray-500 hover:text-red-600 text-xl font-bold">&times;</button>
        </div>

       <form method="POST" action="{{ route('ligne_suivi.cloturer', $facture->Id_Ligne) }}"class="space-y-4">
            @csrf
    @method('PUT')

            <!-- Champs cachés -->
            <input type="hidden" name="ref" id="cloture_ref">
            <input type="hidden" name="action" value="phase_clot">
            <input type="hidden" name="dteCl" value="{{ now()->format('d/m/Y') }}">

            <!-- Message de confirmation -->
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Attention :</strong> Voulez-vous vraiment clôturer la facture
                            <span id="cloture_reference" class="font-semibold text-yellow-800"></span> ?
                        </p>
                        <p class="text-xs text-yellow-600 mt-1">
                            Cette action est irréversible et marquera la facture comme définitivement traitée.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Informations de la facture -->
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Assuré / Prestataire</label>
                    <input type="text" id="cloture_assure" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm" readonly>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Date de clôture</label>
                    <input type="text" value="{{ now()->format('d/m/Y') }}" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm" readonly>
                </div>
            </div>

            <div class="flex justify-end mt-6 space-x-3">
                <button type="button" onclick="closeClotureModal()"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">
                    Annuler
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Clôturer définitivement
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Fonction pour ouvrir le modal de clôture
function openClotureModal(button) {
    const modal = document.getElementById('clotureModal');
    if (!modal) {
        console.error('Modal clotureModal non trouvé');
        return;
    }

    // Récupérer les données du bouton
    const data = {
        ref: button.dataset.ref,
        reference: button.dataset.reference,
        assure: button.dataset.assure
    };

    console.log('Données pour la clôture:', data);

    // Remplir les champs
    document.getElementById('cloture_ref').value = data.ref;
    document.getElementById('cloture_reference').textContent = data.reference;
    document.getElementById('cloture_assure').value = data.assure;

    // Afficher le modal
    modal.style.display = 'flex';
}

// Fonction pour fermer le modal de clôture
function closeClotureModal() {
    const modal = document.getElementById('clotureModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Event listener pour fermer le modal en cliquant à l'extérieur
document.addEventListener('click', function(e) {
    const modal = document.getElementById('clotureModal');
    if (e.target === modal) {
        closeClotureModal();
    }
});

// Event listener pour la touche Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeClotureModal();
    }
});
</script>
