@extends('layout.' . $layout)

@section('subhead')
    <title>Gestion des Profils</title>
    <script src="//unpkg.com/alpinejs" defer></script>
    <style>
        [x-cloak] { display: none !important; }
        .modal-overlay { backdrop-filter: blur(4px); transition: all 0.3s; }
    </style>
@endsection

@section('subcontent')
<div x-data="{
        openAdd: false,
        editOpen: false,
        editingProfil: { id: '', code: '', libelle: '' },
        openEditModal(profil) {
            this.editingProfil = profil;
            this.editOpen = true;
        }
    }" x-cloak class="p-6 bg-slate-50 min-h-screen text-slate-700 font-sans">

    <div class="max-w-full mx-auto">
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Gestion des Profils</h2>
                <p class="text-slate-500 mt-1">Configurez et gérez les rôles d'accès au système.</p>
            </div>
            <button @click="openAdd = true" 
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl font-bold transition-all shadow-lg shadow-indigo-100 active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Nouveau profil
            </button>
        </div>

        <div class="bg-white shadow-sm border border-slate-200 rounded-2xl overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="text-slate-500 uppercase text-xs tracking-widest bg-slate-50/50">
                    <tr>
                        <th class="px-6 py-4 font-semibold border-b">Code Profil</th>
                        <th class="px-6 py-4 font-semibold border-b">Libellé du Rôle</th>
                        <th class="px-6 py-4 font-semibold border-b text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($profils as $profil)
                    <tr class="hover:bg-slate-50/80 transition-all">
                        <td class="px-6 py-4 font-mono font-bold text-indigo-600">{{ $profil->code_profil }}</td>
                        <td class="px-6 py-4 font-medium text-slate-900">{{ $profil->libelle }}</td>
                        <td class="px-6 py-4 text-center">
                            <button @click="openEditModal({ id: '{{ $profil->id }}', code: '{{ $profil->code_profil }}', libelle: '{{ $profil->libelle }}' })"
                                    class="inline-flex items-center gap-1.5 text-indigo-600 hover:text-indigo-900 font-bold transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Modifier
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="openAdd" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">
        
        <div @click.away="openAdd = false" class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden border border-slate-200">
            <div class="px-8 py-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="text-xl font-bold text-slate-900">Nouveau Profil</h3>
                <button @click="openAdd = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form action="{{ route('profils.store') }}" method="POST" class="p-8 space-y-5">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Code Profil *</label>
                    <input type="text" name="code_profil" class="w-full border-2 border-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 rounded-xl text-sm py-2.5 px-4 outline-none transition-all font-semibold" placeholder="Ex: ADMIN, RRSI..." required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Libellé complet *</label>
                    <input type="text" name="libelle" class="w-full border-2 border-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 rounded-xl text-sm py-2.5 px-4 outline-none transition-all font-semibold" placeholder="Ex: Administrateur Système" required>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="openAdd = false" class="px-6 py-2.5 rounded-xl text-slate-500 hover:bg-slate-100 font-bold transition-all">Annuler</button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-2.5 rounded-xl font-bold shadow-lg transition-all active:scale-95">Créer le profil</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="editOpen" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">
        
        <div @click.away="editOpen = false" class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden border border-slate-200">
            <div class="px-8 py-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="text-xl font-bold text-slate-900">Modifier Profil</h3>
                <button @click="editOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form :action="'/profils/' + editingProfil.id" method="POST" class="p-8 space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Code Profil (Non modifiable)</label>
                    <input type="text" name="code_profil" x-model="editingProfil.code" 
                           class="w-full bg-slate-100 border border-slate-200 rounded-xl text-sm py-2.5 px-4 text-slate-400 cursor-not-allowed outline-none font-bold" readonly>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Libellé complet *</label>
                    <input type="text" name="libelle" x-model="editingProfil.libelle" 
                           class="w-full border-2 border-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 rounded-xl text-sm py-2.5 px-4 outline-none transition-all font-semibold" required>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="editOpen = false" class="px-6 py-2.5 rounded-xl text-slate-500 hover:bg-slate-100 font-bold transition-all">Annuler</button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-2.5 rounded-xl font-bold shadow-lg transition-all active:scale-95">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection