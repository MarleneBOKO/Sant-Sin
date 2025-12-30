<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CheckPasswordStatus
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // VÉRIFICATION 1 : Changement obligatoire
        if ($user->must_change_password) {
            if (!$request->is('change-password') && !$request->is('logout')) {
                return redirect()->route('password.change.form');
            }
            return $next($request);
        }

        // VÉRIFICATION 2 : Expiration
        if ($user->password_changed_at) {
            $passwordAge = Carbon::parse($user->password_changed_at)->diffInDays(now());

            // CAS A : EXPIRÉ (> 1 jour)
            if ($passwordAge > 1) {
                $user->update([
                 
                ]);

                if (!$request->is('password-expired') &&
                    !$request->is('logout') &&
                    !$request->is('password-request-reset')) {
                    return redirect()->route('password.expired');
                }
                return $next($request);
            }

            // CAS B : VA EXPIRER (0-1 jour)
            if ($passwordAge >= 0 && $passwordAge < 1) {
                // Marquer comme notifié (une seule fois)
                if (!$user->password_expiry_notified_at) {
                    $user->update(['password_expiry_notified_at' => now()]);
                }
            }
        }

        return $next($request);
    }
}


