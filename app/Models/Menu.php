<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Profil;

class Menu extends Model
{
    protected $fillable = ['nom', 'parent_id', 'route', 'ordre'];
 public $timestamps = false;
   const CREATED_AT = null;
    const UPDATED_AT = null;
    public function profils()
    {
        return $this->belongsToMany(Profil::class, 'menu_profil', 'menu_id', 'profil_id')
            ->withPivot('droits');

    }

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function enfants()
    {
        return $this->hasMany(Menu::class, 'parent_id');
    }
}
