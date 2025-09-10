<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuProfilDroit extends Model
{
    protected $table = 'menu_profil_droit';
 public $timestamps = false;
   const CREATED_AT = null;
    const UPDATED_AT = null;
    protected $fillable = [
        'menu_profil_id',
        'droit_id',
    ];


    public function menuProfil()
    {
        return $this->belongsTo(MenuProfil::class);
    }

    public function droit()
    {
        return $this->belongsTo(Droit::class);
    }
}
