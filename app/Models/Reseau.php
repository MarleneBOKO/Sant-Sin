<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class Reseau extends Model


{
     public $timestamps = false;
   const CREATED_AT = null;
    const UPDATED_AT = null;

     protected $table = 'reseaux';
    protected $fillable = ['code_reseau', 'libelle_reseau', 'actif'];

    public function profils()
    {
        return $this->belongsToMany(Profil::class);
    }
}
