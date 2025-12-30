<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // ✅ OBLIGATOIRE

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 🔒 Fix définitif SQL Server dates
        DB::statement("SET DATEFORMAT ymd");

        View::composer('*', function ($view) {
            $view->with('authUser', Auth::user());
        });
    }
}
