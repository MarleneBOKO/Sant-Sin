<div class="p-6 space-y-4">
    @if ($errors->any())
        <div class="p-3 bg-red-50 border border-red-200 text-red-700 rounded-md">
            <ul class="list-disc list-inside text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div id="js-error-box" class="hidden p-3 bg-red-50 border border-red-200 text-red-700 rounded-md">
        <div class="flex items-center mb-1">
            <i class="fas fa-exclamation-triangle text-red-500 mr-2 text-sm"></i>
            <h6 class="font-semibold text-sm">Erreurs de validation :</h6>
        </div>
        <ul id="js-error-list" class="list-disc list-inside space-y-0.5 text-xs"></ul>
    </div>

    <fieldset class="border border-gray-200 rounded-md p-3 bg-gray-50">
        <legend class="text-xs font-bold uppercase tracking-wider text-gray-500 px-2">Détails du Courrier</legend>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 mt-2">
            <div>
                <label class="block text-xs font-medium text-gray-500">Code / Déposant</label>
                <input type="text" class="w-full px-2 py-1.5 bg-gray-100 border border-gray-300 rounded text-sm cursor-not-allowed" readonly value="{{ $courrier->CodeCour }} - {{ $courrier->NomDeposant }} {{ $courrier->PrenomDeposant }}">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500">Structure / Motif</label>
                <input type="text" class="w-full px-2 py-1.5 bg-gray-100 border border-gray-300 rounded text-sm cursor-not-allowed" readonly value="{{ $courrier->structure }} - {{ Str::limit($courrier->motif, 15) }}" title="{{ $courrier->motif }}">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500">Nb. États (Déposé/Restant)</label>
                <input type="text" class="w-full px-2 py-1.5 bg-blue-50 border border-blue-200 rounded text-sm font-bold text-blue-700 cursor-not-allowed" readonly value="{{ $courrier->nbreetatdepot ?? 0 }} / {{ $nombreRestant }}">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500">Date Réception</label>
                <input type="text" class="w-full px-2 py-1.5 bg-gray-100 border border-gray-300 rounded text-sm cursor-not-allowed" readonly value="{{ $courrier->datereception ? $courrier->datereception->format('d/m/Y') : 'N/A' }}">
            </div>
        </div>
    </fieldset>

    <form method="POST" action="{{ route('courriers.storeLigneSuivi') }}" id="factureForm" class="space-y-4" novalidate>
        @csrf
        <input type="hidden" name="CodeCour" value="{{ $courrier->CodeCour }}">
        <input type="hidden" name="numCour" value="{{ $courrier->NumCour }}">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="space-y-4">
                <fieldset class="border border-gray-200 rounded-md p-3">
                    <legend class="text-xs font-bold text-gray-700 px-2">Partenaire & Assuré</legend>
                    <div class="grid grid-cols-1 gap-3 mt-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="idSouscripteur">Souscripteur <span class="text-red-500">*</span></label>
                            <select name="idSouscripteur" id="idSouscripteur" {{ $isIndividuel ? 'required' : '' }} class="w-full select2-souscripteur">
                                <option value="">Rechercher...</option>
                                @foreach($souscripteurs as $souscripteur)
                                    <option value="{{ $souscripteur->id }}" {{ old('idSouscripteur') == $souscripteur->id ? 'selected' : '' }}>{{ $souscripteur->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="assure">Assuré <span class="text-red-500">*</span></label>
                            <input type="text" name="assure" id="assure" {{ $isIndividuel ? 'required' : '' }} value="{{ old('assure') }}" placeholder="Nom complet de l'assuré" class="w-full px-2 py-2 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="border border-gray-200 rounded-md p-3">
                    <legend class="text-xs font-bold text-gray-700 px-2">Finances & Période</legend>
                    <div class="grid grid-cols-2 gap-3 mt-2">
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="montant">Montant <span class="text-red-500">*</span></label>
                            <input type="number" name="montant" id="montant" required min="0" step="0.01" class="w-full px-2 py-2 border border-gray-300 rounded text-sm">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="nb_factures">Nb. Factures <span class="text-red-500">*</span></label>
                            <input type="number" name="nb_factures" id="nb_factures" required min="1" value="1" class="w-full px-2 py-2 border border-gray-300 rounded text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Du <span class="text-red-500">*</span></label>
                            <input type="date" name="date_debut" id="date_debut" required class="w-full px-2 py-2 border border-gray-300 rounded text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Au <span class="text-red-500">*</span></label>
                            <input type="date" name="date_fin" id="date_fin" required class="w-full px-2 py-2 border border-gray-300 rounded text-sm">
                        </div>
                    </div>
                </fieldset>
            </div>

            <div class="space-y-4">
                <fieldset class="border border-gray-200 rounded-md p-3">
                    <legend class="text-xs font-bold text-gray-700 px-2">Détails Facturation</legend>
                    <div class="grid grid-cols-2 gap-3 mt-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="mois">Mois <span class="text-red-500">*</span></label>
                            <select name="mois" id="mois" required class="w-full px-2 py-2 border border-gray-300 rounded text-sm">
                                <option value="">Choisir...</option>
                                @foreach($moisList as $m)
                                    <option value="{{ $m->Id_mois }}">{{ $m->libelle_mois }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="annee">Année <span class="text-red-500">*</span></label>
                            <select name="annee" id="annee" required class="w-full px-2 py-2 border border-gray-300 rounded text-sm">
                                @foreach($annees as $an)
                                    <option value="{{ $an }}" {{ $an == date('Y') ? 'selected' : '' }}>{{ $an }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="reference_facture">Référence Facture <span class="text-red-500">*</span></label>
                            <textarea name="reference_facture" id="reference_facture" required rows="3" class="w-full px-2 py-2 border border-gray-300 rounded text-sm resize-none" placeholder="Saisir les références..."></textarea>
                        </div>
                    </div>
                </fieldset>

                <div class="bg-yellow-50 border border-yellow-200 p-3 rounded flex items-center">
                    <input type="checkbox" name="is_evac" id="isEvac" value="1" class="h-4 w-4 text-blue-600 rounded border-gray-300">
                    <label for="isEvac" class="ml-3 text-sm font-medium text-yellow-800 cursor-pointer">Activer le mode évacuation</label>
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
            <button type="button" id="btnAnnuler" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium transition-all">
                Annuler
            </button>
            <button type="submit" id="btnValider" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium shadow-sm transition-all flex items-center">
                <i class="fas fa-save mr-2"></i> Enregistrer la facture
            </button>
        </div>
    </form>
</div>

<script>
    (function() {
        const form = document.getElementById('factureForm');
        const btnValider = document.getElementById('btnValider');
        const errorBox = document.getElementById('js-error-box');
        const errorList = document.getElementById('js-error-list');
        document.getElementById('js-error-box').classList.add('hidden');
            form.querySelectorAll('input, select, textarea').forEach(el => el.classList.remove('border-red-500'));
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // État de chargement
            btnValider.disabled = true;
            btnValider.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> En cours...';

            // Reset visuel
            errorBox.classList.add('hidden');
            errorList.innerHTML = '';
            form.querySelectorAll('input, select, textarea').forEach(el => {
                el.classList.remove('border-red-500', 'bg-red-50');
            });

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(async response => {
                const data = await response.json();

                if (response.status === 422) {
                    displayValidationErrors(data.errors);
                    throw new Error("Validation échouée");
                }

                if (!response.ok) throw new Error(data.message || 'Erreur serveur');
                return data;
            })
            .then(data => {
                if (data.success) {
                    // Succès : On ferme et on recharge
                    window.parent.postMessage({ action: 'success-save', message: data.message }, '*');
                }
            })
            .catch(error => {
                if (error.message !== "Validation échouée") {
                    displayValidationErrors({ "Erreur": [error.message] });
                }
            })
            .finally(() => {
                btnValider.disabled = false;
                btnValider.innerHTML = '<i class="fas fa-save mr-2"></i> Enregistrer';
            });
        });

        function displayValidationErrors(errors) {
            errorList.innerHTML = '';
            Object.keys(errors).forEach(key => {
                errors[key].forEach(message => {
                    const li = document.createElement('li');
                    li.textContent = message;
                    errorList.appendChild(li);
                });
                // Highlight des champs
                const input = form.querySelector(`[name="${key}"]`);
                if (input) input.classList.add('border-red-500', 'bg-red-50');
            });

            errorBox.classList.remove('hidden');
            // Remonter en haut du modal pour voir les erreurs
            const modalContainer = document.getElementById('saisieModalContent').closest('.overflow-auto');
            if (modalContainer) modalContainer.scrollTo({ top: 0, behavior: 'smooth' });
        }
    })();
</script>

