<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parametre extends Model
{
    protected $table = 'parametres';
    protected $primaryKey = 'codeparam';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'codeparam',
        'typaram',
        'codtyparam',
        'libelleparam',
        'param1',
        'param2',
        'param3',
    ];
}
