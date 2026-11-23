<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // RNF 2.2 — Gate global: la regla vive aquí, desacoplada del controlador.
        Gate::define('ver-metricas', function (User $user): Response {
            return $user->role === User::ROLE_ADMIN
                ? Response::allow()
                : Response::deny('Acceso restringido: sólo el Administrador Global puede ver las métricas.');
        });
    }
}
