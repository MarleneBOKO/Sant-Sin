@extends('../layout/' . $layout)

@section('subcontent')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-center">
        <div class="w-full max-w-7xl">
            <div class="bg-white shadow rounded mb-6">
                <div class="bg-blue-600 py-3 px-4 rounded-t">
                    <h6 id="formTitle" class="text-center text-white font-semibold text-lg">
                        EDITION DE LISTING DES FACTURES
                    </h6>
                </div>
                <div class="p-6">
                    <form method="GET" action="{{ route('factures.listing') }}" id="filterForm">
                        <!-- Type de facture -->
                        <div class="mb-4">
                            <label for="type_facture" class="block mb-2 font-medium text-gray-700">Type de listing :</label>
                            <select name="type_facture" id="type_facture" required
                                class="block w-full md:w-1/3 rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Sélectionner...</option>
                                <option value="recues" {{ request('type_facture') == 'recues' ? 'selected' : '' }}>Listing des factures reçues</option>
                                <option value="courrier" {{ request('type_facture') == 'courrier' ? 'selected' : '' }}>Listes courriers Reçus</option>
                                <option value="instance" {{ request('type_facture') == 'instance' ? 'selected' : '' }}>Factures en instance</option>
                                <option value="etat_reglement" {{ request('type_facture') == 'etat_reglement' ? 'selected' : '' }}>Etat de règlement</option> <!-- Nouveau -->
                            </select>
                        </div>

                        <!-- Champs dynamiques -->
                        <div id="dynamicFields" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4" style="display: none;"></div>

                        <hr class="my-4 border-gray-300" />

                        <div class="flex justify-center">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded flex items-center gap-2">
                                <span class="fa fa-search"></span>
                                <span>Afficher</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Résultats -->
            @if(isset($factures) && count($factures) > 0)
            <div class="bg-white shadow rounded">
                <div class="border-b border-gray-200 px-6 py-3 flex justify-between items-center">
                    <h5 class="text-blue-600 font-semibold text-lg">
                        Liste des factures
                    </h5>
                    <div class="flex gap-2">
                        <button id="btnExcel" class="bg-green-600 hover:bg-green-700 text-white font-medium py-1 px-3 rounded text-sm">
                            📊 Excel
                        </button>
                        <button id="btnPDF" class="bg-red-600 hover:bg-red-700 text-white font-medium py-1 px-3 rounded text-sm">
                            📄 PDF
                        </button>
                        <button id="btnCopy" class="bg-purple-600 hover:bg-purple-700 text-white font-medium py-1 px-3 rounded text-sm">
                            📋 Copier
                        </button>
                    </div>
                </div>
                <div class="p-4 overflow-x-auto">
                    <table id="facturesTable" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">N°</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Prestataire</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Référence</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Mois</th>
                                <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase">Montant</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Réseau</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($factures as $index => $facture)
                            <tr class="hover:bg-gray-50">
                                <td class="px-2 py-2">{{ $index + 1 }}</td>
                                <td class="px-2 py-2">{{ $facture->Date_Enregistrement ?? '-' }}</td>
                                <td class="px-2 py-2">{{ $facture->Tiers ?? '-' }}</td>
                                <td class="px-2 py-2">{{ $facture->Reference_facture ?? '-' }}</td>
                                <td class="px-2 py-2">{{ $facture->Mois_Facture ?? '-' }}</td>
                                <td class="px-2 py-2 text-right">{{ number_format($facture->Montant_facture ?? 0, 0, ',', ' ') }}</td>
                                <td class="px-2 py-2">{{ $facture->transmission ?? '-' }}</td>
                                <td class="px-2 py-2">{{ $facture->reseau ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @elseif(request()->has('type_facture'))
            <div class="mt-6 p-4 bg-blue-100 text-blue-700 rounded text-center">
                Aucun résultat trouvé
            </div>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
(function() {
    'use strict';

    const formFields = {
        recues: {
            title: 'Listing des factures santé reçues',
            fields: `
                <div>
                    <label class="block mb-1 text-sm font-medium">Réseau :</label>
                    <select name="reseau" class="w-full rounded border border-gray-300 px-3 py-2">
                        <option value="tt">Tous les réseaux</option>
                        <option value="phar">Pharmacie</option>
                        <option value="para">Parapharmacie</option>
                        <option value="ind">Individuels</option>
                        <option value="evac">Evacuation</option>
                        <option value="apfd">Appels de fonds</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium">Statut :</label>
                    <select name="statut_reglement" class="w-full rounded border border-gray-300 px-3 py-2">
                        <option value="ttreg">Tous</option>
                        <option value="reg">Réglés</option>
                        <option value="nreg">Non réglés</option>
                        <option value="annul">Annulées</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium">Du :</label>
                    <input type="date" name="date_debut" required max="${new Date().toISOString().split('T')[0]}"
                        class="w-full rounded border border-gray-300 px-3 py-2">
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium">Au :</label>
                    <input type="date" name="date_fin" required max="${new Date().toISOString().split('T')[0]}"
                        class="w-full rounded border border-gray-300 px-3 py-2">
                </div>
            `
        },
        courrier: {
            title: 'Edition Liste des courriers réceptionnés',
            fields: `
                <div>
                    <label class="block mb-1 text-sm font-medium">Type :</label>
                    <select name="type_courrier" class="w-full rounded border border-gray-300 px-3 py-2">
                        <option value="1">Courrier Santé</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium">Nature :</label>
                    <select name="nature" class="w-full rounded border border-gray-300 px-3 py-2">
                        <option value="3">Facture</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium">Date Début :</label>
                    <input type="date" name="date_debut" required max="${new Date().toISOString().split('T')[0]}"
                        class="w-full rounded border border-gray-300 px-3 py-2">
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium">Date Fin :</label>
                    <input type="date" name="date_fin" required max="${new Date().toISOString().split('T')[0]}"
                        class="w-full rounded border border-gray-300 px-3 py-2">
                </div>
            `
        },
        instance: {
            title: 'Factures en instance',
            fields: `
                <div>
                    <label class="block mb-1 text-sm font-medium">Réseau :</label>
                    <select name="reseau" class="w-full rounded border border-gray-300 px-3 py-2">
                        <option value="tt">Tous les réseaux</option>
                        <option value="phar">Pharmacie</option>
                        <option value="para">Parapharmacie</option>
                        <option value="ind">Individuels</option>
                        <option value="evac">Evacuation</option>
                        <option value="apfd">Appels de fonds</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium">Statut Facture :</label>
                    <select name="statut_instance" class="w-full rounded border border-gray-300 px-3 py-2">
                        <option value="ei">En instance</option>
                        <option value="tr">Traité</option>
                        <option value="An">Annulée</option>
                        <option value="it">En instance à la trésorerie</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium">Période du :</label>
                    <input type="date" name="date_debut" required max="${new Date().toISOString().split('T')[0]}"
                        class="w-full rounded border border-gray-300 px-3 py-2">
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium">Au :</label>
                    <input type="date" name="date_fin" required max="${new Date().toISOString().split('T')[0]}"
                        class="w-full rounded border border-gray-300 px-3 py-2">
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium">Vu Au :</label>
                    <input type="date" name="DateS" required max="${new Date().toISOString().split('T')[0]}"
                        class="w-full rounded border border-gray-300 px-3 py-2">
                </div>
            `
        },
        etat_reglement: { // Nouveau : basé sur ton code PHP
            title: 'ETAT DES REGLEMENTS',
            fields: `
                <div>
                    <label class="block mb-1 text-sm font-medium">Réseau :</label>
                    <select name="reseau" class="w-full rounded border border-gray-300 px-3 py-2">
                        <option value="tt">Tous les réseaux</option>
                        <option value="phar">Pharmacie</option>
                        <option value="para">Parapharmacie</option>
                        <option value="ind">Individuels</option>
                        <option value="evac">Evacuation</option>
                        <option value="apfd">Appels de fonds</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium">Période du :</label>
                    <input type="date" name="date_debut" required max="${new Date().toISOString().split('T')[0]}"
                        class="w-full rounded border border-gray-300 px-3 py-2">
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium">Au :</label>
                    <input type="date" name="date_fin" required max="${new Date().toISOString().split('T')[0]}"
                        class="w-full rounded border border-gray-300 px-3 py-2">
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium">Situation Au :</label>
                    <input type="date" name="finperiode" max="${new Date().toISOString().split('T')[0]}"
                        class="w-full rounded border border-gray-300 px-3 py-2">
                </div>
            `
        }
    };

    function updateFields() {
        const typeFacture = document.getElementById('type_facture').value;
        const dynamicFields = document.getElementById('dynamicFields');
        const formTitle = document.getElementById('formTitle');

        if (typeFacture && formFields[typeFacture]) {
            dynamicFields.innerHTML = formFields[typeFacture].fields;
            dynamicFields.style.display = 'grid';
            formTitle.textContent = formFields[typeFacture].title;
        } else {
            dynamicFields.style.display = 'none';
            dynamicFields.innerHTML = '';
            formTitle.textContent = 'EDITION DE LISTING DES FACTURES';
        }
    }

    document.getElementById('type_facture').addEventListener('change', updateFields);
    updateFields();

    // Boutons export
    @if(isset($factures) && count($factures) > 0)
    const btnExcel = document.getElementById('btnExcel');
    const btnPDF = document.getElementById('btnPDF');
    const btnCopy = document.getElementById('btnCopy');

    if (btnExcel) {
        btnExcel.onclick = function() {
            const table = document.getElementById('facturesTable');
            const wb = XLSX.utils.table_to_book(table, {sheet: "Factures"});
            XLSX.writeFile(wb, 'factures_' + new Date().toISOString().slice(0,10) + '.xlsx');
        };
    }

    if (btnPDF) {
        btnPDF.onclick = function() {
            const table = document.getElementById('facturesTable');
            const headers = [], data = [];
            table.querySelectorAll('thead th').forEach(th => headers.push(th.innerText));
            table.querySelectorAll('tbody tr').forEach(row => {
                const rowData = [];
                row.querySelectorAll('td').forEach(td => rowData.push(td.innerText));
                data.push(rowData);
            });

            const docDef = {
                pageOrientation: 'landscape',
                content: [{
                    text: 'Liste des Factures', style: 'header', alignment: 'center', margin: [0,0,0,20]
                }, {
                    table: {
                        headerRows: 1,
                        body: [headers, ...data]
                    }
                }],
                styles: { header: { fontSize: 16, bold: true } }
            };
            pdfMake.createPdf(docDef).download('factures.pdf');
        };
    }

    if (btnCopy) {
        btnCopy.onclick = function() {
            const table = document.getElementById('facturesTable');
            let text = '';
            table.querySelectorAll('tr').forEach(row => {
                const cols = Array.from(row.querySelectorAll('td, th')).map(c => c.innerText);
                text += cols.join('\t') + '\n';
            });
            navigator.clipboard.writeText(text).then(() => alert('Copié!'));
        };
    }
    @endif
})();
</script>
@endsection
