<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;
use App\Models\Notification;

class User extends Authenticatable
{
    protected $fillable = [
        'name', 'prenom', 'login', 'email', 'password', 'service_id', 'profil_id', 'active',
        'must_change_password', 'password_changed_at', 'password_expired', 'password_expiry_notified_at',
    ];

    protected $hidden = ['password', 'remember_token'];
 public $timestamps = false;
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'password_expiry_notified_at' => 'datetime',
        'active' => 'boolean',
        'must_change_password' => 'boolean',
        'password_expired' => 'boolean',
    ];

    public function profil()
    {
        return $this->belongsTo(Profil::class, 'profil_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function notificationsFactures()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function getFullNameAttribute()
    {
        return strtoupper($this->name) . ' ' . ucfirst($this->prenom);
    }

    public function passwordExpiringSoon()
    {
        if (!$this->password_changed_at) return false;
        $age = Carbon::parse($this->password_changed_at)->diffInDays(now());
        return $age >= 0 && $age < 1;  // Ajusté pour test (au lieu de 25-30)
    }

    public function daysUntilExpiry()
    {
        if (!$this->password_changed_at) return 1;  // Ajusté pour test (au lieu de 30)
        return 1 - Carbon::parse($this->password_changed_at)->diffInDays(now());
    }

    public function isPasswordExpired()
    {

        if (!$this->password_changed_at) return false;
        return Carbon::parse($this->password_changed_at)->diffInDays(now()) > 1;  // Ajusté pour test (au lieu de 30)
    }

    public function notifications()
    {
        // Un utilisateur a plusieurs notifications
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function getPasswordAge()
    {
        return $this->password_changed_at ? Carbon::parse($this->password_changed_at)->diffInDays(now()) : 0;
    }
}
