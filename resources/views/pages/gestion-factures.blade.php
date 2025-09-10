@extends('../layout/' . $layout)

@section('subhead')
    <title>Gestion Factures</title>
    <script src="//unpkg.com/alpinejs" defer></script>

    <!-- Alpine.js chargé dans le head pour éviter le flash -->
    <script src="//unpkg.com/alpinejs" defer></script>

    <!-- Style pour masquer complètement le modal avant qu'Alpine soit chargé -->
    <style>
        [x-cloak] { display: none !important; }

        /* Style additionnel pour s'assuresr que le modal reste caché */
        .modal-hidden {
            display: none !important;
        }

        /* Animation d'apparition plus fluide */
        .modal-overlay {
            backdrop-filter: blur(2px);
            animation: fadeIn 0.2s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
@endsection

@section('subcontent')
<div x-data="{
        openAddIndividuelle: false, openAddTiersPayant: false,
        openEdit: false,
        editingFacture: null,
        openEditModal(id) {
            fetch(`/factures/${id}`)
                .then(r => r.json())
                .then(data => {
                    this.editingFacture = data.facture;
                    this.openEdit = true;
                })
                .catch(() => alert('Erreur chargement facture'));
        },
       init() {
    @if ($errors->any())
        @if(Auth::user()->profil?->id == 4)
            this.openAddIndividuelle = true;
        @else
            this.openAddTiersPayant = true;
        @endif
    @endif
}

    }"
    x-cloak
    class="p-4">

<div class="flex items-center justify-between mt-8 mb-4">
    <h2 class="text-2xl font-semibold">
     @php
    $profilId = Auth::user()->profil?->id;
    @endphp

    <h2 class="text-2xl font-semibold">
        @if(in_array($profilId, [4]))
            Liste des Factures Individuelles
        @elseif(in_array($profilId, [8]))
            Liste des Factures Tiers-Payant
        @elseif(in_array($profilId, [3,5]))
            Liste des Factures Tiers-Payant et Individuelles
        @endif
    </h2>

    </h2>

    @if(in_array($profilId, [4, 3, 5]))
    <button @click="openAddIndividuelle = true" class="btn btn-primary">
        Nouvelle facture individuelle
    </button>
@endif

@if(in_array($profilId, [8, 3, 5]))
    <button @click="openAddTiersPayant = true" class="btn btn-primary">
        Nouvelle facture Tiers-Payant
    </button>
@endif

  </div>

    <div class="overflow-auto">
        <table class="table-auto w-full text-sm border">
            <thead class="bg-gray-100">
                   <tr>
                @if($profilId == 4)
                    <th class="px-4 py-2" >Assuré</th>
                    <th class="px-4 py-2" >Souscripteur</th>
                        @elseif($profilId == 8)
                            <th class="px-4 py-2" >Prestataire</th>
                            <th class="px-4 py-2" >N° Facture</th>
                        @else
                            <th class="px-4 py-2" >Assuré / Prestataire</th>
                            <th class="px-4 py-2" >Souscripteur / Référence</th>
                        @endif
                                <th>Période</th>
                        <th class="px-4 py-2" >Montant</th>
                        <th class="px-4 py-2" >N° Réception</th>
                        <th class="px-4 py-2" >Date Réception Courrier</th>
                        <th class="px-4 py-2" >Date Enregistrement Facture</th>
                        <th class="px-4 py-2" >Date de transmission Estimée</th>
                        <th class="px-4 py-2" >Date de transmission Effective</th>
                        <th class="px-4 py-2" >Date de retour Estimée</th>
                        <th class="px-4 py-2">Mise à jour</th>
                </tr>
            </thead>

            <tbody class=" text-center">
                @foreach($factures as $facture)
                    @if($profilId == 4)
            <td class="border px-4 py-2" >{{ $facture->Nom_Assure }}</td>
                    <td class="border px-4 py-2" >{{ $facture->souscripteur?->nom }}</td>
                @elseif($profilId == 8)
                    <td class="border px-4 py-2" >{{ $facture->prestataire?->nom }}</td>
                    <td class="border px-4 py-2" >{{ $facture->Reference_Facture }}</td>
                @else
                    <td class="border px-4 py-2" >{{ $facture->Nom_Assure ?? $facture->prestataire?->nom }}</td>
                    <td class="border px-4 py-2" >{{ $facture->souscripteur?->nom ?? $facture->Reference_Facture }}</td>
                @endif
                        <td class="border px-4 py-2" >{{ $facture->Date_Debut->format('d/m/Y') }} au {{ $facture->Date_Fin->format('d/m/Y') }}</td>
                        <td class="border px-4 py-2">{{ number_format($facture->Montant_Ligne, 0, ',', ' ') }}</td>
                         <td class="border px-4 py-2" >{{ $facture->Numero_Reception }}</td>
                        <td class="border px-4 py-2" >{{ optional($facture->Date_Reception)->format('d/m/Y') }}</td>
                        <td class="border px-4 py-2" >{{ optional($facture->Date_Enregistrement)->format('d/m/Y') }}</td>
                        <td class="border px-4 py-2" >{{ optional($facture->Date_Enregistrement)->format('d/m/Y') }}</td>
                        <td class="border px-4 py-2" >{{ optional($facture->datetransMedecin)->format('d/m/Y') }}</td>
                        <td class="border px-4 py-2" >{{ optional($facture->Date_Enregistrement?->addDay())->format('d/m/Y') }}</td>

                    @php
                        $estTraitee = $facture->estTraitee();
                    @endphp

                    <!-- Dans votre fichier gestion-facture.blade.php -->
<!-- Remplacez la section des boutons d'actions par ceci : -->

<td class="border px-4 py-2">
    <div class="flex items-center justify-between gap-2">
        <!-- Bouton modifier -->
        <button type="button" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded transition-colors trans"
                data-id="{{ $facture->Id_Ligne }}"
                data-prest="{{ $facture->Nom_Assure ?: ($facture->prestataire?->nom ?? '') }}"
                data-deb="{{ $facture->Date_Debut->format('Y-m-d') }}"
                data-fin="{{ $facture->Date_Fin->format('Y-m-d') }}"
                data-mont="{{ $facture->Montant_Ligne }}"
                title="Modifier">
            <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
        </button>

        @php
            $estTraitee = $facture->estTraitee();
            $estCloturee = ($facture->statut_Ligne == 4); // Statut 4 = Clôturée
        @endphp

        <!-- Bouton traiter -->
        @if($estTraitee)
            <button type="button"
                class="bg-gray-400 text-white px-3 py-1 rounded-md cursor-not-allowed opacity-60"
                disabled
                title="Facture déjà traitée">
                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </button>
        @else
            <button type="button"
                class="btn-traiter bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-md"
                data-ref="{{ $facture->Id_Ligne }}"
                data-fact="{{ $facture->Reference_Facture ?? '' }}"
                data-assures="{{ $facture->Nom_Assure ?? '' }}"
                data-souscripteur="{{ optional($facture->souscripteur)->nom ?? '' }}"
                data-prestataire="{{ optional($facture->prestataire)->nom ?? '' }}"
                data-dateenreg="{{ optional($facture->Date_Enregistrement)->format('Y-m-d') ?? '' }}"
                data-montant="{{ $facture->Montant_Ligne ?? 0 }}"
                data-montrejete="{{ $facture->montrejete ?? 0 }}"
                data-datetransmission="{{ optional($facture->Date_Transmission)->format('Y-m-d') ?? '' }}"
                data-agent="{{ auth()->user()->name }}"
                onclick="openTraitementModal(this)"
                title="Traiter la facture">
                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </button>
        @endif

        <!-- Bouton rejeter -->
        @if($estTraitee || $estCloturee)
            <button type="button"
                class="bg-gray-400 text-white px-3 py-1 rounded-md cursor-not-allowed opacity-60"
                disabled
                title="Facture {{ $estCloturee ? 'clôturée' : 'traitée' }} — rejet non autorisé">
                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        @else
            <button type="button"
                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-md btn-rejet"
                data-id="{{ $facture->Id_Ligne }}"
                data-assure="{{ $facture->Nom_Assure ?? $facture->prestataire?->nom }}"
                data-souscripteur="{{ $facture->souscripteur?->nom ?? $facture->Reference_Facture }}"
                data-mont="{{ $facture->Montant_Ligne }}"
                data-numrecept="{{ $facture->Numero_Reception }}"
                data-ref="{{ $facture->Reference_Facture }}"
                data-dateenreg="{{ optional($facture->Date_Enregistrement)->format('d/m/Y') }}"
                data-agent="{{ auth()->user()->name }}"
                onclick="openRejetModal(this)"
                title="Rejeter cette facture">
                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        @endif

        <!-- NOUVEAU : Bouton clôturer -->
        @if($estCloturee)
            <!-- Facture déjà clôturée - Affichage du statut -->
            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-md text-xs font-semibold flex items-center">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                Clôturée
            </span>
        @elseif($estTraitee)
            <!-- Facture traitée - Bouton clôturer actif -->
            <button type="button"
                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md btn-cloture"
                data-ref="{{ $facture->Id_Ligne }}"
                data-reference="{{ $facture->Reference_Facture ?? 'N/A' }}"
                data-assure="{{ $facture->Nom_Assure ?? $facture->prestataire?->nom }}"
                onclick="openClotureModal(this)"
                title="Clôturer cette facture">
                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </button>
        @else
            <!-- Facture non traitée - Bouton clôturer désactivé -->
            <button type="button"
                class="bg-gray-400 text-white px-3 py-1 rounded-md cursor-not-allowed opacity-60"
                disabled
                title="Facture non traitée — clôture non autorisée">
                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </button>
        @endif
    </div>
</td>
                    </tr>
                @endforeach
            </tbody>

        </table>
    </div>

    <!-- Modal Ajouter -->
<div x-show="openAddIndividuelle"
       x-cloak
       :class="{ 'modal-hidden': !openAddIndividuelle }"
       class="fixed inset-0 z-30 flex items-center justify-center bg-black bg-opacity-50 modal-overlay"
       @keydown.escape.window="openAddIndividuelle = false"
       style="display: none;">
    <div @click.away="openAddIndividuelle = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-90 translate-y-4"
         class="bg-white p-6 rounded-lg shadow-xl max-w-2xl w-full m-4 max-h-screen overflow-y-auto">

        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold">Ajouter une facture</h3>
            <button @click="openAdd = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4 text-sm">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('ligne_suivi.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div>
                    <label class="block mb-1 font-medium" for="assure">Assuré :</label>
                    <input type="text" id="assure" name="assure" value="{{ old('assure') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>

                    <div>
                        <label class="block mb-1 font-medium" for="date_debut">Période du :</label>
                        <input type="date" id="date_debut" name="date_debut" value="{{ old('date_debut') }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block mb-1 font-medium" for="date_fin">Au :</label>
                        <input type="date" id="date_fin" name="date_fin" value="{{ old('date_fin') }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    </div>


                    <div>
                        <label class="block mb-1 font-medium" for="date_saisie">Date de saisie :</label>
                        <input type="text" id="date_saisie" name="date_saisie" value="{{ now()->format('Y-m-d') }}" readonly
                            class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed text-gray-500">
                    </div>

                <div>
                    <label class="block mb-1 font-medium" for="idSouscripteur">Souscripteur :</label>
                    <select id="idSouscripteur" name="idSouscripteur" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Sélectionner</option>
                        @foreach ($souscripteurs as $s)
                            <option value="{{ $s->id }}" @selected(old('idSouscripteur') == $s->id)>{{ $s->nom }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1 font-medium" for="numero_reception">N° Réception :</label>
                    <input type="text" id="numero_reception" name="numero_reception" value="{{ old('numero_reception') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block mb-1 font-medium" for="reference_facture">Référence Facture :</label>
                    <input type="text" id="reference_facture" name="reference_facture" value="{{ old('reference_facture') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block mb-1 font-medium">Évacuation ?</label>
                    <div class="flex items-center space-x-2 mt-1">
                        <input type="checkbox" id="is_evac" name="is_evac" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="is_evac" class="mb-0 select-none cursor-pointer">Evacuation( Oui / Non )</label>
                    </div>
                </div>

                <div>
                    <label class="block mb-1 font-medium" for="montant">Montant :</label>
                    <input type="number" step="0.01" id="montant" name="montant" value="{{ old('montant') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block mb-1 font-medium" for="mois">Mois :</label>
                    <select id="mois" name="mois" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Sélectionner</option>
                        @foreach ($moisList as $mois)
                            <option value="{{ $mois->Id_mois }}" @selected(old('mois') == $mois->Id_mois)>{{ $mois->libelle_mois }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1 font-medium" for="annee">Année :</label>
                 <select id="annee" name="annee" required class="...">
    <option value="">Sélectionner</option>
    @foreach ($annees as $annee)
        <option value="{{ $annee->libelle_annee }}" @selected(old('annee') == $annee->libelle_annee)>
            {{ $annee->libelle_annee }}
        </option>
    @endforeach
</select>


                </div>

            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button" @click="openAdd = false"
                    class="px-4 py-2 bg-gray-100 rounded border border-gray-300 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition">
                    Annuler
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>


<div x-show="openAddTiersPayant"
       x-cloak
       :class="{ 'modal-hidden': !openAddTiersPayant }"
       class="fixed inset-0 z-30 flex items-center justify-center bg-black bg-opacity-50 modal-overlay"
       @keydown.escape.window="openAddTiersPayant = false"
       style="display: none;">
    <div @click.away="openAddTiersPayant = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-90 translate-y-4"
         class="bg-white p-6 rounded-lg shadow-xl max-w-2xl w-full m-4 max-h-screen overflow-y-auto">

        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold">Ajouter une facture Tiers-Payant</h3>
            <button @click="openAdd = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4 text-sm">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('ligne_suivi.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="source" value="tiersPayant">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div>
                    <label class="block mb-1 font-medium">Prestataire :</label>
                    <select name="Code_Prestataire" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Sélectionner</option>
                        @foreach($prestataires as $prestataire)
                            <option value="{{ $prestataire->id }}" @selected(old('Code_Prestataire') == $prestataire->id)>
                                {{ $prestataire->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1 font-medium">N° Réception :</label>
                    <input type="text" name="Numero_Reception" value="{{ old('Numero_Reception') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block mb-1 font-medium">Référence Facture :</label>
                    <input type="text" name="Reference_Facture" value="{{ old('Reference_Facture') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block mb-1 font-medium">Mois :</label>
                    <select name="Mois_Facture" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Sélectionner</option>
                        @foreach($moisList as $mois)
                            <option value="{{ $mois->Id_mois }}" @selected(old('Mois_Facture') == $mois->Id_mois)>
                                {{ $mois->libelle_mois }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1 font-medium">Année :</label>
                    <select name="Annee_Facture" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Sélectionner</option>
                        @foreach($annees as $annee)
                            <option value="{{ $annee->libelle_annee }}" @selected(old('Annee_Facture') == $annee->libelle_annee)>
                                {{ $annee->libelle_annee }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1 font-medium">Période du :</label>
                    <input type="date" name="Date_Debut" value="{{ old('Date_Debut') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block mb-1 font-medium">Au :</label>
                    <input type="date" name="Date_Fin" value="{{ old('Date_Fin') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block mb-1 font-medium">Montant :</label>
                    <input type="number" step="0.01" name="Montant_Ligne" value="{{ old('Montant_Ligne') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block mb-1 font-medium">Date Enregistrement :</label>
                    <input type="text" name="Date_Enregistrement" value="{{ now()->format('Y-m-d') }}" readonly
                        class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed text-gray-500">
                </div>

            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button" @click="openAdd = false"
                    class="px-4 py-2 bg-gray-100 rounded border border-gray-300 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition">
                    Annuler
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>



<div id="transM" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
        <div id="trans_ici">
            <!-- Contenu chargé dynamiquement -->
        </div>
    </div>
</div>

@include('pages.edit_modalTraiter')
@include('pages.rejet-facture')
@include('pages.cloture-facture')
</div>

@endsection
@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>

function openTraitementModal(button) {
    // Récupération des données depuis les attributs data
    const ref = button.dataset.ref;
    const facture = button.dataset.fact;
    const assures = button.dataset.assures;
    const souscripteur = button.dataset.souscripteur;
    const prestataire = button.dataset.prestataire;
    const dateEnreg = button.dataset.dateenreg;
    const montant = parseFloat(button.dataset.montant || 0);
    const montantRejete = parseFloat(button.dataset.montrejete || 0);
    const dateTransmission = button.dataset.datetransmission;

    console.log('=== DEBUG MODAL TRAITEMENT ===');
    console.log('Données récupérées depuis les attributs data:', {
        ref, facture, assures, souscripteur, prestataire,
        dateEnreg, montant, montantRejete, dateTransmission
    });

    // Vérification de la présence des éléments DOM
    if (!document.getElementById('traitementModal')) {
        console.error('Modal traitementModal introuvable');
        return;
    }

    // Remplir les champs du formulaire
    document.getElementById('ref').value = ref || '';

    // Fonction pour vérifier si une valeur est vide ou nulle
    function isEmpty(value) {
        const result = !value || value === 'null' || value === 'undefined' || value.toString().trim() === '';
        console.log(`isEmpty("${value}") = ${result}`);
        return result;
    }

    // Pour le champ "Souscripteur / N° Facture" - logique selon le profil
    const factField = document.getElementById('fact');
    if (!isEmpty(souscripteur)) {
        factField.value = souscripteur; // Afficher le souscripteur
        console.log('Affichage souscripteur:', souscripteur);
    } else if (!isEmpty(facture)) {
        factField.value = facture; // Sinon afficher la référence facture
        console.log('Affichage référence facture:', facture);
    } else {
        factField.value = ''; // Champ vide si rien n'est disponible
        console.log('Aucune valeur pour souscripteur/facture');
    }

    // Pour le champ "Assuré / Prestataire" - CORRECTION DE LA LOGIQUE
    const assuresField = document.getElementById('assures');
    if (!isEmpty(assures)) {
        assuresField.value = assures; // Afficher l'assuré
        console.log('Affichage assuré:', assures);
    } else if (!isEmpty(prestataire)) {
        assuresField.value = prestataire; // Sinon afficher le prestataire
        console.log('Affichage prestataire:', prestataire);
    } else {
        assuresField.value = ''; // Champ vide si rien n'est disponible
        console.log('Aucune valeur pour assuré/prestataire');
    }

    console.log('Valeurs finales des champs:', {
        fact: factField.value,
        assures: assuresField.value

    });

    // Date d'enregistrement
    if (!isEmpty(dateEnreg)) {
        const date = new Date(dateEnreg);
        if (!isNaN(date.getTime())) { // Vérifier que la date est valide
            document.getElementById('dateEnreg').value = date.toLocaleDateString('fr-FR');
        }
    }

    // Montant facture
    document.getElementById('montantF').value = montant ? new Intl.NumberFormat('fr-FR').format(montant) : '';

    // Montant rejeté
    document.getElementById('montrejete').value = montantRejete ? new Intl.NumberFormat('fr-FR').format(montantRejete) : '0';

    // Date transmission
    if (!isEmpty(dateTransmission)) {
        const date = new Date(dateTransmission);
        if (!isNaN(date.getTime())) { // Vérifier que la date est valide
            document.getElementById('dateTransmission').value = date.toLocaleDateString('fr-FR');
        }
    }

    // Mettre à jour le max du montant à régler
    const montantMax = montant - montantRejete;
    const montantRegle = document.getElementById('montantRegle');
    if (montantRegle) {
        montantRegle.max = montantMax;
        montantRegle.value = ""; // reset input
    }

    // Champ hidden pour le montant facture
    document.getElementById('MontantFacture').value = montant;

    // Limiter la date de demande à aujourd'hui
    document.getElementById('dateDemande').max = new Date().toISOString().split('T')[0];

    console.log('=== FIN DEBUG MODAL ===');

    // Afficher la modale
    document.getElementById('traitementModal').classList.remove('hidden');
}

function closeTraitementModal() {
    document.getElementById('traitementModal').classList.add('hidden');
}

// Rendre les fonctions accessibles globalement
window.openTraitementModal = openTraitementModal;
window.closeTraitementModal = closeTraitementModal;



function openRejetModal(button) {
    const modal = document.getElementById('rejetModal');
    if (!modal) {
        console.error('Modal rejetModal non trouvé');
        return;
    }

    // Récupérer toutes les données du bouton
    const data = {
        id: button.dataset.id,
        assure: button.dataset.assure,
        souscripteur: button.dataset.souscripteur,
        mont: button.dataset.mont,
        numrecept: button.dataset.numrecept,
        ref: button.dataset.ref,
        dateenreg: button.dataset.dateenreg,
        agent: button.dataset.agent
    };

    // Champs cachés
    document.getElementById('rejet_id').value = data.id;
    document.getElementById('rejet_mont').value = data.mont;
    document.getElementById('rejet_montrejet').value = data.mont;
    document.getElementById('rejet_numrecept').value = data.numrecept;
    document.getElementById('rejet_agent').value = data.agent;

    // Champs visibles
    document.getElementById('rejet_assure').value = data.assure;
    document.getElementById('rejet_souscripteur').value = data.souscripteur;
    document.getElementById('rejet_ref_facture').value = data.ref;
    document.getElementById('rejet_numreception_affiche').value = data.numrecept;
    document.getElementById('rejet_montant_total').value = Number(data.mont).toLocaleString() + " FCFA";
    document.getElementById('rejet_montant_rejete').value = Number(data.mont).toLocaleString() + " FCFA";
    document.getElementById('rejet_date_enreg').value = data.dateenreg;
    document.getElementById('rejet_agent_nom').value = data.agent;

    // Affiche le modal
    modal.style.display = 'flex';
}

// Fonction pour fermer le modal
function closeRejetModal() {
    const modal = document.getElementById('rejetModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Event listener pour tous les boutons de rejet
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.btn-rejet');
    console.log('Boutons de rejet trouvés:', buttons.length);

    buttons.forEach(button => {
        button.addEventListener('click', function() {
            openRejetModal(this);
        });
    });

    // Fermer le modal si on clique à l'extérieur
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('rejetModal');
        if (e.target === modal) {
            closeRejetModal();
        }
    });
});



$(document).ready(function () {
    console.log('Document ready - Tailwind modal');

    // Initialisation DataTable (sans Bootstrap)
    try {
        $('#dtHorizontalVerticalExample').DataTable({
            scrollX: true,
            scrollY: '500px',
            order: [[5, 'desc']],
            dom: 'frtip', // Enlever les éléments Bootstrap
            language: {
                search: "Rechercher:",
                lengthMenu: "Afficher _MENU_ éléments",
                info: "Affichage de _START_ à _END_ sur _TOTAL_ éléments",
                paginate: {
                    first: "Premier",
                    last: "Dernier",
                    next: "Suivant",
                    previous: "Précédent"
                }
            }
        });
        console.log('DataTable initialisé');
    } catch (e) {
        console.error('Erreur DataTable:', e);
    }

    // Fonction pour ouvrir le modal
    function openModal() {
        document.getElementById('transM').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    // Fonction pour fermer le modal
    function closeModal() {
        document.getElementById('transM').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        $('#trans_ici').empty();
        console.log('Modal fermé et nettoyé');
    }

    // Fermeture au clic sur l'overlay
    $(document).on('click', '#transM', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    // Fermeture avec Escape
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && !document.getElementById('transM').classList.contains('hidden')) {
            closeModal();
        }
    });



    // Clic sur bouton modifier
    $(document).on('click', '.trans', function (e) {
        e.preventDefault();
        console.log('Clic sur bouton modifier');

        const button = $(this);
        const id = button.data('id');
        const mont = button.data('mont');
        const prest = button.data('prest');
        const deb = button.data('deb');
        const fin = button.data('fin');

        console.log('Données:', { id, mont, prest, deb, fin });

        if (!id) {
            alert('ID de facture manquant');
            return;
        }

        // Loader
        $('#trans_ici').html(`
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h4 class="text-lg font-semibold text-gray-800">Chargement...</h4>
                <button type="button" class="text-gray-500 hover:text-red-500 text-2xl" onclick="closeModal()">&times;</button>
            </div>
            <div class="flex items-center justify-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                <p class="ml-4 text-gray-600">Chargement du formulaire...</p>
            </div>
        `);

        // Ouvrir le modal
        openModal();

        // Appel AJAX
        $.ajax({
            url: "{{ route('ligne_suivi.editModal') }}",
            method: 'GET',
            data: { id, mont, prest, deb, fin },
            success: function (html) {
                console.log('Contenu modal reçu');
                $('#trans_ici').html(html);
            },
            error: function (xhr, status, error) {
                console.error('Erreur AJAX:', xhr, status, error);

                $('#trans_ici').html(`
                    <div class="flex justify-between items-center border-b pb-3 mb-4">
                        <h4 class="text-lg font-semibold text-red-600">Erreur</h4>
                        <button type="button" class="text-gray-500 hover:text-red-500 text-2xl" onclick="closeModal()">&times;</button>
                    </div>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Erreur lors du chargement: ${xhr.status}</span>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="button" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded" onclick="closeModal()">
                            Fermer
                        </button>
                    </div>
                `);
            }
        });
    });

    // Rendre closeModal globale
    window.closeModal = closeModal;

    console.log('Boutons .trans trouvés:', $('.trans').length);
});
</script>

