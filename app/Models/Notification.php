<?php
// app/Models/Notification.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Notification extends Model
{
    protected $table = 'notifications';
protected $dateFormat = 'Ymd H:i:s';
    protected $fillable = [
        'user_id',
        'facture_id',
        'type',
        'titre',
        'message',
        'lue',
        'priorite',
        'date_limite',
    ];

   protected $casts = [
        'lue' => 'boolean',
        // Utiliser 'date' ici car votre colonne SQL est de type [date]
        'date_limite' => 'date',
    ];
    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec la facture
     */
    public function facture()
    {
        return $this->belongsTo(LigneSuivi::class, 'facture_id', 'Id_Ligne');
    }

    /**
     * Scope pour les notifications non lues
     */
    public function scopeNonLues($query)
    {
        return $query->where('lue', false);
    }

    /**
     * Scope pour les notifications urgentes
     */
    public function scopeUrgentes($query)
    {
        return $query->where('priorite', 'haute')
            ->where('date_limite', '<=', Carbon::now()->addDay());
    }
}
