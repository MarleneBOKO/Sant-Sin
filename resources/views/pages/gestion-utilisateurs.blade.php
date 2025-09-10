@extends('../layout/' . $layout)

@section('subhead')
    <title>Gestion Utilisateurs</title>

    <!-- Alpine.js chargé dans le head pour éviter le flash -->
    <script src="//unpkg.com/alpinejs" defer></script>

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
<!-- Container Alpine.js avec protection anti-flash renforcée -->
<div x-data="{
        open: false,
        editOpen: false,
        editingUser: null,
        loading: false,
        init() {
            // Attendre que Alpine soit complètement initialisé
            this.$nextTick(() => {
                @if ($errors->any())
                    this.open = true;
                @endif
            });
        },
        async openEditModal(userId) {
            this.loading = true;
            try {
                const response = await fetch(`/users/${userId}`);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('La réponse n\'est pas du JSON valide');
                }

                const data = await response.json();

                if (data.error) {
                    throw new Error(data.message || 'Erreur du serveur');
                }

                this.editingUser = data.user;
                this.editOpen = true;

            } catch (error) {
                console.error('Erreur lors du chargement:', error);
                alert('Erreur lors du chargement des données utilisateur: ' + error.message);
            } finally {
                this.loading = false;
            }
        },
        closeEditModal() {
            this.editOpen = false;
            this.editingUser = null;
        }
     }"
     x-cloak
     class="p-4">

    <!-- Header avec espacement amélioré -->
    <div class="flex items-center justify-between mt-8 mb-4">
        <h2 class="text-2xl font-semibold">Liste des utilisateurs</h2>
        <button @click="open = true"
                class="btn btn-success px-4 py-2 rounded shadow hover:bg-green-600 transition">
            <span class="fas fa-plus mr-2"></span> Nouveau Utilisateur
        </button>
    </div>

    <!-- Messages de succès -->
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- Tableau des utilisateurs -->
    <div class="overflow-auto">
        <table class="table-auto w-full text-sm border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2">ID Agent</th>
                    <th class="px-4 py-2">Login</th>
                    <th class="px-4 py-2">Noms & Prénoms</th>
                    <th class="px-4 py-2">Service</th>
                    <th class="px-4 py-2">Profil</th>
                    <th class="px-4 py-2">Actif</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="border px-4 py-2">{{ $user->id }}</td>
                    <td class="border px-4 py-2">{{ $user->login }}</td>
                    <td class="border px-4 py-2">{{ $user->name }} {{ $user->prenom }}</td>
                    <td class="border px-4 py-2">{{ $user->service->libelle ?? '-' }}</td>
                    <td class="border px-4 py-2">{{ $user->profil->libelle ?? '-' }}</td>
                    <td class="border px-4 py-2">{{ $user->active ? 'Actif' : 'Inactif' }}</td>
                    <td class="border px-4 py-2">
                        <div class="flex space-x-2">
                            <button @click="openEditModal({{ $user->id }})"
                                    :disabled="loading"
                                    class="text-blue-600 hover:underline focus:outline-none disabled:opacity-50">
                                <span x-show="!loading">Modifier</span>
                                <span x-show="loading">Chargement...</span>
                            </button>
                            <form action="{{ $user->active ? route('users.deactivate', $user->id) : route('users.activate', $user->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="{{ $user->active ? 'text-red-600' : 'text-green-600' }} hover:underline"
                                        onclick="return confirm('Êtes-vous sûr de vouloir {{ $user->active ? 'désactiver' : 'activer' }} cet utilisateur ?')">
                                    {{ $user->active ? 'Désactiver' : 'Activer' }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal Ajout Utilisateur -->
    <div x-show="open"
         x-cloak
         :class="{ 'modal-hidden': !open }"
         class="fixed inset-0 z-30 flex items-center justify-center bg-black bg-opacity-50 modal-overlay"
         @keydown.escape.window="open = false"
         style="display: none;">

        <div @click.away="open = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-90 translate-y-4"
             class="bg-white p-6 rounded-lg shadow-xl max-w-2xl w-full m-4 max-h-screen overflow-y-auto">

            <!-- Header du modal -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold">Ajouter un utilisateur</h3>
                <button @click="open = false"
                        class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Messages d'erreur -->
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4 text-sm">
                    <div class="flex items-center mb-2">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium">Erreurs de validation :</span>
                    </div>
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Formulaire d'ajout -->
            <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Noms <span class="text-red-500">*</span>:</label>
                        <input type="text"
                               name="name"
                               value="{{ old('name') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prénoms <span class="text-red-500">*</span>:</label>
                        <input type="text"
                               name="prenom"
                               value="{{ old('prenom') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Login <span class="text-red-500">*</span>:</label>
                        <input type="text"
                               name="login"
                               value="{{ old('login') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span>:</label>
                        <input type="email"
                               name="email"
                               value="{{ old('email') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe <span class="text-red-500">*</span>:</label>
                        <input type="password"
                               name="userpass"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer mot de passe <span class="text-red-500">*</span>:</label>
                        <input type="password"
                               name="userpass_confirmation"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Service <span class="text-red-500">*</span>:</label>
                        <select name="idserv"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required>
                            <option value="">Sélectionner un service</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}" @selected(old('idserv') == $service->id)>
                                    {{ $service->libelle }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Profil <span class="text-red-500">*</span>:</label>
                        <select name="Profil"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required>
                            <option value="">Sélectionner un profil</option>
                            @foreach ($profils as $profil)
                                <option value="{{ $profil->id }}" @selected(old('Profil') == $profil->id)>
                                    {{ $profil->libelle }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <button type="button"
                            @click="open = false"
                            class="px-4 py-2 text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <span class="fas fa-plus mr-2"></span>
                        Ajouter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Modification -->
    <div x-show="editOpen"
         x-cloak
         :class="{ 'modal-hidden': !editOpen }"
         class="fixed inset-0 z-30 flex items-center justify-center bg-black bg-opacity-50 modal-overlay"
         @keydown.escape.window="closeEditModal()"
         style="display: none;">

        <div @click.away="closeEditModal()"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-90 translate-y-4"
             class="bg-white p-6 rounded-lg shadow-xl max-w-2xl w-full m-4 max-h-screen overflow-y-auto">

            <!-- Header du modal -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold">Modifier l'utilisateur</h3>
                <button @click="closeEditModal()"
                        class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Formulaire de modification avec action dynamique -->
            <form :action="editingUser ? `/gestion-utilisateurs/${editingUser.id}` : '#'"
                  method="POST"
                  class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Noms <span class="text-red-500">*</span>:</label>
                        <input type="text"
                               name="name"
                               :value="editingUser?.name || ''"
                               @input="if(editingUser) editingUser.name = $event.target.value"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prénoms <span class="text-red-500">*</span>:</label>
                        <input type="text"
                               name="prenom"
                               :value="editingUser?.prenom || ''"
                               @input="if(editingUser) editingUser.prenom = $event.target.value"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Login :</label>
                        <input type="text"
                               :value="editingUser?.login || ''"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500"
                               disabled
                               readonly>
                        <p class="text-xs text-gray-500 mt-1">Le login ne peut pas être modifié</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span>:</label>
                        <input type="email"
                               name="email"
                               :value="editingUser?.email || ''"
                               @input="if(editingUser) editingUser.email = $event.target.value"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Service <span class="text-red-500">*</span>:</label>
                        <select name="service_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required>
                            <option value="">Sélectionner un service</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}"
                                        :selected="editingUser && editingUser.service_id == {{ $service->id }}">
                                    {{ $service->libelle }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Profil <span class="text-red-500">*</span>:</label>
                        <select name="profil_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required>
                            <option value="">Sélectionner un profil</option>
                            @foreach ($profils as $profil)
                                <option value="{{ $profil->id }}"
                                        :selected="editingUser && editingUser.profil_id == {{ $profil->id }}">
                                    {{ $profil->libelle }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <button type="button"
                            @click="closeEditModal()"
                            class="px-4 py-2 text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition">
                        <span class="fas fa-save mr-2"></span>
                        Modifier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
