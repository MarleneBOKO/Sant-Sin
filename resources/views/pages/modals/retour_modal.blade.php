{{-- resources/views/pages/modals/retour_modal.blade.php --}}
<div id="retourM" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden opacity-0 pointer-events-none transition-opacity duration-300" aria-hidden="true" role="dialog" aria-modal="true" tabindex="-1">
    <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
        {{-- Bouton Fermer --}}
        <button type="button" class="close-retour-modal absolute top-3 right-3 text-gray-600 hover:text-gray-900" aria-label="Fermer la fenêtre modale">
            <i class="fas fa-times"></i>
        </button>

        {{-- En-tête de la Modale --}}
        <h3 class="text-xl font-semibold mb-4">Saisir le Retour pour la Facture {{ $ligne->Reference_Facture ?? 'N/A' }}</h3>

        {{-- Formulaire --}}
        <form id="retour-form" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="facture_id" value="{{ $ligne->Id_Ligne }}">

            {{-- Exemples de Champs : Ajustez selon vos besoins --}}
            <div class="mb-4">
                <label for="date_retour" class="block font-semibold mb-2">Date de Retour :</label>
                <input type="date" id="date_retour" name="date_retour" value="{{ now()->format('Y-m-d') }}" required
                       class="w-full border border-gray-300 rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label for="commentaire" class="block font-semibold mb-2">Commentaire :</label>
                <textarea id="commentaire" name="commentaire" rows="3" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            {{-- Ajoutez d'autres champs si nécessaire, par exemple un upload de fichier pour preuve de retour --}}

            {{-- Boutons --}}
            <div class="flex justify-end space-x-4">
                <button type="button" class="close-retour-modal bg-gray-400 hover:bg-gray-500 text-white rounded-full px-4 py-2">Annuler</button>
                <button type="button" class="submit-retour-btn bg-green-600 hover:bg-green-700 text-white rounded-full px-4 py-2">
                    <i class="fas fa-check mr-1"></i> Valider le Retour
                </button>
            </div>
        </form>
    </div>
</div>
