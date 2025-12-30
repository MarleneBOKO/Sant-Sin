<div id="traitementModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 w-full max-w-2xl">
    <!-- Header -->
    <div class="flex justify-between items-center mb-4">
      <h5 class="text-xl font-semibold">Traiter la facture</h5>
      <button onclick="closeTraitementModal()" class="text-gray-600 hover:text-gray-900">&times;</button>
    </div>

<form method="POST" id="traitementForm" action="">
    @csrf
    @method('PUT')

    @if(session('traitement_errors'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 mb-4 rounded">
        <ul class="list-disc pl-5">
            @foreach (session('traitement_errors')->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@if (session('success'))
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 mb-4 rounded">
    {{ session('success') }}
</div>
@endif

@if (session('error'))
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 mb-4 rounded">
    {{ session('error') }}
</div>
@endif

      <input type="hidden" name="ref" id="ref">

      <div class="mb-4">
        <label>Souscripteur / N° Facture :</label>
        <input type="text" name="fact" id="fact" readonly class="w-full border px-3 py-2 rounded field-disabled"  />
      </div>

      <div class="mb-4">
        <label>Assuré / Prestataire :</label>
       <input type="text" name="assure" id="assures" readonly class="w-full border px-3 py-2 rounded field-disabled" />

      </div>

      <div class="mb-4 grid grid-cols-2 gap-4">
        <div>
          <label>Date enregistrement :</label>
          <input type="text" id="dateEnreg" readonly class="w-full border px-3 py-2 rounded field-disabled" />
        </div>
        <div>
          <label>Montant Facture :</label>
          <input type="text" name="MontantF" id="montantF" readonly class="w-full border px-3 py-2 rounded field-disabled" />
        </div>
      </div>

      <div class="mb-4 grid grid-cols-2 gap-4">
        <div>
          <label>Montant rejeté :</label>
          <input type="text" name="montrejete" id="montrejete" readonly class="w-full border px-3 py-2 rounded field-disabled" />
        </div>
        <div>
          <label>Date transmission :</label>
          <input type="text" id="dateTransmission" readonly class="w-full border px-3 py-2 rounded field-disabled" />
        </div>
      </div>

      <div class="mb-4 grid grid-cols-3 gap-4">
        <div>
          <label>N° Demande :</label>
          <input type="text" name="numero_demande" required class="w-full border px-3 py-2 rounded" />
        </div>
        <div>
          <label>Date Demande :</label>
          <input type="date" name="date_demande" min="" max="{{ date('Y-m-d') }}" required class="w-full border px-3 py-2 rounded" id="dateDemande" />
        </div>
        <div>
          <label>Montant règlement :</label>
          <input type="number" name="montant_regle" min="1" step="1" max="" required class="w-full border px-3 py-2 rounded" id="montantRegle" />
        </div>
      </div>

      <div class="mb-4 grid grid-cols-2 gap-4">
        <div>
          <label>Date Saisie :</label>
          <input type="text" name="date_enregg" value="{{ date('d/m/Y') }}" readonly class=" field-disabled w-full border px-3 py-2 rounded" />
        </div>
        <div>
          <label>Saisie effectuée par :</label>
          <input type="text" name="userSaisieReg" value="{{ auth()->user()->name }}" readonly class="field-disabled w-full border px-3 py-2 rounded" />
        </div>
      </div>

      <input type="hidden" name="MontantFacture" id="MontantFacture" />

      <div class="flex justify-end gap-3 pt-4 border-t">
        <button type="button" onclick="closeTraitementModal()" class="btn btn-secondary px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">Annuler</button>
        <button type="submit" class="btn btn-primary px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white">Valider</button>
      </div>
    </form>
  </div>
</div>

<style>
  .field-disabled {
    background-color: #f3f4f6; /* gris clair */
    color: #6b7280;           /* texte gris */
    cursor: not-allowed;
  }
</style>
