<!-- Modal de Rejet -->
<div id="rejetModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
    <div class="bg-white w-full max-w-4xl rounded shadow p-6">  <!-- Augmenté max-w pour la grille -->
        <div class="flex justify-between items-center border-b pb-2">
            <h2 class="text-lg font-bold text-red-700">Rejet de Facture</h2>
            <button type="button" onclick="closeRejetModal()" class="text-gray-500 hover:text-red-600 text-xl">&times;</button>
        </div>

        <form method="POST" id="rejetForm" class="mt-4">
            @csrf

            <!-- Champs cachés pour traitement -->
            <input type="hidden" name="code" id="rejet_id">
            <input type="hidden" name="mont" id="rejet_mont">
            <input type="hidden" name="montrejet" id="rejet_montrejet">
            <input type="hidden" name="numrecept" id="rejet_numrecept">
            <input type="hidden" name="usersaisie" id="rejet_agent">
            <input type="hidden" name="date_rejet" value="{{ now()->format('Y-m-d') }}">

            <!-- Grille pour les champs -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 space-y-0">  <!-- Grille responsive -->

                <!-- 1. Prestataire ou Assuré -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Prestataire</label>
                    <input type="text" id="rejet_assure" class="w-full border border-gray-300 rounded px-3 py-2
                  bg-gray-200 text-gray-500
                  cursor-not-allowed select-none focus:outline-none"  readonly>
                </div>

                <!-- 2. Souscripteur ou Référence -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Référence / Souscripteur</label>
                    <input type="text" id="rejet_souscripteur" class="w-full border border-gray-300 rounded px-3 py-2
                  bg-gray-200 text-gray-500
                  cursor-not-allowed select-none focus:outline-none" readonly>
                </div>

                <!-- 3. Motif du rejet -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Motif du rejet</label>
                    <select name="motif" required class="mt-1 block w-full rounded-md border-2 border-gray-400 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                        {{-- Option par défaut vide et sélectionnée --}}
                        <option value="" selected disabled>-- VEUILLEZ SÉLECTIONNER UN MOTIF --</option>

                        @foreach(DB::table('parametres')->where('typaram', 'motif_rejet')->orderBy('libelleparam')->get() as $motif)
                            <option value="{{ $motif->codtyparam }}">{{ strtoupper($motif->libelleparam) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 4. Numéro Facture -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">N° Facture</label>
                    <input type="text" id="rejet_ref_facture" class="w-full border border-gray-300 rounded px-3 py-2
                  bg-gray-200 text-gray-500
                  cursor-not-allowed select-none focus:outline-none"readonly>
                </div>

                <!-- 5. Numéro Réception -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">N° Réception</label>
                    <input type="text" id="rejet_numreception_affiche" class="w-full border border-gray-300 rounded px-3 py-2
                  bg-gray-200 text-gray-500
                  cursor-not-allowed select-none focus:outline-none" readonly>
                </div>

                <!-- 6. Montant Facture -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Montant Facture</label>
                    <input type="text" id="rejet_montant_total" class="w-full border border-gray-300 rounded px-3 py-2
                  bg-gray-200 text-gray-500
                  cursor-not-allowed select-none focus:outline-none" readonly>
                </div>

                <!-- 7. Montant à rejeter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Montant à rejeter</label>
                    <input type="text" id="rejet_montant_rejete" class="w-full border border-gray-300 rounded px-3 py-2
                  bg-gray-200 text-gray-500
                  cursor-not-allowed select-none focus:outline-none" readonly>
                </div>

                <!-- 8. Date du rejet -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date du rejet</label>
                    <input type="text" value="{{ now()->format('d/m/Y') }}" class="w-full border border-gray-300 rounded px-3 py-2
                  bg-gray-200 text-gray-500
                  cursor-not-allowed select-none focus:outline-none" readonly>
                </div>

                <!-- 9. Date enregistrement -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date d'enregistrement</label>
                    <input type="text" id="rejet_date_enreg" class="w-full border border-gray-300 rounded px-3 py-2
                  bg-gray-200 text-gray-500
                  cursor-not-allowed select-none focus:outline-none" readonly>
                </div>

                <!-- 10. Rejet effectué par (plein largeur) -->
                <div class="md:col-span-2">  <!-- Occupe les deux colonnes sur desktop -->
                    <label class="block text-sm font-medium text-gray-700">Rejet effectué par</label>
                    <input type="text" id="rejet_agent_nom" class="w-full border border-gray-300 rounded px-3 py-2
                  bg-gray-200 text-gray-500
                  cursor-not-allowed select-none focus:outline-none" readonly>
                </div>

            </div>

            <!-- Boutons -->
            <div class="flex justify-end mt-6 space-x-2">
                <button type="button" onclick="closeRejetModal()" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Annuler</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Valider le rejet</button>
            </div>
        </form>
    </div>
</div>
