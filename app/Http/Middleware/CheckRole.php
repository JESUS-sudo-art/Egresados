<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Debug temporal
        \Log::info('CheckRole - Usuario: ' . $request->user()->email);
        \Log::info('CheckRole - Roles requeridos: ' . json_encode($roles));
        \Log::info('CheckRole - Roles del usuario: ' . json_encode($request->user()->roles->pluck('name')->toArray()));

        if (!$request->user()->hasAnyRole($roles)) {
            \Log::error('CheckRole - ACCESO DENEGADO para usuario: ' . $request->user()->email);
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        \Log::info('CheckRole - ACCESO PERMITIDO');
        return $next($request);
    }
}
