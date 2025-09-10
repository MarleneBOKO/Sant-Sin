<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Désactive la gestion automatique des timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Ces constantes forcent Laravel à ignorer les colonnes created_at et updated_at
     * même si elles existent dans la table.
     */
    const CREATED_AT = null;
    const UPDATED_AT = null;

    /**
     * Les attributs assignables en masse.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'login',
        'nom',
        'prenom',
        'active',
        'profil_id',
        'service_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int,string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Les casts à appliquer aux attributs.
     *
     * @return array<string,string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'active' => 'boolean',
        ];
    }

    public function profil()
    {
        return $this->belongsTo(Profil::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
