<div class="p-6 space-y-4"> <!-- Padding global, espacement vertical minimal entre sections principales -->
    <!-- Erreurs de Validation (pleine largeur, en haut) -->
    @if ($errors->any())
        <div class="p-3 bg-red-50 border border-red-200 text-red-700 rounded-md col-span-full">
            <div class="flex items-center mb-1">
                <i class="fas fa-exclamation-triangle text-red-500 mr-1 text-sm"></i>
                <h6 class="font-semibold text-sm">Erreurs :</h6>
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Section 1: Détails du Courrier (pleine largeur, en haut - info statique) -->
    <fieldset class="border border-gray-200 rounded-md p-3 bg-gray-50 col-span-full">
        <legend class="text-xs font-medium text-gray-700 px-1">Détails du Courrier</legend>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-2">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-0.5">Code / Déposant</label>
                <input type="text" class="w-full px-2 py-2 bg-gray-100 border border-gray-300 rounded text-sm cursor-not-allowed" readonly value="{{ $courrier->CodeCour }} - {{ $courrier->NomDeposant }} {{ $courrier->PrenomDeposant }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-0.5">Structure / Motif</label>
                <input type="text" class="w-full px-2 py-2 bg-gray-100 border border-gray-300 rounded text-sm cursor-not-allowed" readonly value="{{ $courrier->structure }} - {{ Str::limit($courrier->motif, 20) }}" title="{{ $courrier->motif }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-0.5">Nb. États / Restant</label>
                <input type="text" class="w-full px-2 py-2 bg-blue-50 border border-blue-200 rounded text-sm font-semibold text-blue-700 cursor-not-allowed" readonly value="{{ ($courrier->nbreetatdepot ?? 0) }} / {{ $nombreRestant }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-0.5">Date Réception</label>
                <input type="text" class="w-full px-2 py-2 bg-gray-100 border border-gray-300 rounded text-sm cursor-not-allowed" readonly value="{{ $courrier->datereception ? $courrier->datereception->format('d/m/Y') : 'N/A' }}">
            </div>
        </div>
    </fieldset>

 <form method="POST" action="{{ route('courriers.storeLigneSuivi') }}" id="factureForm" class="space-y-0 novalidate"> <!-- novalidate : désactive validation HTML5 native -->
        @csrf
        <input type="hidden" name="CodeCour" value="{{ $courrier->CodeCour }}">
        <input type="hidden" name="numCour" value="{{ $courrier->NumCour }}">

        <!-- Grille Globale pour Rows Horizontales des Sections (1 col mobile, 2 cols desktop) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Colonne Gauche : Partenaire et Assuré + Financiers et Période -->
            <div class="space-y-3 lg:space-y-4">
                <!-- Section 2: Partenaire et Assuré (row interne de 2 max, required CONDITIONNEL) -->
                <fieldset class="border border-gray-200 rounded-md p-3">
                    <legend class="text-sm font-medium text-gray-700 px-1">Partenaire et Assuré</legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-2">
                        <div class="{{ $isIndividuel ? '' : 'hidden' }}">
                            <label class="block text-sm font-medium text-gray-700 mb-0.5" for="idSouscripteur">Souscripteur <span class="text-red-500">*</span></label>
                            <select name="idSouscripteur" id="idSouscripteur" {{ $isIndividuel ? 'required' : '' }} class="w-full px-2 py-2 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <option value="">Sélectionnez...</option>
                                @foreach($souscripteurs as $souscripteur)
                                    <option value="{{ $souscripteur->id }}" {{ old('idSouscripteur') == $souscripteur->id ? 'selected' : '' }}>{{ $souscripteur->nom }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="{{ $isIndividuel ? '' : 'hidden' }} md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-0.5" for="assure">Assuré <span class="text-red-500">*</span></label>
                            <input type="text" name="assure" id="assure" {{ $isIndividuel ? 'required' : '' }} value="{{ old('assure') }}" placeholder="Nom de l'assuré" class="w-full px-2 py-2 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        </div>
                    </div>
                </fieldset>

                <!-- Section 4: Financiers et Période (row interne de 2 max) -->
                <fieldset class="border border-gray-200 rounded-md p-3">
                    <legend class="text-sm font-medium text-gray-700 px-1">Financiers et Période</legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-0.5" for="montant">Montant <span class="text-red-500">*</span></label>
                            <input type="number" name="montant" id="montant" required min="0" step="0.01" value="{{ old('montant') }}" placeholder="0.00" class="w-full px-2 py-2 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-0.5" for="nb_factures">Nb. Factures <span class="text-red-500">*</span></label>
                            <input type="number" name="nb_factures" id="nb_factures" required min="1" value="{{ old('nb_factures', 1) }}" placeholder="1" class="w-full px-2 py-2 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-0.5">Nb. Restant</label>
                            <input type="number" class="w-full px-2 py-2 bg-gray-100 border border-gray-300 rounded text-sm cursor-not-allowed" readonly value="{{ $nombreRestant }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-0.5" for="date_debut">Période du <span class="text-red-500">*</span></label>
                            <input type="date" name="date_debut" id="date_debut" required max="{{ date('Y-m-d') }}" value="{{ old('date_debut') }}" class="w-full px-2 py-2 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-0.5" for="date_fin">Au <span class="text-red-500">*</span></label>
                            <input type="date" name="date_fin" id="date_fin" required max="{{ date('Y-m-d') }}" value="{{ old('date_fin') }}" class="w-full px-2 py-2 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        </div>
                    </div>
                </fieldset>
            </div>

            <!-- Colonne Droite : Détails Facture + Infos Saisie -->
            <div class="space-y-3 lg:space-y-4">
                <!-- Section 3: Détails de la Facture (row interne de 2 max) -->
                <fieldset class="border border-gray-200 rounded-md p-3">
                    <legend class="text-sm font-medium text-gray-700 px-1">Détails Facture</legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-0.5" for="mois">Mois <span class="text-red-500">*</span></label>
                            <select name="mois" id="mois" required class="w-full px-2 py-2 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <option value="">Sélectionnez...</option>
                                @foreach($moisList as $mois)
                                    <option value="{{ $mois->Id_mois }}" {{ old('mois') == $mois->Id_mois ? 'selected' : '' }}>{{ $mois->libelle_mois }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-0.5" for="annee">Année <span class="text-red-500">*</span></label>
                            <select name="annee" id="annee" required class="w-full px-2 py-2 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <option value="">Sélectionnez...</option>
                                @foreach($annees as $annee)
                                    <option value="{{ $annee }}" {{ old('annee') == $annee ? 'selected' : '' }}>{{ $annee }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-0.5" for="reference_facture">Référence Facture <span class="text-red-500">*</span></label>
                            <textarea name="reference_facture" id="reference_facture" required rows="2" placeholder="Référence complète..." class="w-full px-2 py-2 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none">{{ old('reference_facture') }}</textarea>
                        </div>
                    </div>
                </fieldset>

                <!-- Section Infos Saisie (row interne de 2) -->
                <fieldset class="border border-gray-200 rounded-md p-3 bg-gray-50">
                    <legend class="text-sm font-medium text-gray-700 px-1">Infos Saisie</legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-0.5">Date Saisie</label>
                            <input type="text" class="w-full px-2 py-2 bg-gray-100 border border-gray-300 rounded text-sm cursor-not-allowed" readonly value="{{ now()->format('d/m/Y H:i') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-0.5">Utilisateur</label>
                            <input type="text" class="w-full px-2 py-2 bg-gray-100 border border-gray-300 rounded text-sm cursor-not-allowed" readonly value="{{ Auth::user()->name ?? 'Système' }}">
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>

        <!-- Section Évacuation (conditionnelle, pleine largeur en bas des rows) -->
        <fieldset class="border border-gray-200 rounded-md p-3 bg-yellow-50 col-span-full">
            <legend class="text-xs font-medium text-gray-700 px-1">Mode Évacuation</legend>
            <div class="flex items-center p-2 mt-2">
                <input type="checkbox" name="is_evac" id="isEvac" value="1" class="h-3 w-3 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mr-2">
                <label for="isEvac" class="text-xs font-medium text-gray-700 cursor-pointer">Évacuation ? (Cocher pour activer)</label>
            </div>
        </fieldset>

        <!-- Boutons d'Action (pleine largeur, en bas) -->
        <div class="flex justify-end space-x-2 pt-3 border-t border-gray-200 col-span-full">
            <button type="button" id="btnAnnuler" class="px-3 py-1.5 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors text-xs font-medium flex items-center">
                <i class="fas fa-times mr-1 text-xs"></i>Annuler
            </button>
            <button type="submit" id="btnValider" class="px-3 py-1.5 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors text-xs font-medium flex items-center shadow-sm">
                <i class="fas fa-save mr-1 text-xs"></i>Valider
            </button>
        </div>
    </form>

    <!-- Script JS pour soumission AJAX (autonome, gère loading et fermeture modal) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('factureForm');
            const btnAnnuler = document.getElementById('btnAnnuler');
            const btnValider = document.getElementById('btnValider');

            if (form) {
                // Soumission AJAX avec loading
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    btnValider.disabled = true;
                    btnValider.innerHTML = '<i class="fas fa-spinner fa-spin mr-1 text-xs"></i>Enregistrement...';

                    const formData = new FormData(form);
                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Erreur HTTP: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Succès : alerte et fermeture modal
                            alert(data.message || 'Facture enregistrée avec succès !');
                            // Trigger rechargement parent via postMessage
                            if (window.parent) {
                                window.parent.postMessage('facture-saved', '*');
                            }
                        } else {
                            // Erreur validation : alerte et rollback bouton
                            alert('Erreur : ' + (data.error || 'Échec de l\'enregistrement. Vérifiez les champs.'));
                            btnValider.disabled = false;
                            btnValider.innerHTML = '<i class="fas fa-save mr-1 text-xs"></i>Valider';
                        }
                    })
                    .catch(error => {
                        console.error('Erreur AJAX:', error);
                        alert('Erreur de connexion. Réessayez ou contactez l\'admin.');
                        btnValider.disabled = false;
                        btnValider.innerHTML = '<i class="fas fa-save mr-1 text-xs"></i>Valider';
                    });
                });

                // Bouton Annuler : ferme le modal parent
                if (btnAnnuler) {
                    btnAnnuler.addEventListener('click', function() {
                        if (window.parent && window.parent.closeSaisieModal) {
                            window.parent.closeSaisieModal();
                        } else {
                            // Fallback : ferme la fenêtre si standalone
                            window.close();
                        }
                    });
                }
            }
        });
    </script>
</div> <!-- Fin du div principal p-6 space-y-4 -->
