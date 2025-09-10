<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Droit extends Model

{
       public $timestamps = false;

   const CREATED_AT = null;
    const UPDATED_AT = null;
    protected $fillable = ['code', 'libelle'];

}
