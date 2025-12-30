<div class="p-6 text-center">
    <h2 class="text-xl font-bold text-red-600 mb-2">
        ❌ Erreur
    </h2>

    <p class="text-gray-700">
        {{ $message ?? 'Une erreur est survenue.' }}
    </p>

    @if(!empty($details))
        <pre class="mt-4 text-sm text-gray-500 bg-gray-100 p-3 rounded">
{{ $details }}
        </pre>
    @endif
</div>
