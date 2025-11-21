<?php

namespace App\Http\Controllers;

use App\Models\Egresado;
use App\Models\EncuestaAsignada;
use App\Models\Respuesta;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $encuestasAsignadas = collect();

        // Debug: Log del email del usuario
        \Log::info('Dashboard - Usuario email: ' . $user->email);

        // Verificar si el usuario es Estudiante o Egresado (no mostrar a administradores)
        $esEstudianteOEgresado = $user->hasAnyRole(['Estudiantes', 'Egresados']);
        \Log::info('Dashboard - Es Estudiante o Egresado: ' . ($esEstudianteOEgresado ? 'Sí' : 'No'));

        if (!$esEstudianteOEgresado) {
            \Log::info('Dashboard - Usuario es administrador, no se muestran encuestas');
            return Inertia::render('Dashboard', [
                'encuestasAsignadas' => []
            ]);
        }

        // Primero, buscar TODAS las encuestas asignadas a "todos"
        $encuestasTodos = EncuestaAsignada::with(['encuesta'])
            ->where('tipo_asignacion', 'todos')
            ->whereHas('encuesta', function($q) {
                $q->where('estatus', 'A');
            })
            ->get()
            ->map(function($asignacion) {
                return [
                    'id' => $asignacion->id,
                    'encuesta_id' => $asignacion->encuesta_id,
                    'nombre' => $asignacion->encuesta->nombre,
                    'descripcion' => $asignacion->encuesta->descripcion,
                    'fecha_inicio' => $asignacion->encuesta->fecha_inicio,
                    'fecha_fin' => $asignacion->encuesta->fecha_fin,
                    'tipo_asignacion' => $asignacion->tipo_asignacion,
                ];
            });

        $encuestasAsignadas = $encuestasAsignadas->merge($encuestasTodos);
        \Log::info('Dashboard - Encuestas para TODOS: ' . $encuestasTodos->count());

        // Buscar el egresado/estudiante asociado al usuario por email
        $egresado = Egresado::where('email', $user->email)->first();

        \Log::info('Dashboard - Egresado encontrado: ' . ($egresado ? 'Sí (ID: '.$egresado->id.')' : 'No'));

        if ($egresado) {
            // Obtener carreras y generaciones del egresado/estudiante
            $carrerasEgresado = $egresado->carreras()->with(['carrera.unidades', 'generacion'])->get();
            
            // Obtener unidades del usuario a través de sus carreras
            $unidadesIds = collect();
            foreach ($carrerasEgresado as $carreraEgresado) {
                if ($carreraEgresado->carrera && $carreraEgresado->carrera->unidades) {
                    $unidadesIds = $unidadesIds->merge($carreraEgresado->carrera->unidades->pluck('id'));
                }
            }
            $unidadesIds = $unidadesIds->unique()->filter();

            \Log::info('Dashboard - Carreras del egresado: ' . $carrerasEgresado->count());
            \Log::info('Dashboard - Unidades IDs: ' . $unidadesIds->toJson());

            // Buscar encuestas asignadas específicas (no incluimos 'todos' porque ya las agregamos)
            $encuestasEspecificas = EncuestaAsignada::with(['encuesta', 'carrera', 'generacion', 'unidad'])
                ->where(function($query) use ($egresado, $carrerasEgresado, $unidadesIds) {
                    // 1. Encuestas por UNIDAD
                    $query->where(function($q) use ($unidadesIds) {
                        if ($unidadesIds->isNotEmpty()) {
                            $q->where('tipo_asignacion', 'unidad')
                              ->whereIn('unidad_id', $unidadesIds);
                        }
                    });

                    // 2. Encuestas por GENERACIÓN (todas las carreras)
                    foreach ($carrerasEgresado as $carreraEgresado) {
                        if ($carreraEgresado->generacion_id) {
                            $query->orWhere(function($q) use ($carreraEgresado) {
                                $q->where('tipo_asignacion', 'generacion')
                                  ->where('generacion_id', $carreraEgresado->generacion_id);
                            });
                        }
                    }

                    // 3. Encuestas por CARRERA + GENERACIÓN
                    foreach ($carrerasEgresado as $carreraEgresado) {
                        $query->orWhere(function($q) use ($carreraEgresado) {
                            $q->where('tipo_asignacion', 'carrera_generacion')
                              ->where('carrera_id', $carreraEgresado->carrera_id)
                              ->where('generacion_id', $carreraEgresado->generacion_id);
                        });
                    }
                })
                ->whereHas('encuesta', function($q) {
                    $q->where('estatus', 'A');
                })
                ->get()
                ->map(function($asignacion) {
                    return [
                        'id' => $asignacion->id,
                        'encuesta_id' => $asignacion->encuesta_id,
                        'nombre' => $asignacion->encuesta->nombre,
                        'descripcion' => $asignacion->encuesta->descripcion,
                        'fecha_inicio' => $asignacion->encuesta->fecha_inicio,
                        'fecha_fin' => $asignacion->encuesta->fecha_fin,
                        'tipo_asignacion' => $asignacion->tipo_asignacion,
                    ];
                });

            // Combinar encuestas específicas con las de "todos"
            $encuestasAsignadas = $encuestasAsignadas->merge($encuestasEspecificas)->unique('encuesta_id');

            \Log::info('Dashboard - Encuestas específicas: ' . $encuestasEspecificas->count());
            \Log::info('Dashboard - Total encuestas: ' . $encuestasAsignadas->count());
        } else {
            \Log::warning('Dashboard - No se encontró egresado para el email: ' . $user->email . ' - Solo mostrando encuestas "Para Todos"');
        }

        // Agregar información de si ya respondió cada encuesta
        $encuestasConEstado = $encuestasAsignadas->map(function($encuesta) use ($user) {
            $yaRespondio = Respuesta::where('encuesta_id', $encuesta['encuesta_id'])
                ->where('egresado_id', $user->id)
                ->exists();
            
            return array_merge($encuesta, [
                'ya_respondida' => $yaRespondio
            ]);
        });

        return Inertia::render('Dashboard', [
            'encuestasAsignadas' => $encuestasConEstado->values()->all()
        ]);
    }
}
