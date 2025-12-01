<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Unidad;
use App\Models\Egresado;
use App\Models\Encuesta;
use App\Models\Pregunta;
use App\Models\Opcion;
use App\Models\Dimension;
use App\Models\Carrera;
use App\Models\Generacion;
use App\Models\NivelEstudio;
use App\Models\CicloEscolar;
use App\Policies\UnidadPolicy;
use App\Policies\EgresadoPolicy;
use App\Policies\EncuestaPolicy;
use App\Policies\PreguntaPolicy;
use App\Policies\OpcionPolicy;
use App\Policies\DimensionPolicy;
use App\Policies\CarreraPolicy;
use App\Policies\GeneracionPolicy;
use App\Policies\NivelEstudioPolicy;
use App\Policies\CicloEscolarPolicy;
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
        // Acceso total para Administrador general y Administrador académico
        Gate::before(function ($user, $ability) {
            if ($user->hasRole(['Administrador general', 'Administrador academico'])) {
                return true;
            }
        });
        
        // Registrar todas las policies
        Gate::policy(Unidad::class, UnidadPolicy::class);
        Gate::policy(Egresado::class, EgresadoPolicy::class);
        Gate::policy(Encuesta::class, EncuestaPolicy::class);
        Gate::policy(Pregunta::class, PreguntaPolicy::class);
        Gate::policy(Opcion::class, OpcionPolicy::class);
        Gate::policy(Dimension::class, DimensionPolicy::class);
        Gate::policy(Carrera::class, CarreraPolicy::class);
        Gate::policy(Generacion::class, GeneracionPolicy::class);
        Gate::policy(NivelEstudio::class, NivelEstudioPolicy::class);
        Gate::policy(CicloEscolar::class, CicloEscolarPolicy::class);
        
        // Registrar el observer para cambiar roles automáticamente
        Egresado::observe(EgresadoObserver::class);
    }
}
