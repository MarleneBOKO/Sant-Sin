@extends('../layout/' . $layout)

@section('subhead')
    <title>Gestion Factures</title>
    <script src="//unpkg.com/alpinejs" defer></script>

    <!-- Alpine.js chargé dans le head pour éviter le flash -->

    <!-- Style pour masquer complètement le modal avant qu'Alpine soit chargé -->
    <style>
        [x-cloak] { display: none !important; }

        /* Style additionnel pour s'assurer que le modal reste caché */
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
<div x-data="factureSelection()" x-cloak class="p-4">

<div class="flex flex-col">
   <div>
         <h2 class="text-2xl font-semibold">
  @php
    $profilCode = Auth::user()->profil?->code_profil;
@endphp

     <h2 class="text-2xl font-semibold">
        @if(in_array($profilCode, ['RSI', 'RRSI']))
            Liste des Factures Individuelles
        @elseif(in_array($profilCode, ['RSTP', 'RRSTP']))
            Liste des Factures Tiers-Payant
        @elseif(in_array($profilCode, ['RSIN', 'ADMIN']))
            Liste des Factures Tiers-Payant et Individuelles
        @endif
    </h2>
   </div>
<div class="flex items-center justify-between mt-8 mb-4">
         {{-- Boutons d'ajout de factures --}}
@if(in_array($profilCode, ['RSI', 'RSIN', 'ADMIN', 'RRSI']))
    <button @click="openAddIndividuelleModal()"
            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Nouvelle facture individuelle
    </button>
@endif

@if(in_array($profilCode, ['RSTP', 'RSIN', 'ADMIN', 'RRSTP']))
    <button @click="openAddTiersPayantModal()"
            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 ml-3">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Nouvelle facture Tiers-Payant
    </button>
@endif
{{-- Bouton de validation --}}
<button x-show="selectedFactures.length > 0"
        @click="openValiderModal = true"
        class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-black font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 ml-3"
        type="button">
    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    Valider
</button>

</div>

  <div class="mt-8 mt-4">
    <form method="GET" action="{{ url()->current() }}" class="flex items-center space-x-2 w-8">
        <input type="text" name="search" value="{{ request()->get('search', '') }}"
               placeholder="Rechercher par assuré, référence, numéro de réception, souscripteur ou prestataire..."
               class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            Rechercher
        </button>
        @if(request()->has('search'))
            <a href="{{ url()->current() }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                Effacer
            </a>
        @endif
    </form>
</div>


  </div>


    <div class="overflow-auto">
        @if(session('success'))
    <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
        {{ session('success') }}
    </div>

@endif

      <table class="table-auto w-full text-sm border">
            <thead class="bg-gray-100">
                   <tr>
                <th class="px-4 py-2">Validé</th>
                @if($profilCode === 'RSI' || $profilCode === 'RRSI')
                    <th class="px-4 py-2">Assuré</th>
                    <th class="px-4 py-2">Souscripteur</th>
                @elseif($profilCode === 'RSTP' || $profilCode === 'RRSTP' )
                    <th class="px-4 py-2">Prestataire</th>
                    <th class="px-4 py-2">N° Facture</th>
                @else
                    <th class="px-4 py-2">Assuré / Prestataire</th>
                    <th class="px-4 py-2">Souscripteur / Référence</th>
                @endif
                                <th>Période</th>
                        <th class="px-4 py-2" >Montant</th>
                        <th class="px-4 py-2" >N° Réception</th>
                        <th class="px-4 py-2" >Date Réception Courrier</th>
                        <th class="px-4 py-2" >Date Enregistrement Facture</th>
                        <th class="px-4 py-2" >Date de transmission Estimée</th>
                        <th class="px-4 py-2" >Date de transmission Effective</th>
                        <th class="px-4 py-2" >Date de retour Estimée</th>
                         <th class="px-4 py-2">Statut</th>
                         <th class="px-4 py-2">Action</th>
                </tr>
            </thead>

            <tbody class=" text-center">
                @foreach($factures as $facture)
                <tr>
                    <td class="border px-4 py-2 text-center">
                        @if($facture->Statut_Ligne != 4 && $facture->rejete != 1 && $facture->Statut_Ligne != 3 && $facture->Statut_Ligne == 1)
                            <input type="checkbox"
                                :checked="isSelected({{ $facture->Id_Ligne }})"
                                @change="toggleFacture({{ $facture->Id_Ligne }})">

                        @endif
                    </td>

                                   @if($profilCode === 'RSI')
                        <td class="border px-4 py-2">{{ $facture->Nom_Assure }}</td>
                        <td class="border px-4 py-2">{{ $facture->souscripteur?->nom }}</td>
                    @elseif($profilCode === 'RSTP')
                        <td class="border px-4 py-2">{{ $facture->prestataire?->nom }}</td>
                        <td class="border px-4 py-2">{{ $facture->Reference_Facture }}</td>
                    @else
                        <td class="border px-4 py-2">{{ $facture->Nom_Assure ?? $facture->prestataire?->nom }}</td>
                        <td class="border px-4 py-2">{{ $facture->souscripteur?->nom ?? $facture->Reference_Facture }}</td>
                    @endif
                        <td class="border px-4 py-2" >{{ $facture->Date_Debut->format('d/m/Y') }} au {{ $facture->Date_Fin->format('d/m/Y') }}</td>
                        <td class="border px-4 py-2">{{ number_format($facture->Montant_Ligne, 0, ',', ' ') }}</td>
                         <td class="border px-4 py-2" >{{ $facture->Numero_Reception }}</td>
                        <td class="border px-4 py-2" >{{ optional($facture->Date_Reception)->format('d/m/Y') }}</td>
                        <td class="border px-4 py-2" >{{ optional($facture->Date_Enregistrement)->format('d/m/Y') }}</td>
                        <td class="border px-4 py-2" >{{ optional($facture->Date_Enregistrement)->format('d/m/Y') }}</td>
                        <td class="border px-4 py-2" >{{ optional($facture->datetransMedecin)->format('d/m/Y') }}</td>
                        <td class="border px-4 py-2" >{{ optional($facture->Date_Enregistrement?->addDay())->format('d/m/Y') }}</td>
                        <td class="border px-4 py-2">
                                   @php
                                $statut = $facture->Statut_Ligne;
                                $estRejetee = ($facture->rejete == 1);
                                 $estCloturee = ($statut == 4);
                            @endphp

                             @if($statut == 0)
                                    <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded text-xs font-semibold flex items-center">
                                        <i class="fas fa-clock mr-1"></i> Non Traitée

                                    </span>
                                @endif

                                @if($statut == 5)
                                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded text-xs font-semibold flex items-center">
                                        <i class="fas fa-paper-plane mr-1"></i> Transmise Médecin
                                    </span>
                                @endif

                                @if($statut == 6)
                                    <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded text-xs font-semibold flex items-center">
                                        <i class="fas fa-reply mr-1"></i> Retour Médecin Reçu
                                    </span>
                                @endif

                                @if($statut == 1)
                                    <span class="bg-teal-100 text-teal-800 px-3 py-1 rounded text-xs font-semibold flex items-center">
                                        <i class="fas fa-check mr-1"></i> Traitée
                                    </span>
                                @endif

                                @if($statut == 2)
                                    <span class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded text-xs font-semibold flex items-center">
                                        <i class="fas fa-share mr-1"></i> Transmise Tréso
                                    </span>
                                @endif

                                @if($statut == 3)
                                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded text-xs font-semibold flex items-center">
                                        <i class="fas fa-money-check-alt mr-1"></i> Réglée
                                    </span>
                                @endif

                                @if($statut == 4)
                                    <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded text-xs font-semibold flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd"/>
                                        </svg>
                                        Clôturée
                                    </span>
                                @endif

                                @if($estRejetee)
                                    <span class="bg-red-100 text-red-800 px-3 py-1 rounded text-xs font-semibold flex items-center">
                                        <i class="fas fa-times-circle mr-1"></i> Rejetée
                                    </span>
                                @endif
                        </td>

                    @php
                        $estTraitee = $facture->estTraitee();
                    @endphp


                      <td class="border px-4 py-2">

                           <div class="flex items-center gap-2 justify-center">

                            @php
                                $statut = $facture->Statut_Ligne;
                                $estRejetee = ($facture->rejete == 1);
                                $estCloturee = ($statut == 4);
                                $estReglee   = ($statut == 3);

                                $user = Auth::user();
                               $estCreateur = trim(strtolower($facture->Redacteur)) === trim(strtolower($user->name));



                                $codeProfil = $user->profil->code_profil ?? null;
                                $estResponsable = in_array($codeProfil, ['RRSTP', 'RRSI']);

                                $peutModifier = false;

                                // 👤 Créateur → uniquement statut 0
                                if ($estCreateur) {
                                    $peutModifier = (
                                        $statut == 0 &&
                                        !$estRejetee &&
                                        !$estCloturee &&
                                        !$estReglee
                                    );
                                }

                                    // 👔 Responsable → même si statut ≠ 0
                                    if ($estResponsable) {
                                        $peutModifier = (
                                            !$estRejetee &&
                                            !$estCloturee &&
                                            !$estReglee
                                        );
                                    }
                                @endphp


                               @if ($peutModifier)
                                <button type="button"
                                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded trans"
                                        data-id="{{ $facture->Id_Ligne }}"
                                        data-prest="{{ $facture->Nom_Assure ?: ($facture->prestataire?->nom ?? '') }}"
                                        data-deb="{{ $facture->Date_Debut->format('Y-m-d') }}"
                                        data-fin="{{ $facture->Date_Fin->format('Y-m-d') }}"
                                        data-mont="{{ $facture->Montant_Ligne }}"
                                        title="Modifier">
                                    ✏️
                                </button>
                            @endif




                                {{-- Bouton TRAITER (uniquement si retour reçu du médecin : statut = 6) --}}
                                @if($statut == 6 && !$estRejetee)
                                    <button type="button"
                                            class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded"
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
                                            title="Traiter la facture (retour médecin reçu)">
                                        <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>

                                    </button>
                                @endif

                                {{-- Bouton REJETER (uniquement si pas clôturée et pas déjà rejetée) --}}
                              @if(!$estCloturee && !$estRejetee && !$estReglee)
                                    <button type="button"
                                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded"
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

                                {{-- Bouton RÉGLER (uniquement si transmise à la tréso : statut = 2) --}}
                                @if($statut == 2 && !$estRejetee)
                                    <button type="button"
                                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded"
                                            onclick="openReglerModal(this)"
                                            data-id="{{ $facture->Id_Ligne }}"
                                            data-ref="{{ $facture->Reference_Facture ?? '' }}"
                                            data-fact="{{ $facture->souscripteur?->nom ?? $facture->Reference_Facture ?? '' }}"
                                            data-assure="{{ $facture->Nom_Assure ?? $facture->prestataire?->nom ?? '' }}"
                                            data-numdem="{{ $facture->Numero_demande }}"
                                            data-dateenreg="{{ optional($facture->Date_Enregistrement)->format('d/m/Y') }}"
                                            data-datedemande="{{ $facture->Date_Demande ? \Carbon\Carbon::parse($facture->Date_Demande)->format('d/m/Y') : '' }}"
                                            data-datetransmission="{{ optional($facture->Date_Transmission)->format('d/m/Y') }}"
                                            data-montantfacture="{{ number_format($facture->Montant_Ligne, 0, ',', ' ') }}"
                                            data-montantreglement="{{ number_format($facture->Montant_Reglement ?? 0, 0, ',', ' ') }}"
                                            data-montantrejete="{{ number_format(($facture->Montant_Ligne - ($facture->Montant_Reglement ?? 0)), 0, ',', ' ') }}"
                                            data-datesaisie="{{ now()->format('d/m/Y') }}"
                                            data-user="{{ auth()->user()->name }}"
                                            data-ncheque="{{ $facture->Numero_Cheque ?? '' }}"
                                            title="Régler cette facture (transmise à la tréso)">
                                        <i class="fas fa-money-check-alt"></i>

                                    </button>
                                @endif

                                {{-- Bouton CLÔTURER (uniquement si réglée : statut = 3) --}}
                               @if($estReglee && !$estCloturee)
                                    <button type="button"
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded"
                                            data-ref="{{ $facture->Id_Ligne }}"
                                            data-reference="{{ $facture->Reference_Facture ?? 'N/A' }}"
                                            data-assure="{{ $facture->Nom_Assure ?? $facture->prestataire?->nom }}"
                                            onclick="openClotureModal(this)"
                                            title="Clôturer cette facture (réglée)">
                                        <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>

                                    </button>
                                @endif

                            </div>
                      </td>
                </tr>


                            </tr>
                @endforeach
            </tbody>

        </table>


        <div class="mt-4 flex justify-center">
    {{ $factures->links() }}
</div>
    </div>

    <!-- Modal Ajouter -->
<div x-show="openAddIndividuelle"
     x-cloak
     id="openAddIndividuelle"
     :class="{ 'modal-hidden': !openAddIndividuelle }"
     class="fixed inset-0 z-30 flex items-center justify-center bg-black bg-opacity-50 modal-overlay"
     @keydown.escape.window="openAddIndividuelle = false"
     @click.away="openAddIndividuelle = false"
     style="display: none;">
    <div x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-90 translate-y-4"
         class="bg-white p-6 rounded-lg shadow-xl max-w-2xl w-full m-4 max-h-screen overflow-y-auto">

        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold">Ajouter une facture</h3>
            <button @click="openAddIndividuelle = false" class="text-gray-400 hover:text-gray-600 transition">  <!-- Corrigé -->
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

        <form action="{{ route('ligne_suivi.store') }}" method="POST" name="source" class="space-y-4">
            @csrf
                                        <input type="hidden" name="source" value="individuelle">

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
                        <input type="date" id="date_fin" name="date_fin" value="{{ old('date_fin') }}"   min="{{ old('date_debut') }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    </div>


                    <div>
                        <label class="block mb-1 font-medium" for="date_saisie">Date de saisie :</label>
                        <input type="text" id="date_saisie" name="date_saisie" value="{{ now()->format('Y-m-d') }}" readonly
                            class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed text-gray-500">
                    </div>

                <div>
                    <label class="block mb-1 font-medium" for="idSouscripteur">Souscripteur :</label>
                    <select id="idSouscripteur" name="idSouscripteur" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
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
               <button type="button" @click="openAddIndividuelle = false"
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
     id="openAddTiersPayant"
     :class="{ 'modal-hidden': !openAddTiersPayant }"
     class="fixed inset-0 z-30 flex items-center justify-center bg-black bg-opacity-50 modal-overlay"
     @keydown.escape.window="openAddTiersPayant = false"
     @click.away="openAddTiersPayant = false"
     style="display: none;">
    <div x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-90 translate-y-4"
         class="bg-white p-6 rounded-lg shadow-xl max-w-2xl w-full m-4 max-h-screen overflow-y-auto">

        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold">Ajouter une facture Tiers-Payant</h3>
            <button @click="openAddTiersPayant = false" class="text-gray-400 hover:text-gray-600 transition">  <!-- Corrigé -->
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
                <select name="Code_partenaire" id="prestataire-select" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Sélectionner</option>
                    @foreach($prestataires as $prestataire)
                        <option value="{{ $prestataire->id }}" @selected(old('Code_partenaire') == $prestataire->id)>
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
                    <label class="block mb-1 font-medium">N° Facture :</label>
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
                    <input type="date" name="Date_Fin" value="{{ old('Date_Fin') }}"   min="{{ old('date_debut') }}" required
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


    <div x-show="openValiderModal" x-cloak
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div @click.away="openValiderModal = false" class="bg-white p-6 rounded shadow max-w-md w-full">
            <h3 class="text-lg font-bold mb-4">Transmission de Facture à la trésorerie</h3>
            <form x-ref="validerForm" method="POST" action="{{ route('ligne_suivi.valider') }}" @submit.prevent="submitValidation">
                @csrf
                <input type="hidden" name="factures" x-ref="factures" />
                <div>
                    <label for="date_transmission" class="block mb-1 font-medium">Date de transmission :</label>
                    <input type="date" id="date_transmission" name="date_transmission" required
                        class="w-full px-3 py-2 border rounded" max="{{ date('Y-m-d') }}" />
                </div>
                <div class="mt-4 flex justify-end space-x-2">
                    <button type="button" @click="openValiderModal = false"  class="px-4  bg-red-600 text-white rounded hover:bg-red-700 focus:outline-none  focus:ring-red-500 transition">Annuler</button>
                    <button type="submit" class="px-4  bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none  focus:ring-blue-500 transition">Valider</button>
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



<div x-data="reglerModal()" x-cloak>
    <div x-show="isOpen"
         x-transition
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
         @keydown.escape.window="close()">

        <div @click.stop
             class="bg-white p-6 rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-screen overflow-y-auto">

            <div class="flex justify-between items-center mb-4 pb-2 border-b">
                <h3 class="text-xl font-bold">Référence chèque</h3>
                <button @click="close()" class="text-gray-400 hover:text-gray-600 text-3xl">&times;</button>
            </div>

            <form method="POST" :action="`/ligne_suivi/regler/${formData.id}`" @submit.prevent="submitForm($event)">
                @csrf

                <input type="hidden" name="ref" :value="formData.id" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                   <template x-for="field in fields" :key="field.key">
            <div>
                <label class="block font-medium text-sm mb-1" x-text="field.label"></label>
                <input type="text"
                    readonly
                    class="w-full border border-gray-300 rounded px-3 py-2
                  bg-gray-200 text-gray-500
                  cursor-not-allowed select-none focus:outline-none text-sm"
                    :value="formData[field.key] ? formData[field.key] : ''" /> </div>
                    </template>

                            <div>
                                <label class="block font-medium text-sm mb-1">N° Chèque <span class="text-red-500">*</span> :</label>
                                <input type="text" name="numero_cheque" x-model="formData.ncheque" required
                                    class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500" />
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 pt-4 border-t">
                            <button type="button" @click="close()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded">Annuler</button>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">Valider</button>
                        </div>
                    </form>
                </div>
            </div>
</div>

@if ($errors->any())
    <script>
        window.__OPEN_MODAL__ = "{{ old('source') }}";
    </script>
@endif


@include('pages.edit_modalTraiter')
@include('pages.rejet-facture')
@include('pages.cloture-facture')
</div>

@endsection
@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<!-- CSS de Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- JS de Select2 (après jQuery) -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
            // ============================================
            // PARTIE 1 : FONCTIONS GLOBALES (jQuery/Vanilla JS)
            // ============================================

            function openTraitementModal(button) {
                const ref = button.dataset.ref;
                const facture = button.dataset.fact;
                const assures = button.dataset.assures;
                const souscripteur = button.dataset.souscripteur;
                const prestataire = button.dataset.prestataire;
                const dateEnreg = button.dataset.dateenreg;
                const montant = parseFloat(button.dataset.montant || 0);
                const montantRejete = parseFloat(button.dataset.montrejete || 0);
                const dateTransmission = button.dataset.datetransmission;

                if (!document.getElementById('traitementModal')) {
                    console.error('Modal traitementModal introuvable');
                    return;
                }

                const form = document.getElementById('traitementForm');
                form.action = `/ligne_suivi/${ref}/traiter`;

                document.getElementById('ref').value = ref || '';

                function isEmpty(value) {
                    return !value || value === 'null' || value === 'undefined' || value.toString().trim() === '';
                }

                const factField = document.getElementById('fact');
                if (!isEmpty(souscripteur)) {
                    factField.value = souscripteur;
                } else if (!isEmpty(facture)) {
                    factField.value = facture;
                } else {
                    factField.value = '';
                }

                const assuresField = document.getElementById('assures');
                if (!isEmpty(assures)) {
                    assuresField.value = assures;
                } else if (!isEmpty(prestataire)) {
                    assuresField.value = prestataire;
                } else {
                    assuresField.value = '';
                }

                if (!isEmpty(dateEnreg)) {
                    const date = new Date(dateEnreg);
                    if (!isNaN(date.getTime())) {
                        document.getElementById('dateEnreg').value = date.toLocaleDateString('fr-FR');
                    }
                }

                document.getElementById('montantF').value = montant ? new Intl.NumberFormat('fr-FR').format(montant) : '';
                document.getElementById('montrejete').value = montantRejete ? new Intl.NumberFormat('fr-FR').format(montantRejete) : '0';

                if (!isEmpty(dateTransmission)) {
                    const date = new Date(dateTransmission);
                    if (!isNaN(date.getTime())) {
                        document.getElementById('dateTransmission').value = date.toLocaleDateString('fr-FR');
                    }
                }

                const montantMax = montant - montantRejete;
                const montantRegle = document.getElementById('montantRegle');
                if (montantRegle) {
                    montantRegle.max = montantMax;
                    montantRegle.value = "";
                }

                document.getElementById('MontantFacture').value = montant;
                document.getElementById('dateDemande').max = new Date().toISOString().split('T')[0];
                document.getElementById('traitementModal').classList.remove('hidden');
            }

            function closeTraitementModal() {
                document.getElementById('traitementModal').classList.add('hidden');
            }

            function openRejetModal(button) {
                const modal = document.getElementById('rejetModal');
                if (!modal) {
                    console.error('Modal rejetModal non trouvé');
                    return;
                }

                const form = document.getElementById('rejetForm');
                form.action = `/ligne_suivi/${button.dataset.id}/rejeter`;

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

                document.getElementById('rejet_id').value = data.id;
                document.getElementById('rejet_mont').value = data.mont;
                document.getElementById('rejet_montrejet').value = data.mont;
                document.getElementById('rejet_numrecept').value = data.numrecept;
                document.getElementById('rejet_agent').value = data.agent;
                document.getElementById('rejet_assure').value = data.assure;
                document.getElementById('rejet_souscripteur').value = data.souscripteur;
                document.getElementById('rejet_ref_facture').value = data.ref;
                document.getElementById('rejet_numreception_affiche').value = data.numrecept;
                document.getElementById('rejet_montant_total').value = Number(data.mont).toLocaleString() + " FCFA";
                document.getElementById('rejet_montant_rejete').value = Number(data.mont).toLocaleString() + " FCFA";
                document.getElementById('rejet_date_enreg').value = data.dateenreg;
                document.getElementById('rejet_agent_nom').value = data.agent;

                modal.style.display = 'flex';
            }

            function closeRejetModal() {
                const modal = document.getElementById('rejetModal');
                if (modal) {
                    modal.style.display = 'none';
                }
            }

            // Rendre les fonctions globales
            window.openTraitementModal = openTraitementModal;
            window.closeTraitementModal = closeTraitementModal;
            window.openRejetModal = openRejetModal;
            window.closeRejetModal = closeRejetModal;

            // ============================================
            // PARTIE 2 : COMPOSANTS ALPINE.JS
            // ============================================

            // Composant pour la gestion des factures
            document.addEventListener('alpine:init', () => {
                Alpine.data('factureSelection', () => ({
                    openAddIndividuelle: false,
                    openAddTiersPayant: false,
                    openEdit: false,
                    openValiderModal: false,
                    selectedFactures: [],
                    editingFacture: null,

                    openAddIndividuelleModal() {
                        this.openAddIndividuelle = true;
                        this.$nextTick(() => {
                            this.initSelect2Individuelle();
                        });
                    },

                    openAddTiersPayantModal() {
                        this.openAddTiersPayant = true;
                        this.$nextTick(() => {
                            this.initSelect2TiersPayant();
                        });
                    },

                    initSelect2Individuelle() {
                        $('#idSouscripteur').select2({
                            placeholder: "Sélectionner un souscripteur",
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#openAddIndividuelle')
                        });
                        $('#mois').select2({
                            placeholder: "Sélectionner un mois",
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#openAddIndividuelle')
                        });
                        $('#annee').select2({
                            placeholder: "Sélectionner une année",
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#openAddIndividuelle')
                        });
                    },

                    initSelect2TiersPayant() {
                        $('#prestataire-select').select2({
                            placeholder: "Sélectionner un prestataire",
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#openAddTiersPayant')
                        });
                        $('#mois-facture-select').select2({
                            placeholder: "Sélectionner un mois",
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#openAddTiersPayant')
                        });
                        $('#annee-facture-select').select2({
                            placeholder: "Sélectionner une année",
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#openAddTiersPayant')
                        });
                    },

                    openEditModal(id) {
                        fetch(`/factures/${id}`)
                            .then(r => r.json())
                            .then(data => {
                                this.editingFacture = data.facture;
                                this.openEdit = true;
                            })
                            .catch(() => alert('Erreur chargement facture'));
                    },

                    // toggleSelection(event) {
                    //     const id = parseInt(event.target.value);
                    //     if (event.target.checked) {
                    //         if (!this.selectedFactures.includes(id)) {
                    //             this.selectedFactures.push(id);
                    //         }
                    //     } else {
                    //         this.selectedFactures = this.selectedFactures.filter(i => i !== id);
                    //     }
                    // },

                        toggleFacture(id) {
                            id = Number(id);

                            if (this.selectedFactures.includes(id)) {
                                this.selectedFactures = this.selectedFactures.filter(i => i !== id);
                            } else {
                                this.selectedFactures.push(id);
                            }

                            sessionStorage.setItem(
                                'selectedFactures',
                                JSON.stringify(this.selectedFactures)
                            );
                        },

                        isSelected(id) {
                            return this.selectedFactures.includes(Number(id));
                        },

                            submitValidation() {
                                if (this.selectedFactures.length === 0) {
                                    alert('Veuillez sélectionner au moins une facture.');
                                    return;
                                }

                                // Remplir le champ hidden
                                this.$refs.factures.value = this.selectedFactures.join(',');

                                // Effacer les sélections après avoir préparé la soumission
                                this.selectedFactures = [];  // Vider le tableau
                                sessionStorage.removeItem('selectedFactures');  // Supprimer de sessionStorage

                                // Fermer le modal **avant** le téléchargement
                                this.openValiderModal = false;

                                // Soumettre le formulaire
                                this.$refs.validerForm.submit();

                                // 🔄 Recharger la page après un court délai pour permettre le téléchargement
                                setTimeout(() => {
                                    window.location.reload();
                                }, 7000);  // Ajustez le délai si nécessaire (1.5 secondes ici)
                            },

                    // submitValidation() {
                    //     if (this.selectedFactures.length === 0) {
                    //         alert('Veuillez sélectionner au moins une facture.');
                    //         return;
                    //     }

                    //     // Remplir le champ hidden
                    //     this.$refs.factures.value = this.selectedFactures.join(',');

                    //     // Soumettre le form via le ref
                    //     this.$refs.validerForm.submit();
                    // },


                    init() {
                        // 🔁 Restaurer depuis sessionStorage
                        const saved = sessionStorage.getItem('selectedFactures');
                        if (saved) {
                            this.selectedFactures = JSON.parse(saved);
                        }

                       if (window.__OPEN_MODAL__ === 'individuelle') {
                            this.$nextTick(() => {
                                this.openAddIndividuelleModal();
                            });
                        }

                        if (window.__OPEN_MODAL__ === 'tiersPayant') {
                            this.$nextTick(() => {
                                this.openAddTiersPayantModal();
                            });
                        }

                    },
                }));

                // Composant pour le modal de règlement
                Alpine.data('reglerModal', () => ({
                    isOpen: false,
                    formData: {},
                    fields: [
                        { label: 'N° Facture / Souscripteur', key: 'fact' },
                        { label: 'Assuré / Prestataire', key: 'assure' },
                        { label: 'N° Demande', key: 'numdem' },
                        { label: 'Date Enregistrement', key: 'dateenreg' },
                        { label: 'Date Demande', key: 'datedemande' },
                        { label: 'Date Transmission', key: 'datetransmission' },
                        { label: 'Montant Facture', key: 'montantfacture' },
                        { label: 'Montant Règlement', key: 'montantreglement' },
                        { label: 'Montant Rejeté', key: 'montantrejete' },
                        { label: 'Date Saisie', key: 'datesaisie' },
                        { label: 'Chèque enregistré par', key: 'user' }
                    ],

                   // Dans Alpine.data('reglerModal', ...)
                    open(dataset) {
                        // LOG DE DEBUG : Affichez dataset pour voir ce qui arrive RÉELLEMENT du bouton
                        console.log("Contenu brut du bouton (dataset) :", dataset);

                        this.formData = {
                            id: dataset.id,
                            fact: dataset.fact,
                            assure: dataset.assure,
                            numdem: dataset.numdem,
                            // On essaye de récupérer datedemande, sinon on cherche dateDemande (au cas où)
                            datedemande: dataset.datedemande || dataset.dateDemande || '',
                            dateenreg: dataset.dateenreg,
                            datetransmission: dataset.datetransmission,
                            montantfacture: dataset.montantfacture,
                            montantreglement: dataset.montantreglement,
                            montantrejete: dataset.montantrejete,
                            datesaisie: dataset.datesaisie,
                            user: dataset.user,
                            ncheque: dataset.ncheque || ''
                        };
                        this.isOpen = true;
                        document.body.style.overflow = 'hidden';
                    },

                    close() {
                        this.isOpen = false;
                        document.body.style.overflow = '';
                        this.formData = {};
                    },

                   submitForm(event) {
                        // 1. Vérification manuelle
                        if (!this.formData.ncheque || !this.formData.ncheque.trim()) {
                            alert('Veuillez saisir un numéro de chèque');
                            return; // On s'arrête ici
                        }

                        // 2. Si c'est OK, on soumet réellement le formulaire à Laravel
                        // event.target est l'élément <form>
                        event.target.submit();
                    }
                }));
            });

            // Fonction globale pour ouvrir le modal de règlement
            function openReglerModal(button) {
                const modalElement = document.querySelector('[x-data*="reglerModal"]');
                if (!modalElement) {
                    console.error('Modal element not found');
                    return;
                }

                // Attendre qu'Alpine soit initialisé
                if (typeof Alpine !== 'undefined') {
                    const modalData = Alpine.$data(modalElement);
                    if (modalData && typeof modalData.open === 'function') {
                        modalData.open(button.dataset);
                    }
                }
            }

            window.openReglerModal = openReglerModal;

            // ============================================
            // PARTIE 3 : JQUERY INITIALIZATION
            // ============================================

            $(document).ready(function () {
                console.log('Document ready - Initialisation');

                // DataTable
                try {
                    $('#dtHorizontalVerticalExample').DataTable({
                        scrollX: true,
                        scrollY: '500px',
                        order: [[5, 'desc']],
                        dom: 'frtip',
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

                // Modal jQuery pour édition
                function openModal() {
                    document.getElementById('transM').classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                }

                function closeModal() {
                    document.getElementById('transM').classList.add('hidden');
                     if ($('.select2-edit').length > 0 && $('.select2-edit').hasClass('select2-hidden-accessible')) {
                        $('.select2-edit').select2('destroy');
                        console.log('Select2 détruit à la fermeture');
                    }
                    document.body.classList.remove('overflow-hidden');
                    $('#trans_ici').empty();
                }

                window.closeModal = closeModal;

                $(document).on('click', '#transM', function(e) {
                    if (e.target === this) {
                        closeModal();
                    }
                });

                $(document).on('keydown', function(e) {
                    if (e.key === 'Escape' && !document.getElementById('transM').classList.contains('hidden')) {
                        closeModal();
                    }
                });

                // Bouton modifier
                $(document).on('click', '.trans', function (e) {
                    e.preventDefault();
                    console.log('Clic sur bouton modifier');

                    const button = $(this);
                    const id = button.data('id');
                    const mont = button.data('mont');
                    const prest = button.data('prest');
                    const deb = button.data('deb');
                    const fin = button.data('fin');
                    const statut = button.data('statut');
                    const rejetee = button.data('rejetee');

                    if (!id) {
                        alert('ID de facture manquant');
                        return;
                    }

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

                    openModal();

                    $.ajax({
                        url: "{{ route('ligne_suivi.editModal') }}",
                        method: 'GET',
                        data: { id, mont, prest, deb, fin },
                        success: function (html) {
                            console.log('Contenu modal reçu');

                            // On injecte le HTML
                            const container = $('#trans_ici');
                            container.html(html);

                            // On attend que l'insertion soit effective dans le DOM avant d'initialiser
                            container.promise().done(function() {
                                console.log('DOM mis à jour, tentative d\'initialisation...');

                                if (typeof window.initializeEditModal === 'function') {
                                    window.initializeEditModal({ statut, rejetee });
                                }
                            });
                        },
                        error: function (xhr) {
                            console.error('Erreur AJAX:', xhr);
                            let errorMessage = 'Erreur lors du chargement';
                            if (xhr.status === 404) errorMessage = 'Route non trouvée (404)';
                            else if (xhr.status === 500) errorMessage = 'Erreur serveur (500)';
                            alert(errorMessage);
                            closeModal();
                        }
                    });
                });

                // Event listeners pour les boutons de rejet
                const buttons = document.querySelectorAll('.btn-rejet');
                buttons.forEach(button => {
                    button.addEventListener('click', function() {
                        openRejetModal(this);
                    });
                });

                document.addEventListener('click', function(e) {
                    const modal = document.getElementById('rejetModal');
                    if (e.target === modal) {
                        closeRejetModal();
                    }
                });
            });

                   window.initializeEditModal = function(buttonData) {
                        console.log('Initializing edit modal...');

                        // Attendre que le DOM soit prêt (timeout simple)
                        setTimeout(function() {
                            const $select = $('.select2-edit');
                            if ($select.length > 0) {
                                // Détruire si déjà initialisé
                                if ($select.hasClass('select2-hidden-accessible')) {
                                    $select.select2('destroy');
                                }

                                // Réinitialiser
                                $select.select2({
                                    dropdownParent: $('#transM'),
                                    width: '100%',
                                    placeholder: "Sélectionner une option",
                                    allowClear: true
                                });

                                console.log('Select2 réinitialisé');

                                // Appliquer restrictions
                                if (buttonData && typeof applyFieldRestrictions === 'function') {
                                    applyFieldRestrictions(buttonData.statut, buttonData.rejetee);
                                }
                            } else {
                                console.error('Élément .select2-edit introuvable');
                            }
                        }, 500); // Attendre 500ms pour que le DOM soit chargé
                    };

            </script>

            <style>
                    [x-cloak] { display: none !important; }
                    .btn {
                        padding: 8px 16px;
                        border-radius: 4px;
                        border: none;
                        cursor: pointer;
                        margin: 10px;
                    }
                    .btn-info {
                        background-color: #17a2b8;
                        color: white;
                    }
                    .btn-info:hover {
                        background-color: #138496;
                    }

                    /* Force l'affichage du modal quand il est ouvert */
                    .modal-overlay.show {
                        display: flex !important;
                    }


                    /* Styles pour Select2 dans les modals */
            .select2-container--default .select2-selection--single {
                height: 38px;
                border: 1px solid #d1d5db;
                border-radius: 0.375rem;
            }
            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 36px;
                padding-left: 12px;
                color: #374151;
            }
            .select2-container--default .select2-selection--single .select2-selection__placeholder {
                color: #9ca3af;
            }
            .select2-dropdown {
                border: 1px solid #d1d5db;
                border-radius: 0.375rem;
                z-index: 9999; /* Assure un z-index élevé */
            }
</style>
