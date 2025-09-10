@extends('layout.' . $layout)

@section('subhead')
    <title>Modifier un utilisateur</title>
@endsection

@section('subcontent')
<div class="p-4">

    <h2 class="text-2xl font-semibold mb-4">Modifier l’utilisateur</h2>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4 text-sm">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('users.update', $user->id) }}" method="POST" class="space-y-4">
        @csrf @method('PUT')

        <div>
            <label class="block">Noms :</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="input w-full border rounded" required>
        </div>

        <div>
            <label class="block">Prénoms :</label>
            <input type="text" name="prenom" value="{{ old('prenom', $user->prenom) }}" class="input w-full border rounded" required>
        </div>

        <div>
            <label class="block">Email :</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="input w-full border rounded" required>
        </div>

        <div>
            <label class="block">Service :</label>
            <select name="service_id" class="input w-full border rounded" required>
                @foreach ($services as $service)
                    <option value="{{ $service->id }}" @selected(old('service_id', $user->service_id) == $service->id)>
                        {{ $service->libelle }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block">Profil :</label>
            <select name="profil_id" class="input w-full border rounded" required>
                @foreach ($profils as $profil)
                    <option value="{{ $profil->id }}" @selected(old('profil_id', $user->profil_id) == $profil->id)>
                        {{ $profil->libelle }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex justify-end space-x-2">
            <button type="submit" class="btn btn-primary px-4 py-2 rounded">Enregistrer</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary px-4 py-2 rounded">Annuler</a>
        </div>
    </form>
</div>
@endsection
