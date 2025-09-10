@extends('layout.' . $layout)

@section('subhead')
    <title>Gestion des profils</title>
    <script src="//unpkg.com/alpinejs" defer></script>
    <style>
        [x-cloak] { display: none !important; }
        .modal-hidden { display: none !important; }
        .modal-overlay { backdrop-filter: blur(2px); animation: fadeIn 0.2s ease-out; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
@endsection

@section('subcontent')
<div x-data="{
        openAdd: false,
        editOpen: false,
        editingProfil: null,
        openEditModal(profil) {
            this.editingProfil = profil;
            this.editOpen = true;
        }
    }" x-cloak class="p-4">

    <h2 class="text-2xl font-semibold mb-4">Liste des profils</h2>
    <button @click="openAdd = true" class="btn btn-success mb-4">Ajouter un profil</button>

    <table class="table-auto w-full border text-sm">
        <thead class="bg-gray-100">
            <tr><th class="px-4 py-2">Code</th><th class="px-4 py-2">Libellé</th><th class="px-4 py-2">Actions</th></tr>
        </thead>
        <tbody>
            @foreach ($profils as $profil)
            <tr class="hover:bg-gray-50">
                <td class="border px-4 py-2">{{ $profil->code_profil }}</td>
                <td class="border px-4 py-2">{{ $profil->libelle }}</td>
                <td class="border px-4 py-2">
                    <button @click="openEditModal({ id: {{ $profil->id }}, code: '{{ $profil->code_profil }}', libelle: '{{ $profil->libelle }}' })"
                            class="text-blue-600 hover:underline">
                        Modifier
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Modal Ajout -->
    <div x-show="openAdd" x-cloak :class="{ 'modal-hidden': !openAdd }"
         class="fixed inset-0 z-30 flex items-center justify-center bg-black bg-opacity-50 modal-overlay"
         @keydown.escape.window="openAdd = false" style="display:none;">
        <div @click.away="openAdd = false"
             class="bg-white p-6 rounded-lg shadow-xl max-w-lg w-full m-4">
            <h3 class="text-xl font-bold mb-4">Ajouter un profil</h3>
            <form action="{{ route('profils.store') }}" method="POST" class="space-y-4">
                @csrf
                <div><label>Code :</label><input type="text" name="code_profil" class="input w-full border rounded" required></div>
                <div><label>Libellé :</label><input type="text" name="libelle" class="input w-full border rounded" required></div>
                <div class="flex justify-end space-x-2 pt-4">
                    <button type="button" @click="openAdd = false" class="btn btn-secondary">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Modification -->
    <div x-show="editOpen" x-cloak :class="{ 'modal-hidden': !editOpen }"
         class="fixed inset-0 z-30 flex items-center justify-center bg-black bg-opacity-50 modal-overlay"
         @keydown.escape.window="editOpen = false" style="display:none;">
        <div @click.away="editOpen = false"
             class="bg-white p-6 rounded-lg shadow-xl max-w-lg w-full m-4">
            <h3 class="text-xl font-bold mb-4">Modifier le profil</h3>
       <form :action="'/profils/' + editingProfil.id" method="POST">

                @csrf
                @method('PUT')
                <div><label>Code :</label><input type="text" name="code_profil" x-model="editingProfil.code" class="input w-full border rounded" readonly></div>
                <div><label>Libellé :</label><input type="text" name="libelle" x-model="editingProfil.libelle" class="input w-full border rounded" required></div>
                <div class="flex justify-end space-x-2 pt-4">
                    <button type="button" @click="editOpen = false" class="btn btn-secondary">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
