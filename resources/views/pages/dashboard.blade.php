@extends('../layout/' . $layout)

@section('subcontent')




@if(Auth::check())
    {{-- Calcul de l'âge du mot de passe (UNIQUEMENT PAR DATES) --}}
    @php
        $passwordAge = Auth::user()->password_changed_at
            ? \Carbon\Carbon::parse(Auth::user()->password_changed_at)->startOfDay()->diffInDays(now()->startOfDay())
            : 0;
        $daysLeft = 30 - $passwordAge;
    @endphp

    {{-- DEBUG --}}
    <!-- <div class="bg-gray-100 border-2 border-gray-400 p-4 mb-4 rounded font-mono text-sm">
        <h3 class="font-bold mb-2 text-lg">🔍 DEBUG - État du mot de passe</h3>
        <div class="grid grid-cols-2 gap-2">
            <div><strong>User ID:</strong> {{ Auth::user()->id }}</div>
            <div><strong>Login:</strong> {{ Auth::user()->login }}</div>
            <div><strong>password_changed_at:</strong> {{ Auth::user()->password_changed_at ?? 'NULL' }}</div>
            <div><strong>password_expired:</strong> {{ Auth::user()->password_expired ? 'OUI ❌' : 'NON ✅' }}</div>
            <div><strong>must_change_password:</strong> {{ Auth::user()->must_change_password ? 'OUI ❌' : 'NON ✅' }}</div>
            <div><strong>Âge:</strong> {{ $passwordAge }} jour(s) complet(s)</div>
            <div><strong>Jours restants:</strong> {{ $daysLeft }} jour(s)</div>
            <div class="col-span-2">
                <strong>Statut:</strong>
                @if($passwordAge >= 30 || Auth::user()->password_expired)
                    <span class="text-red-600 font-bold">🔴 EXPIRÉ</span>
                @elseif($passwordAge >= 25)
                    <span class="text-yellow-600 font-bold">🟡 VA EXPIRER</span>
                @else
                    <span class="text-green-600 font-bold">🟢 OK</span>
                @endif
            </div>
            <div class="col-span-2 mt-2 p-2 bg-blue-50 rounded">
                <strong>Conditions d'alerte :</strong>
                <ul class="list-disc pl-5 text-xs">
                    <li>passwordAge >= 30 : {{ $passwordAge >= 30 ? 'VRAI ✅' : 'FAUX ❌' }}</li>
                    <li>passwordAge >= 25 && < 30 : {{ ($passwordAge >= 25 && $passwordAge < 30) ? 'VRAI ✅' : 'FAUX ❌' }}</li>
                    <li>password_expired = true : {{ Auth::user()->password_expired ? 'VRAI ✅' : 'FAUX ❌' }}</li>
                </ul>
            </div>
        </div>
    </div> -->

    {{-- NOTIFICATION EXPIRÉ (30 jours ou plus) --}}
    @if($passwordAge >= 30 || Auth::user()->password_expired)
        <div id="password-expired-toast" class="fixed bottom-4 right-4 z-50 max-w-md bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-2xl">
            <div class="flex items-start justify-between">
                <div class="flex items-start">
                    <svg class="w-8 h-8 mr-3 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="flex-1">
                        <p class="font-bold text-lg">🔒 Mot de passe expiré !</p>
                        <p class="text-sm mt-1">
                            Expiré depuis : <strong>{{ max(0, $passwordAge - 30) }} jour(s)</strong>
                        </p>
                        <p class="text-xs mt-2 bg-red-200 p-2 rounded">
                            ⚠️ Contactez l'administrateur pour réinitialiser.
                        </p>
                    </div>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-red-500 hover:text-red-700">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>

    {{-- NOTIFICATION VA EXPIRER (entre 25 et 29 jours) --}}
    @elseif($passwordAge >= 25 && $passwordAge < 30)
        <div id="password-warning-toast" class="fixed bottom-4 right-4 z-50 max-w-md bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 rounded-lg shadow-2xl">
            <div class="flex items-start justify-between">
                <div class="flex items-start">
                    <svg class="w-8 h-8 mr-3 text-yellow-600 flex-shrink-0 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="flex-1">
                        <p class="font-bold text-lg">⚠️ Mot de passe expirant bientôt !</p>
                        <p class="text-sm mt-1">
                            Expire dans : <strong>{{ $daysLeft }} jour(s)</strong><br>
                            Âge actuel : <strong>{{ $passwordAge }} jours</strong> sur 30 autorisés
                        </p>
                        <a href="{{ route('password.change.form') }}"
                           class="inline-block mt-3 bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded-lg shadow transition">
                            🔑 Changer maintenant
                        </a>
                    </div>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-yellow-500 hover:text-yellow-700">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif
@endif

<div class="container mx-auto px-4 py-8 max-w-7xl">
    <!-- En-tête du Dashboard -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Tableau de bord</h1>
        </div>
        <div class="mt-4 md:mt-0 flex items-center bg-white p-1.5 rounded-lg shadow-sm border border-slate-200">
            <label class="text-slate-500 text-sm font-medium px-3">Exercice :</label>
            <select id="exercice" class="bg-slate-50 border-none text-slate-700 text-sm font-semibold rounded focus:ring-2 focus:ring-blue-500 py-1.5 pl-3 pr-8 cursor-pointer outline-none">
                @foreach($annees as $an)
                    <option value="{{ $an }}" {{ $an == $annee ? 'selected' : '' }}>{{ $an }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <!-- Stats Globales (Non Traités, Demandés, Réglés) -->
    <div id="stats-container" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Carte: Non Traités -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 relative overflow-hidden group hover:shadow-md transition-all">
            <div class="flex justify-between items-start z-10 relative">
                <div>
                    <div class="text-slate-500 text-sm font-medium uppercase tracking-wide mb-1">Non Traités</div>
                    <div class="text-2xl font-bold text-slate-800 mt-2">{{ number_format($data['stats']['nonTraites']['montant'], 0, ',', ' ') }} <span class="text-sm font-normal text-slate-400">FCFA</span></div>
                    <div class="text-xs font-medium text-red-500 mt-1 bg-red-50 inline-block px-2 py-0.5 rounded-full">
                        {{ $data['stats']['nonTraites']['nombre'] }} factures en attente
                    </div>
                </div>
                <div class="p-3 bg-red-50 rounded-lg text-red-500">
                    <i class="fas fa-exclamation-circle fa-lg"></i>
                </div>
            </div>
        </div>
        <!-- Carte: Demandés -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 relative overflow-hidden group hover:shadow-md transition-all">
            <div class="flex justify-between items-start z-10 relative">
                <div>
                    <div class="text-slate-500 text-sm font-medium uppercase tracking-wide mb-1">Montant Demandé</div>
                    <div class="text-2xl font-bold text-slate-800 mt-2">{{ number_format($data['stats']['demandes']['montant'], 0, ',', ' ') }} <span class="text-sm font-normal text-slate-400">FCFA</span></div>
                </div>
                <div class="p-3 bg-yellow-50 rounded-lg text-yellow-600">
                    <i class="fas fa-file-invoice-dollar fa-lg"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex justify-between text-xs text-slate-500 mb-1">
                    <span>Progression ({{ $data['stats']['demandes']['taux'] }}%)</span>
                    <span>Sur {{ number_format($data['stats']['demandes']['totalFacture'], 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1.5">
                    <div class="bg-yellow-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ $data['stats']['demandes']['taux'] }}%"></div>
                </div>
            </div>
        </div>
        <!-- Carte: Réglés -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 relative overflow-hidden group hover:shadow-md transition-all">
            <div class="flex justify-between items-start z-10 relative">
                <div>
                    <div class="text-slate-500 text-sm font-medium uppercase tracking-wide mb-1">Montant Réglé</div>
                    <div class="text-2xl font-bold text-slate-800 mt-2">{{ number_format($data['stats']['regles']['montant'], 0, ',', ' ') }} <span class="text-sm font-normal text-slate-400">FCFA</span></div>
                </div>
                <div class="p-3 bg-green-50 rounded-lg text-green-600">
                    <i class="fas fa-check-circle fa-lg"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex justify-between text-xs text-slate-500 mb-1">
                    <span>Taux de recouvrement ({{ $data['stats']['regles']['taux'] }}%)</span>
                    <span>Sur {{ number_format($data['stats']['regles']['montantDemande'], 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1.5">
                    <div class="bg-green-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ $data['stats']['regles']['taux'] }}%"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- NOUVELLE SECTION : TOTAUX PAR RÉSEAU -->
    <div class="mb-8">
        <h2 class="text-lg font-bold text-slate-700 mb-4 flex items-center">
            <i class="fas fa-network-wired mr-2 text-blue-500"></i> TOTAUX PAR RÉSEAU
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

            <!-- Pharmacies -->
            <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm flex items-center justify-between hover:border-blue-300 transition-colors">
                <div>
                    <div class="text-xs font-semibold text-slate-500 uppercase">Pharmacies</div>
                    <div class="text-xl font-bold text-slate-800 mt-1">
                        <span id="total-pharmacie">{{ number_format(array_sum($data['pointMensuel']['Pharmacie'] ?? []), 0, ',', ' ') }}</span> <span class="text-xs text-slate-400 font-normal">FCFA</span>
                    </div>
                </div>
                <div class="h-10 w-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                    <i class="fas fa-pills"></i>
                </div>
            </div>
            <!-- Parapharmacies -->
            <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm flex items-center justify-between hover:border-teal-300 transition-colors">
                <div>
                    <div class="text-xs font-semibold text-slate-500 uppercase">Parapharmacies</div>
                    <div class="text-xl font-bold text-slate-800 mt-1">
                        <span id="total-parapharmacie">{{ number_format(array_sum($data['pointMensuel']['Parapharmacie'] ?? []), 0, ',', ' ') }}</span> <span class="text-xs text-slate-400 font-normal">FCFA</span>
                    </div>
                </div>
                <div class="h-10 w-10 rounded-full bg-teal-50 flex items-center justify-center text-teal-600">
                    <i class="fas fa-mortar-pestle"></i>
                </div>
            </div>
            <!-- Individuels -->
            <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm flex items-center justify-between hover:border-indigo-300 transition-colors">
                <div>
                    <div class="text-xs font-semibold text-slate-500 uppercase">Individuels</div>
                    <div class="text-xl font-bold text-slate-800 mt-1">
                        <span id="total-individuels">{{ number_format(array_sum($data['pointMensuel']['Individuels'] ?? []), 0, ',', ' ') }}</span> <span class="text-xs text-slate-400 font-normal">FCFA</span>
                    </div>
                </div>
                <div class="h-10 w-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="fas fa-user"></i>
                </div>
            </div>
            <!-- Evacuations -->
            <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm flex items-center justify-between hover:border-orange-300 transition-colors">
                <div>
                    <div class="text-xs font-semibold text-slate-500 uppercase">Evacuations</div>
                    <div class="text-xl font-bold text-slate-800 mt-1">
                        <span id="total-evacuations">{{ number_format(array_sum($data['pointMensuel']['Evacuation'] ?? []), 0, ',', ' ') }}</span> <span class="text-xs text-slate-400 font-normal">FCFA</span>
                    </div>
                </div>
                <div class="h-10 w-10 rounded-full bg-orange-50 flex items-center justify-center text-orange-600">
                    <i class="fas fa-plane-departure"></i>
                </div>
            </div>
            <!-- Appels de Fonds -->
            <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm flex items-center justify-between hover:border-purple-300 transition-colors">
                <div>
                    <div class="text-xs font-semibold text-slate-500 uppercase">Appels de Fonds</div>
                    <div class="text-xl font-bold text-slate-800 mt-1">
                        <span id="total-appels">{{ number_format(array_sum($data['pointMensuel']['Appels de fonds'] ?? []), 0, ',', ' ') }}</span> <span class="text-xs text-slate-400 font-normal">FCFA</span>
                    </div>
                </div>
                <div class="h-10 w-10 rounded-full bg-purple-50 flex items-center justify-center text-purple-600">
                    <i class="fas fa-briefcase"></i>
                </div>
            </div>
            <!-- Rejets -->
            <div class="bg-white p-5 rounded-lg border border-red-200 shadow-sm flex items-center justify-between hover:bg-red-50/30 transition-colors">
                <div>
                    <div class="text-xs font-semibold text-red-500 uppercase">Rejets</div>
                    <div class="text-xl font-bold text-slate-800 mt-1">
                        <span id="total-rejets">{{ number_format($data['stats']['rejets'] ?? 0, 0, ',', ' ') }}</span> <span class="text-xs text-slate-400 font-normal">FCFA</span>
                    </div>
                </div>
                <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center text-red-600">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>
    </div>
    <!-- Tableau FACTURES SANTE (POINT MENSUEL) -->
    <div class="bg-white shadow-sm rounded-xl border border-slate-200 mb-8 overflow-hidden">
        <div class="bg-white py-4 px-6 border-b border-slate-200 flex items-center justify-between">
            <h6 class="text-slate-800 font-bold text-lg flex items-center gap-2">
                <span class="w-1 h-6 bg-blue-600 rounded-full block"></span>
                FACTURES SANTE (POINT MENSUEL)
            </h6>
        </div>
        <div class="overflow-x-auto">
            <table id="pointMensuelTable" class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Catégorie</th>
                        @for($i = 1; $i <= 12; $i++)
                            <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">{{ \Carbon\Carbon::create()->month($i)->format('M') }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody id="pointMensuelBody" class="divide-y divide-slate-100">
                    @foreach($data['pointMensuel'] as $categorie => $moisData)
                        <tr class="hover:bg-blue-50/50 transition-colors group">
                            <td class="px-6 py-4 font-semibold text-slate-700 text-sm whitespace-nowrap group-hover:text-blue-700">{{ $categorie }}</td>
                            @for($i = 1; $i <= 12; $i++)
                                <td class="px-4 py-4 text-sm text-slate-600 text-right font-medium">{{ number_format($moisData[$i], 0, ',', ' ') }}</td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <!-- Tableau REPARTITION MENSUELLE -->
    <div class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
        <div class="bg-white py-4 px-6 border-b border-slate-200 flex items-center justify-between">
            <h6 class="text-slate-800 font-bold text-lg flex items-center gap-2">
                <span class="w-1 h-6 bg-purple-600 rounded-full block"></span>
                REPARTITION MENSUELLE
            </h6>
        </div>
        <div class="overflow-x-auto">
            <table id="repartitionTable" class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Catégorie</th>
                        @for($i = 1; $i <= 12; $i++)
                            <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">{{ \Carbon\Carbon::create()->month($i)->format('M') }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody id="repartitionBody" class="divide-y divide-slate-100">
                    @foreach($data['repartitionMensuelle'] as $categorie => $moisData)
                        <tr class="hover:bg-purple-50/50 transition-colors group">
                            <td class="px-6 py-4 font-semibold text-slate-700 text-sm whitespace-nowrap group-hover:text-purple-700">{{ $categorie }}</td>
                            @for($i = 1; $i <= 12; $i++)
                                <td class="px-4 py-4 text-sm text-slate-600 text-right font-medium">{{ $moisData[$i] }}</td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Modal pour les détails des factures -->
<div class="modal fade" id="showdetail" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content rounded-xl border-none shadow-xl">
            <div class="modal-header border-b border-slate-100 p-5 bg-slate-50 rounded-t-xl">
                <h5 class="modal-title font-bold text-slate-800">Détail des factures</h5>
                <button class="close opacity-50 hover:opacity-100 transition-opacity" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="dataTable" width="100%" cellspacing="0">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="border-0 py-3 px-4 font-semibold text-xs uppercase">Libelle Prestataire</th>
                                <th class="border-0 py-3 px-4 font-semibold text-xs uppercase">Numéro Réception</th>
                                <th class="border-0 py-3 px-4 font-semibold text-xs uppercase">Réf. Facture</th>
                                <th class="border-0 py-3 px-4 font-semibold text-xs uppercase text-right">Montant</th>
                            </tr>
                        </thead>
                        <tbody id='det_row' class="text-sm text-slate-600"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#exercice').change(function() {
        const annee = $(this).val();
        $.ajax({
            url: '{{ route("dashboard.data") }}',
            method: 'GET',
            data: { annee: annee },
            success: function(response) {
                function formatNumber(num) {
                    return Math.round(num).toLocaleString('fr-FR');
                }
                // Fonction utilitaire pour calculer le total d'une catégorie (somme des 12 mois)
                function calculateTotalFromMonths(monthData) {
                    if (!monthData) return 0;
                    // On filtre pour ne garder que les valeurs numériques et on somme
                    return Object.values(monthData).reduce((a, b) => a + (parseFloat(b) || 0), 0);
                }
                // 1. MISE A JOUR DES STATS GLOBALES
                $('#stats-container').html(`
                    <!-- Carte: Non Traités -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 relative overflow-hidden group hover:shadow-md transition-all">
                        <div class="flex justify-between items-start z-10 relative">
                            <div>
                                <div class="text-slate-500 text-sm font-medium uppercase tracking-wide mb-1">Non Traités</div>
                                <div class="text-2xl font-bold text-slate-800 mt-2">${formatNumber(response.stats.nonTraites.montant)} <span class="text-sm font-normal text-slate-400">FCFA</span></div>
                                <div class="text-xs font-medium text-red-500 mt-1 bg-red-50 inline-block px-2 py-0.5 rounded-full">
                                    ${response.stats.nonTraites.nombre} factures en attente
                                </div>
                            </div>
                            <div class="p-3 bg-red-50 rounded-lg text-red-500">
                                <i class="fas fa-exclamation-circle fa-lg"></i>
                            </div>
                        </div>
                    </div>
                    <!-- Carte: Demandés -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 relative overflow-hidden group hover:shadow-md transition-all">
                        <div class="flex justify-between items-start z-10 relative">
                            <div>
                                <div class="text-slate-500 text-sm font-medium uppercase tracking-wide mb-1">Montant Demandé</div>
                                <div class="text-2xl font-bold text-slate-800 mt-2">${formatNumber(response.stats.demandes.montant)} <span class="text-sm font-normal text-slate-400">FCFA</span></div>
                            </div>
                            <div class="p-3 bg-yellow-50 rounded-lg text-yellow-600">
                                <i class="fas fa-file-invoice-dollar fa-lg"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex justify-between text-xs text-slate-500 mb-1">
                                <span>Progression (${response.stats.demandes.taux}%)</span>
                                <span>Sur ${formatNumber(response.stats.demandes.totalFacture)} FCFA</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5">
                                <div class="bg-yellow-500 h-1.5 rounded-full transition-all duration-500" style="width: ${response.stats.demandes.taux}%"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Carte: Réglés -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 relative overflow-hidden group hover:shadow-md transition-all">
                        <div class="flex justify-between items-start z-10 relative">
                            <div>
                                <div class="text-slate-500 text-sm font-medium uppercase tracking-wide mb-1">Montant Réglé</div>
                                <div class="text-2xl font-bold text-slate-800 mt-2">${formatNumber(response.stats.regles.montant)} <span class="text-sm font-normal text-slate-400">FCFA</span></div>
                            </div>
                            <div class="p-3 bg-green-50 rounded-lg text-green-600">
                                <i class="fas fa-check-circle fa-lg"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex justify-between text-xs text-slate-500 mb-1">
                                <span>Taux de recouvrement (${response.stats.regles.taux}%)</span>
                                <span>Sur ${formatNumber(response.stats.regles.montantDemande)} FCFA</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5">
                                <div class="bg-green-500 h-1.5 rounded-full transition-all duration-500" style="width: ${response.stats.regles.taux}%"></div>
                            </div>
                        </div>
                    </div>
                `);
                // 2. MISE A JOUR DES TOTAUX PAR RÉSEAU (AJAX)
                // On calcule le total en faisant la somme des mois reçus pour chaque catégorie
                $('#total-pharmacie').text(formatNumber(calculateTotalFromMonths(response.pointMensuel['Pharmacie'])));
                $('#total-parapharmacie').text(formatNumber(calculateTotalFromMonths(response.pointMensuel['Parapharmacie'])));
                $('#total-individuels').text(formatNumber(calculateTotalFromMonths(response.pointMensuel['Individuels'])));
                $('#total-evacuations').text(formatNumber(calculateTotalFromMonths(response.pointMensuel['Evacuation'])));
                $('#total-appels').text(formatNumber(calculateTotalFromMonths(response.pointMensuel['Appels de fonds'])));

                // Pour les rejets, on suppose que c'est une stat directe
                $('#total-rejets').text(formatNumber(response.stats.rejets || 0));
                // 3. MISE A JOUR TABLEAU POINT MENSUEL
                let pointMensuelHtml = '';
                for (const [categorie, moisData] of Object.entries(response.pointMensuel)) {
                    pointMensuelHtml += '<tr class="hover:bg-blue-50/50 transition-colors group"><td class="px-6 py-4 font-semibold text-slate-700 text-sm whitespace-nowrap group-hover:text-blue-700">' + categorie + '</td>';
                    for (let i = 1; i <= 12; i++) {
                        pointMensuelHtml += '<td class="px-4 py-4 text-sm text-slate-600 text-right font-medium">' + formatNumber(moisData[i] || 0) + '</td>';
                    }
                    pointMensuelHtml += '</tr>';
                }
                $('#pointMensuelBody').html(pointMensuelHtml);
                // 4. MISE A JOUR TABLEAU REPARTITION
                let repartitionHtml = '';
                for (const [categorie, moisData] of Object.entries(response.repartitionMensuelle)) {
                    repartitionHtml += '<tr class="hover:bg-purple-50/50 transition-colors group"><td class="px-6 py-4 font-semibold text-slate-700 text-sm whitespace-nowrap group-hover:text-purple-700">' + categorie + '</td>';
                    for (let i = 1; i <= 12; i++) {
                        repartitionHtml += '<td class="px-4 py-4 text-sm text-slate-600 text-right font-medium">' + (moisData[i] || 0) + '</td>';
                    }
                    repartitionHtml += '</tr>';
                }
                $('#repartitionBody').html(repartitionHtml);
            }
        });
    });
    // Gestion des clics pour les détails
    $(document).on('click', '.det', function() {
        var annee = $(this).attr('data-an');
        var mois = $(this).attr('data-mois');
        var categorie = $(this).attr('data-categ');
    });
});
</script>
<style>
.overflow-x-auto::-webkit-scrollbar {
    height: 8px;
}
.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}
.overflow-x-auto::-webkit-scrollbar-thumb {

background: #cbd5e1;
border-radius: 4px;
}
.overflow-x-auto::-webkit-scrollbar-thumb:hover {
background: #94a3b8;
}
</style>
<!--
function closeToast(toastId) {
    const toast = document.getElementById(toastId);
    if (toast) {
        toast.classList.add('animate-slide-out');
        setTimeout(() => {
            toast.style.display = 'none';
        }, 300);
    }
} -->





@endsection

