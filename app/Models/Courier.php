<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    protected $table = 'courrier';  // Nom de la table
    protected $primaryKey = 'NumCour';  // Clé primaire
    public $timestamps = false;  // Pas de timestamps

    protected $fillable = [
        'NumCour', 'RefCour', 'Objet', 'expediteur', 'DateRecep', 'DateEnreg', 'DateClotureEstime', 'annee', 'codecour', 'statut', 'nbr'
    ];
    protected $casts = [
    'DateRecep' => 'datetime',
    'DateEnreg' => 'datetime',
    'DateClotureEstime' => 'datetime',
];

}
