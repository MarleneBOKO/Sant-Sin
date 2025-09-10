<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partenaire extends Model
{
    protected $table = 'partenaires';

    protected $fillable = [
        'type',
        'nom',
        'adresse',
        'telephone',
        'email',
        'code_type_prestataire',
        'coutierG',
        'code_reseau',
    ];


    public function typePrestataire()
    {
        return $this->belongsTo(TypePrestataire::class, 'code_type_prestataire', 'code_type_prestataire');
    }

    public function reseau()
    {
        return $this->belongsTo(Reseau::class, 'code_reseau', 'code_reseau');
    }

    // Scopes

    public function scopePrestataires($query)
    {
        return $query->where('type', 'prestataire');
    }

    public function scopeSouscripteurs($query)
    {
        return $query->where('type', 'souscripteur');
    }
}
