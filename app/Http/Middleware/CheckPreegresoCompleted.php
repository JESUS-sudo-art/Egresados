<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Egresado;
use App\Models\CedulaPreegreso;

class CheckPreegresoCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // Solo aplicar para usuarios autenticados con rol Egresados
        if ($user && $user->hasRole('Egresados')) {
            // Permitir acceso a la ruta de pre-egreso y rutas de settings
            if ($request->routeIs('encuesta-preegreso') || 
                $request->routeIs('cedula-preegreso.store') ||
                $request->routeIs('settings.*') ||
                $request->routeIs('logout')) {
                return $next($request);
            }
            
            // Buscar el egresado asociado
            $egresado = Egresado::where('email', $user->email)->first();
            
            if ($egresado) {
                // Verificar si ya completó la cédula de pre-egreso
                $hasPreegreso = CedulaPreegreso::where('egresado_id', $egresado->id)->exists();
                
                if (!$hasPreegreso) {
                    // Redirigir a la encuesta de pre-egreso
                    return redirect()->route('encuesta-preegreso')
                        ->with('warning', 'Debes completar la Cédula de Pre-Egreso antes de continuar.');
                }
            }
        }
        
        return $next($request);
    }
}

