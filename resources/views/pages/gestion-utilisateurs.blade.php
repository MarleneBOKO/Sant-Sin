@extends('../layout/' . $layout)

@section('subhead')
    <title>Gestion Utilisateurs</title>
    <script src="//unpkg.com/alpinejs" defer></script>
    <style>
        [x-cloak] { display: none !important; }
        .modal-overlay {
            backdrop-filter: blur(4px);
            animation: fadeIn 0.3s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-primary { background: #dbeafe; color: #1e40af; }
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }
        .status-dot.active { background: #10b981; }
        .status-dot.inactive { background: #ef4444; }
        .stat-card {
            transition: transform 0.2s ease-in-out;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
    </style>
@endsection

@section('subcontent')
<div x-data="{
    open: false,
    editOpen: false,
    resetOpen: false,
    editingUser: null,
    resettingUser: null,
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
    openResetModal(user) {
        this.resettingUser = user;
        this.resetOpen = true;
        // Définir l'action du formulaire dynamiquement
        this.$nextTick(() => {
            const form = document.getElementById('resetForm');
            if (form && user.id) {
                form.action = `/users/${user.id}/reset-password`;
            }
        });
    },
    closeEditModal() {
        this.editOpen = false;
        this.editingUser = null;
    },
    closeResetModal() {
        this.resetOpen = false;
        this.resettingUser = null;
    }
}"

     x-cloak
     class="p-6 space-y-6">

    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Gestion des utilisateurs</h2>
            <p class="text-gray-600 mt-1">Administrez les comptes et permissions</p>
        </div>
        <button @click="open = true"
                class="btn bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-3 rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition self-start lg:self-auto">
            <i class="fas fa-plus mr-2"></i> Nouvel utilisateur
        </button>
    </div>

    <!-- Alertes -->
    @if (session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg shadow-sm">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                <p class="text-green-700">{{ session('success') }}</p>
                @if(session('success') && strpos(session('success'), 'Mot de passe temporaire') !== false)
                    <button @click="copyPassword('{{ session('temp_password') }}')"
                            class="ml-auto text-green-600 hover:text-green-800">
                        <i class="fas fa-copy"></i> Copier
                    </button>
                @endif
            </div>
        </div>
    @endif

    @if(session('password_warning'))
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-r-lg shadow-sm">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-yellow-500 mr-3"></i>
                <p class="text-yellow-700">{{ session('password_warning') }}</p>
            </div>
        </div>
    @endif

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-6 rounded-xl text-white shadow-lg stat-card">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-blue-100 text-sm">Total utilisateurs</p>
                    <h3 class="text-3xl font-bold mt-1">{{ $users->count() }}</h3>
                </div>
                <i class="fas fa-users text-3xl text-blue-200"></i>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 p-6 rounded-xl text-white shadow-lg stat-card">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-green-100 text-sm">Actifs</p>
                    <h3 class="text-3xl font-bold mt-1">{{ $users->where('active', true)->count() }}</h3>
                </div>
                <i class="fas fa-user-check text-3xl text-green-200"></i>
            </div>
        </div>

        <div class="bg-gradient-to-br from-red-500 to-red-600 p-6 rounded-xl text-white shadow-lg stat-card">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-red-100 text-sm">Inactifs</p>
                    <h3 class="text-3xl font-bold mt-1">{{ $users->where('active', false)->count() }}</h3>
                </div>
                <i class="fas fa-user-times text-3xl text-red-200"></i>
            </div>
        </div>

        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 p-6 rounded-xl text-white shadow-lg stat-card">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-yellow-100 text-sm">MDP à changer</p>
                    <h3 class="text-3xl font-bold mt-1">{{ $users->where('must_change_password', true)->count() }}</h3>
                </div>
                <i class="fas fa-key text-3xl text-yellow-200"></i>
            </div>
        </div>
    </div>

    <!-- Tableau -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-auto w-full">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Utilisateur</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Login</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Service</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Profil</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Mot de passe</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                @foreach ($users as $user)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr($user->prenom, 0, 1)) }}
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }} {{ $user->prenom }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-mono bg-gray-100 px-2 py-1 rounded">{{ $user->login }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $user->service->libelle ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <span class="badge badge-primary">{{ $user->profil->libelle ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($user->active)
                                <span class="badge badge-success">
                                    <span class="status-dot active"></span>Actif
                                </span>
                            @else
                                <span class="badge badge-danger">
                                    <span class="status-dot inactive"></span>Inactif
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($user->password_expired)
                                <span class="badge badge-danger">
                                    <i class="fas fa-exclamation-circle mr-1"></i>Expiré
                                </span>
                            @elseif($user->must_change_password)
                                <span class="badge badge-warning">
                                    <i class="fas fa-clock mr-1"></i>À changer
                                </span>
                            @else
                                <span class="badge badge-success">
                                    <i class="fas fa-check-circle mr-1"></i>Valide
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex justify-center space-x-2">
                                <button @click="openEditModal({{ $user->id }})"
                                        class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 p-2 rounded transition"
                                        title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <button @click="openResetModal({{ $user->toJson() }})"
                                        class="text-orange-600 hover:text-orange-800 hover:bg-orange-50 p-2 rounded transition"
                                        title="Réinitialiser MDP">
                                    <i class="fas fa-key"></i>
                                </button>

                                <form action="{{ $user->active ? route('users.deactivate', $user->id) : route('users.activate', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="{{ $user->active ? 'text-red-600 hover:text-red-800 hover:bg-red-50' : 'text-green-600 hover:text-green-800 hover:bg-green-50' }} p-2 rounded transition"
                                            onclick="return confirm('{{ $user->active ? 'Désactiver' : 'Activer' }} cet utilisateur ?')"
                                            title="{{ $user->active ? 'Désactiver' : 'Activer' }}">
                                        <i class="fas fa-{{ $user->active ? 'ban' : 'check' }}"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
<div class="mt-6">
    {{ $users->links() }}
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

    <!-- Modal de Réinitialisation du Mot de Passe -->
    <div x-show="resetOpen"
         x-cloak
         :class="{ 'modal-hidden': !resetOpen }"
         class="fixed inset-0 z-30 flex items-center justify-center bg-black bg-opacity-50 modal-overlay"
         @keydown.escape.window="closeResetModal()"
         style="display: none;">

        <div @click.away="closeResetModal()"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-90 translate-y-4"
             class="bg-white p-6 rounded-lg shadow-xl max-w-md w-full m-4">

            <!-- Header du modal -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-orange-600">Réinitialiser le mot de passe</h3>
                <button @click="closeResetModal()"
                        class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Informations de l'utilisateur -->
            <div class="bg-orange-50 border border-orange-200 rounded-md p-4 mb-4">
                <div class="flex items-center">
                    <div class="h-10 w-10 flex-shrink-0 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white font-semibold">
                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}{{ strtoupper(substr($user->prenom ?? 'U', 0, 1)) }}
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900" x-text="resettingUser ? resettingUser.name + ' ' + resettingUser.prenom : ''"></p>
                        <p class="text-sm text-gray-500" x-text="resettingUser ? resettingUser.login : ''"></p>
                    </div>
                </div>
            </div>

            <!-- Formulaire de réinitialisation -->
            <form id="resetForm" action="" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')
                <input type="hidden" name="user_id" x-bind:value="resettingUser ? resettingUser.id : ''">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe <span class="text-red-500">*</span>:</label>
                    <input type="password"
                           name="password"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                           required
                           minlength="12">
                    <p class="text-xs text-gray-500 mt-1">Minimum 12 caractères</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe <span class="text-red-500">*</span>:</label>
                    <input type="password"
                           name="password_confirmation"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                           required
                           minlength="12">
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-yellow-400 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-sm text-yellow-800 font-medium">Attention</p>
                            <p class="text-xs text-yellow-700 mt-1">
                                L'utilisateur devra changer son mot de passe à la prochaine connexion.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <button type="button"
                            @click="closeResetModal()"
                            class="px-4 py-2 text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-white bg-orange-600 border border-transparent rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 transition">
                        <span class="fas fa-key mr-2"></span>
                        Réinitialiser
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal d'Ajout d'Utilisateur -->
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
</div>
@endsection

