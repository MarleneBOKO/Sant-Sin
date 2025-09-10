<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profil extends Model
{
        public $timestamps = false;
    protected $fillable = ['code_profil', 'libelle'];

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'menu_profil', 'profil_id', 'menu_id')
            ->withPivot('droits'); // si tu stockes les droits en JSON ou autre

    }

    public function reseaux()
{
    return $this->belongsToMany(Reseau::class);
}

}
