@extends('../layout/' . $layout)

@section('subcontent')
<div class="max-w-3xl mx-auto p-6 bg-white rounded shadow">

    <h3 class="text-xl font-bold mb-4">Règlement facture</h3>

    <form action="{{ route('ligne_suivi.regler', $facture->Id_Ligne) }}" method="POST">
        @csrf

        <input type="hidden" name="ref" value="{{ $facture->Id_Ligne }}">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">

            <div>
                <label class="block font-medium">N° Facture / Souscripteur :</label>
                <input type="text" readonly value="{{ $profil == 7 ? ($facture->souscripteur?->nom ?? '') : ($facture->Reference_Facture ?? '') }}" class="w-full border rounded px-3 py-2 bg-gray-100" />
            </div>

            <div>
                <label class="block font-medium">Assuré / Prestataire :</label>
                <input type="text" readonly value="{{ $profil == 7 ? ($facture->Nom_Assure ?? '') : ($facture->prestataire?->nom ?? '') }}" class="w-full border rounded px-3 py-2 bg-gray-100" />
            </div>

            <div>
                <label class="block font-medium">N° Demande :</label>
                <input type="text" readonly value="{{ $facture->Numero_Demande ?? '' }}" class="w-full border rounded px-3 py-2 bg-gray-100" />
            </div>

            <div>
                <label class="block font-medium">Date Enregistrement :</label>
                <input type="text" readonly value="{{ optional($facture->Date_Enregistrement)->format('d/m/Y') }}" class="w-full border rounded px-3 py-2 bg-gray-100" />
            </div>

            <div>
                <label class="block font-medium">Date Demande :</label>
                <input type="text" readonly value="{{ optional($facture->Date_Demande)->format('d/m/Y') }}" class="w-full border rounded px-3 py-2 bg-gray-100" />
            </div>

            <div>
                <label class="block font-medium">Date Transmission :</label>
                <input type="text" readonly value="{{ optional($facture->Date_Transmission)->format('d/m/Y') }}" class="w-full border rounded px-3 py-2 bg-gray-100" />
            </div>

            <div>
                <label class="block font-medium">Montant Facture :</label>
                <input type="text" readonly value="{{ number_format($facture->Montant_Ligne, 0, ',', ' ') }}" class="w-full border rounded px-3 py-2 bg-gray-100" />
            </div>

            <div>
                <label class="block font-medium">Montant Règlement :</label>
                <input type="text" readonly value="{{ number_format($facture->Montant_Reglement ?? 0, 0, ',', ' ') }}" class="w-full border rounded px-3 py-2 bg-gray-100" />
            </div>

            <div>
                <label class="block font-medium">Montant Rejeté :</label>
                <input type="text" readonly value="{{ number_format(($facture->Montant_Ligne - ($facture->Montant_Reglement ?? 0)), 0, ',', ' ') }}" class="w-full border rounded px-3 py-2 bg-gray-100" />
            </div>

            <div>
                <label class="block font-medium">Date Saisie :</label>
                <input type="text" readonly value="{{ now()->format('d/m/Y') }}" class="w-full border rounded px-3 py-2 bg-gray-100" />
            </div>

            <div>
                <label class="block font-medium">Chèque enregistré par :</label>
                <input type="text" readonly value="{{ auth()->user()->name }}" class="w-full border rounded px-3 py-2 bg-gray-100" />
            </div>

            <div>
                <label class="block font-medium" for="numero_cheque">N° Chèque :</label>
                <input type="text" name="numero_cheque" id="numero_cheque" value="{{ old('numero_cheque', $facture->Numero_Cheque) }}" required
                    class="w-full border rounded px-3 py-2 @error('numero_cheque') border-red-500 @enderror" />
                @error('numero_cheque')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('gestion-factures') }}" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Valider</button>
        </div>

    </form>
</div>
@endsection
