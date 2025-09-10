<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuProfil extends Pivot
{
    protected $table = 'menu_profil';
 public $timestamps = false;
   const CREATED_AT = null;
    const UPDATED_AT = null;
    protected $fillable = [
        'profil_id',
        'menu_id',
        'droits', // facultatif si tu utilises la table menu_profil_droit
    ];


    // Relation vers les droits via la table menu_profil_droit
    public function droits()
    {
        return $this->belongsToMany(Droit::class, 'menu_profil_droit', 'menu_profil_id', 'droit_id');
        
    }
}
