<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypePrestataire extends Model
{
    protected $table = 'type_prestataires';

    protected $primaryKey = 'code_type_prestataire';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code_type_prestataire',
        'libelle_type_prestataire',
    ];

    public function partenaires()
    {
        return $this->hasMany(Partenaire::class, 'code_type_prestataire', 'code_type_prestataire');
    }
}
