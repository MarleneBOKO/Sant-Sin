@extends('../layout/' . $layout)

@section('subhead')
    <title>Liste des Utilisateurs</title>
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">Liste des Utilisateurs</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-no-wrap items-center mt-2">
            <a href="#" class="button text-white bg-theme-1 shadow-md mr-2">Ajouter un utilisateur</a>
            <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
                <form action="{{ route('users.index') }}" method="GET">
                    <div class="w-56 relative text-gray-700 dark:text-gray-300">
                        <input type="text" name="search" value="{{ request('search') }}" class="input w-56 box pr-10 placeholder-theme-13" placeholder="Recherche...">
                        <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <i data-feather="search" class="w-4 h-4"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- BEGIN: Data List -->
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-no-wrap">Nom</th>
                        <th class="whitespace-no-wrap">Email</th>
                        <th class="whitespace-no-wrap">Profil</th>
                        <th class="text-center whitespace-no-wrap">Statut</th>
                        <th class="text-center whitespace-no-wrap">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="intro-x">
                            <td>
                                <a href="{{ route('users.show', $user->id) }}" class="font-medium whitespace-no-wrap">{{ $user->name }}</a>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->profil->libelle ?? '—' }}</td>
                            <td class="text-center">
                                @if ($user->actif)
                                    <div class="flex items-center justify-center text-theme-9">
                                        <i data-feather="check-square" class="w-4 h-4 mr-2"></i> Actif
                                    </div>
                                @else
                                    <div class="flex items-center justify-center text-theme-6">
                                        <i data-feather="x-square" class="w-4 h-4 mr-2"></i> Inactif
                                    </div>
                                @endif
                            </td>
                            <td class="table-report__action w-56">
                                <div class="flex justify-center items-center space-x-4">
                                    <a href="{{ route('users.edit', $user->id) }}" class="flex items-center text-theme-1" title="Modifier">
                                        <i data-feather="edit" class="w-4 h-4 mr-1"></i> Modifier
                                    </a>
                                    @if ($user->actif)
                                        <form action="{{ route('users.deactivate', $user->id) }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment désactiver cet utilisateur ?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="flex items-center text-theme-6" title="Désactiver">
                                                <i data-feather="slash" class="w-4 h-4 mr-1"></i> Désactiver
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('users.activate', $user->id) }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment activer cet utilisateur ?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="flex items-center text-theme-9" title="Activer">
                                                <i data-feather="check-circle" class="w-4 h-4 mr-1"></i> Activer
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Aucun utilisateur trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- END: Data List -->

        <!-- BEGIN: Pagination -->
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center mt-5">
            {{ $users->links('vendor.pagination.tailwind') }}
        </div>
        <!-- END: Pagination -->
    </div>
@endsection

@push('scripts')
<script>
    // Active feather icons (assure-toi que feather est chargé)
    document.addEventListener('DOMContentLoaded', () => {
        if (window.feather) {
            feather.replace();
        }
    });
</script>
@endpush
