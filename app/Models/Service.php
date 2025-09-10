<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class Service extends Model
{
public $timestamps = false;

public function direction()
{
    return $this->belongsTo(Direction::class);
}

public function users()
{
    return $this->hasMany(User::class);
}
}
