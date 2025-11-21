<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Unidad;
use App\Models\Egresado;
use App\Policies\UnidadPolicy;
use App\Observers\EgresadoObserver;

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
        Gate::policy(Unidad::class, UnidadPolicy::class);
        
        // Registrar el observer para cambiar roles automáticamente
        Egresado::observe(EgresadoObserver::class);
    }
}
