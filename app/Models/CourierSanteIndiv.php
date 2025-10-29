<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CourierSanteIndiv extends Model
{
    protected $table = 'courier_sante_indivs';
    protected $primaryKey = 'NumCour';
    public $timestamps = false;

    protected $fillable = [
        'CodeCour',
        'NomDeposant',
        'PrenomDeposant',
        'structure',
        'motif',
        'DateDepot',
        'Comptede',
        'nbreetatdepot',
        'nbrerecu',
        'datesysteme',
        'datereception',
        'Receptioniste',
        'utilisateurSaisie',
    ];

protected $casts = [
    'DateDepot' => 'datetime:Y-m-d H:i:s',
    'datesysteme' => 'datetime:Y-m-d H:i:s',
    'datereception' => 'datetime:Y-m-d H:i:s',
    'nbreetatdepot' => 'integer',
    'nbrerecu' => 'integer',
];



}
