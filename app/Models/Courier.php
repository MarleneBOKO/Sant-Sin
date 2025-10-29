<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{


     protected $table = 'courrier'; // Nom de la table
    protected $primaryKey = 'NumCour';

    protected $keyType = 'string'; // Ou 'int' selon les types
    protected $fillable = [
        'NumCour', 'CodeCour', 'RefCour', 'DateEdit', 'DateRecep', 'DateEnreg',
        'Image', 'Chemin', 'CodeNat', 'CodeType', 'Civilite', 'Nom', 'Prenom',
        'Objet', 'expediteur', 'usersaisie', 'Code_Civilite', 'annee', 'telephone'
    ];

}
