<form action="{{ route('factures.save-by-courrier') }}" method="post" class="space-y-6">
    @csrf
    <input type="hidden" name="code" value="{{ $numCour }}">

    <!-- Header Modal -->
    <div class="bg-blue-900 text-white p-4 rounded-t-lg">
        <h5 class="text-lg font-bold">Enregistrement de Facture</h5>
        <p class="text-sm opacity-90">Veuillez renseigner les informations, puis cliquer sur "Valider".</p>
    </div>

    <!-- Body Modal -->
<div class="mb-4">
    <label class="block text-sm font-medium text-gray-700 mb-1">
        {{ $profil == 7 ? 'Souscripteur' : 'Prestataire' }}
    </label>

    <select name="prest"
            id="tiersSelect"
            class="w-full select2">
        <option value="">
            -- Sélectionner {{ $profil == 7 ? 'un souscripteur' : 'un prestataire' }} --
        </option>

        @foreach($prestataires as $p)
            <option value="{{ $p->Code }}">
                {{ $p->Libelle }}
                @if($p->libelle_type_prestataire)
                    ({{ $p->libelle_type_prestataire }})
                @endif
            </option>
        @endforeach
    </select>
</div>


            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Expéditeur</label>
                <input type="text" class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100" readonly value="{{ $courrier->expediteur }}">
            </div>
        </div>

        <!-- Ligne 2 : Mois, Année, N° Réception, Date Réception -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mois</label>
                <select name="mois" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @foreach($mois as $m)
                        <option value="{{ $m->Id_mois }}" {{ $m->Id_mois == now()->month ? 'selected' : '' }}>{{ $m->libelle_mois }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Année</label>
                <select name="an" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="{{ now()->year }}" selected>{{ now()->year }}</option>
                    @foreach($annees as $a)
                        <option value="{{ $a->annee_facture }}">{{ $a->annee_facture }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">N° Réception</label>
                <input type="text" name="recept" class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100" readonly value="{{ $courrier->NumCour }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date Réception</label>
                <input type="text" name="drec" class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100" readonly value="{{ $courrier->date_DateRecep }}">
            </div>
        </div>

        <!-- Ligne 3 : Référence Facture, Objet, Référence Courrier -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Référence Facture</label>
                <textarea name="souscrip" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Objet</label>
                <textarea name="objet" class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100" readonly>{{ $courrier->Objet }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Référence Courrier</label>
                <textarea class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100" readonly>{{ $courrier->RefCour }}</textarea>
            </div>
        </div>

        <!-- Ligne 4 : Montant, Nombre de Facture, Nombre Restant, Code Courrier -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Montant</label>
                <input type="number" name="mont" min="0" step="0.01" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de Facture</label>
                <input type="number" name="nb" min="1" step="1" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="{{ $lignefac->nbfacture ?? '' }}" {{ $lignefac ? 'readonly' : '' }}>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Restant</label>
                <input type="text" class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100" readonly value="{{ $nombreRestant }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Code Courrier Interne</label>
                <input type="text" name="CodeCour" class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100" readonly value="{{ $courrier->CodeCour }}">
            </div>
        </div>

        <!-- Ligne 5 : Période, Date Saisie, Utilisateur -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Période du</label>
                <input type="date" name="datedeb" max="{{ now()->toDateString() }}" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Au</label>
                <input type="date" name="datefin" max="{{ now()->toDateString() }}" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date Saisie</label>
                <input type="text" name="dtEnreg" class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100" readonly value="{{ now()->format('d/m/Y') }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Utilisateur</label>
                <input type="text" name="usersaisie" class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100" readonly value="{{ session('NP_Agent') }}">
            </div>
        </div>

        <!-- Si Profil 7 : Assuré et Évacuation -->
        @if($profil == 7)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Assuré</label>
                    <input type="text" name="ass" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Évacuation ?</label>
                    <div class="flex items-center mt-3">
                        <input type="checkbox" name="isEvac[]" id="isEvac" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="isEvac" class="ml-2 text-sm text-gray-700">Oui / Non</label>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Footer Modal -->
    <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 rounded-b-lg">
        <button type="button" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors" onclick="$('#transM').addClass('hidden').fadeOut();">Annuler</button>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Valider</button>
    </div>
</form>


<script>
$(function () {

    const $select = $('#tiersSelect');

    if ($select.hasClass('select2-hidden-accessible')) {
        $select.select2('destroy');
    }

    $select.select2({
        placeholder: "Rechercher...",
        width: '100%',
        allowClear: true,
        dropdownParent: $('#transM')
    });

});
</script>
