<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;  

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Forcer HTTPS si on est sur Ngrok ou en production
        /*if (str_contains(env('APP_URL'), 'ngrok-free.dev') || app()->environment('production')) {
            URL::forceScheme('https');
        }*/

        // Partage Inertia des données utilisateur
        Inertia::share('user', function () {
            $user = Auth::user();
            if ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ];
            }
            return null;
        });
    }
}