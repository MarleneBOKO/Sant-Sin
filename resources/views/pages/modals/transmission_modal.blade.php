<div class="modal-header">
    <h5 class="modal-title">Transmettre la Facture</h5>
    <button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
    <p>Facture : {{ $ligne->Reference_Facture ?? 'N/A' }} ({{ $ligne->prest ?? $ligne->Nom_Assure }})</p>
    <form id="form-transmission">
        @csrf
        <div class="form-group">
            <label>Date de Transmission :</label>
            <input type="date" name="date_transmission" class="form-control" required value="{{ now()->format('Y-m-d') }}">
        </div>
        {{-- Ajoutez d'autres champs si besoin (e.g., commentaires) --}}
        <input type="hidden" name="id" value="{{ $ligne->id }}">
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
    <button type="button" class="btn btn-primary" onclick="submitTransmission({{ $ligne->id }})">Transmettre</button>
</div>

<script>
function submitTransmission(id) {
    const formData = new FormData(document.getElementById('form-transmission'));
    formData.append('id', id); // Pour transmission individuelle

    $.ajax({
        url: "{{ route('transmit') }}", // Ou une route dédiée pour individuel
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                alert(response.message);
                location.reload();
            } else {
                alert('Erreur: ' + response.message);
            }
        },
        error: function() {
            alert('Erreur lors de la transmission.');
        }
    });
}
</script>
