<?php

namespace App\Http\Controllers;

use App\Models\BitacoraEncuesta;
use App\Models\RespuestaInt;
use App\Models\RespuestaTxt;
use App\Models\Egresado;
use App\Models\Encuesta;
use App\Models\Respuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RespuestasAntiguasController extends Controller
{
    /**
     * Muestra el listado de encuestas antiguas Y nuevas contestadas por el egresado
     */
    public function index()
    {
        $user = Auth::user();
        
        // Buscar el egresado asociado al usuario
        $egresado = Egresado::where('email', $user->email)->first();
        
        if (!$egresado) {
            return redirect()->route('dashboard')
                ->with('error', 'No se encontró tu perfil de egresado');
        }

        // Obtener bitácoras de encuestas ANTIGUAS (migradas)
        $bitacorasAntiguas = BitacoraEncuesta::with(['encuesta', 'ciclo'])
            ->where('egresado_id', $egresado->id)
            ->orderBy('id', 'desc')
            ->get();

        // Procesar bitácoras antiguas con información de respuestas
        $bitacorasFormateadas = collect();
        
        foreach ($bitacorasAntiguas as $bitacora) {
            try {
                // Contar respuestas de cada tipo
                $totalInt = RespuestaInt::where('bitacora_encuesta_id', $bitacora->id)->count();
                $totalTxt = RespuestaTxt::where('bitacora_encuesta_id', $bitacora->id)->count();
                
                $bitacorasFormateadas->push([
                    'id' => $bitacora->id,
                    'tipo' => 'antigua',
                    'encuesta' => [
                        'id' => $bitacora->encuesta_id,
                        'nombre' => $bitacora->encuesta?->nombre ?? 'Encuesta eliminada',
                    ],
                    'ciclo' => [
                        'id' => $bitacora->ciclo_id,
                        'nombre' => $bitacora->ciclo?->nombre ?? 'Ciclo desconocido',
                    ],
                    'fecha_inicio' => $bitacora->fecha_inicio?->format('d/m/Y H:i') ?? 'N/A',
                    'fecha_fin' => $bitacora->fecha_fin?->format('d/m/Y H:i') ?? 'N/A',
                    'completada' => $bitacora->completada ?? false,
                    'total_respuestas' => $totalInt + $totalTxt,
                    'respuestas_numericas' => $totalInt,
                    'respuestas_texto' => $totalTxt,
                ]);
            } catch (\Exception $e) {
                \Log::error('Error procesando bitácora ' . $bitacora->id . ': ' . $e->getMessage());
            }
        }

        // Obtener encuestas NUEVAS (sistema actual) contestadas por el egresado
        $encuestasNuevas = Respuesta::select('encuesta_id', \DB::raw('MIN(created_at) as fecha_inicio'), \DB::raw('MAX(updated_at) as fecha_fin'), \DB::raw('COUNT(*) as total'))
            ->where('egresado_id', $egresado->id)
            ->groupBy('encuesta_id')
            ->with('encuesta')
            ->get();

        foreach ($encuestasNuevas as $respuesta) {
            $bitacorasFormateadas->push([
                'id' => 'nueva_' . $respuesta->encuesta_id,
                'tipo' => 'nueva',
                'encuesta' => [
                    'id' => $respuesta->encuesta_id,
                    'nombre' => $respuesta->encuesta?->nombre ?? 'Encuesta',
                ],
                'ciclo' => [
                    'id' => null,
                    'nombre' => 'Actual',
                ],
                'fecha_inicio' => $respuesta->fecha_inicio ? date('d/m/Y H:i', strtotime($respuesta->fecha_inicio)) : 'N/A',
                'fecha_fin' => $respuesta->fecha_fin ? date('d/m/Y H:i', strtotime($respuesta->fecha_fin)) : 'N/A',
                'completada' => true,
                'total_respuestas' => $respuesta->total,
                'respuestas_numericas' => $respuesta->total,
                'respuestas_texto' => 0,
            ]);
        }

        return Inertia::render('modules/RespuestasAntiguas', [
            'bitacoras' => $bitacorasFormateadas->sortByDesc('fecha_inicio')->values()->toArray(),
            'egresado' => [
                'id' => $egresado->id,
                'nombre' => $egresado->nombre,
                'apellidos' => $egresado->apellidos,
            ],
        ]);
    }

    /**
     * Muestra el detalle de respuestas antiguas o nuevas de una encuesta específica
     */
    public function show($bitacoraId)
    {
        $user = Auth::user();
        
        // Verificar si es una respuesta nueva (formato: nueva_X) o antigua (número)
        if (str_starts_with($bitacoraId, 'nueva_')) {
            // Es una encuesta nueva del sistema actual
            $encuestaId = (int) str_replace('nueva_', '', $bitacoraId);
            
            // Buscar el egresado asociado al usuario
            $egresado = Egresado::where('email', $user->email)->first();
            
            if (!$egresado) {
                return redirect()->route('dashboard')
                    ->with('error', 'No se encontró tu perfil de egresado');
            }
            
            return $this->mostrarRespuestasNuevas($egresado, $encuestaId);
        }

        // Es una bitácora antigua migrada - obtener el egresado de la bitácora
        $bitacora = BitacoraEncuesta::with('egresado')->findOrFail($bitacoraId);
        
        // Verificar permisos: el usuario debe ser el egresado O tener rol de administrador
        $esPropio = $bitacora->egresado->email === $user->email;
        $esAdmin = $user->hasAnyRole(['Administrador general', 'Administrador unidad', 'Administrador académico']);
        
        if (!$esPropio && !$esAdmin) {
            abort(403, 'No tienes permiso para ver estas respuestas');
        }
        
        return $this->mostrarRespuestasAntiguas($bitacora->egresado, $bitacoraId);
    }

    /**
     * Muestra respuestas del sistema actual
     */
    private function mostrarRespuestasNuevas($egresado, $encuestaId)
    {
        // Obtener la encuesta
        $encuesta = Encuesta::findOrFail($encuestaId);
        
        // Obtener todas las respuestas del egresado para esta encuesta
        $respuestas = Respuesta::with(['pregunta.dimension', 'opcion'])
            ->where('egresado_id', $egresado->id)
            ->where('encuesta_id', $encuestaId)
            ->get();

        if ($respuestas->isEmpty()) {
            return redirect()->route('respuestas-antiguas.index')
                ->with('error', 'No se encontraron respuestas para esta encuesta');
        }

        // Agrupar respuestas por pregunta
        $respuestasPorPregunta = collect();
        
        foreach ($respuestas as $resp) {
            if (!$respuestasPorPregunta->has($resp->pregunta_id)) {
                $respuestasPorPregunta->put($resp->pregunta_id, [
                    'pregunta_id' => $resp->pregunta_id,
                    'pregunta_texto' => $resp->pregunta?->texto ?? 'Pregunta eliminada',
                    'tipo' => $resp->pregunta?->tipo_pregunta ?? 'N/A',
                    'dimension' => $resp->pregunta?->dimension?->nombre ?? 'Sin dimensión',
                    'dimension_orden' => $resp->pregunta?->dimension?->orden ?? 9999,
                    'respuestas' => [],
                ]);
            }
            
            $pregunta = $respuestasPorPregunta->get($resp->pregunta_id);
            
            // Determinar el valor de la respuesta
            $valor = null;
            if ($resp->opcion_id) {
                $valor = $resp->opcion?->texto ?? "Opción {$resp->opcion_id}";
            } elseif ($resp->respuesta_texto) {
                $valor = $resp->respuesta_texto;
            } elseif ($resp->respuesta_entero !== null) {
                $valor = $resp->respuesta_entero;
            }
            
            $pregunta['respuestas'][] = [
                'tipo' => $resp->respuesta_texto ? 'texto' : 'numerico',
                'valor' => $valor,
            ];
            
            $respuestasPorPregunta->put($resp->pregunta_id, $pregunta);
        }

        $respuestasAgrupadas = $respuestasPorPregunta->sortBy('dimension_orden')->values()->toArray();

        return Inertia::render('modules/RespuestasAntiguasShow', [
            'bitacora' => [
                'id' => 'nueva_' . $encuestaId,
                'encuesta' => [
                    'id' => $encuesta->id,
                    'nombre' => $encuesta->nombre,
                ],
                'ciclo' => [
                    'nombre' => 'Actual',
                ],
                'fecha_inicio' => $respuestas->min('created_at')?->format('d/m/Y H:i:s') ?? 'N/A',
                'fecha_fin' => $respuestas->max('updated_at')?->format('d/m/Y H:i:s') ?? 'N/A',
                'completada' => true,
            ],
            'respuestas' => $respuestasAgrupadas,
            'egresado' => [
                'nombre' => $egresado->nombre,
                'apellidos' => $egresado->apellidos,
            ],
        ]);
    }

    /**
     * Muestra respuestas antiguas migradas
     */
    private function mostrarRespuestasAntiguas($egresado, $bitacoraId)
    {
        // Buscar la bitácora y verificar que pertenezca al egresado
        $bitacora = BitacoraEncuesta::with(['encuesta.dimensiones', 'ciclo'])
            ->where('id', $bitacoraId)
            ->where('egresado_id', $egresado->id)
            ->firstOrFail();

        // Obtener todas las respuestas numéricas con sus preguntas
        $respuestasInt = RespuestaInt::with(['pregunta.dimension', 'pregunta.tipo'])
            ->where('bitacora_encuesta_id', $bitacoraId)
            ->get();

        // Obtener todas las respuestas de texto con sus preguntas
        $respuestasTxt = RespuestaTxt::with(['pregunta.dimension', 'pregunta.tipo'])
            ->where('bitacora_encuesta_id', $bitacoraId)
            ->get();

        // Agrupar respuestas por pregunta_id
        $respuestasPorPregunta = collect();

        // Agregar respuestas numéricas
        foreach ($respuestasInt as $resp) {
            if (!$respuestasPorPregunta->has($resp->pregunta_id)) {
                $respuestasPorPregunta->put($resp->pregunta_id, [
                    'pregunta_id' => $resp->pregunta_id,
                    'pregunta_texto' => $resp->pregunta ? $resp->pregunta->texto : 'Pregunta eliminada',
                    'tipo' => $resp->pregunta && $resp->pregunta->tipo ? $resp->pregunta->tipo->descripcion : 'N/A',
                    'dimension' => $resp->pregunta && $resp->pregunta->dimension ? $resp->pregunta->dimension->nombre : 'Sin dimensión',
                    'dimension_orden' => $resp->pregunta && $resp->pregunta->dimension ? $resp->pregunta->dimension->orden : 9999,
                    'respuestas' => [],
                ]);
            }
            
            $pregunta = $respuestasPorPregunta->get($resp->pregunta_id);
            
            // Intentar buscar la opción por VALOR primero, luego por ID
            $valor = $resp->respuesta;
            if (is_numeric($valor) && $resp->pregunta) {
                // Primero intentar buscar por valor
                $opcion = \App\Models\Opcion::where('pregunta_id', $resp->pregunta_id)
                    ->where('valor', $valor)
                    ->first();
                    
                // Si no encuentra por valor, buscar por ID (para opciones con IDs antiguos)
                if (!$opcion) {
                    $opcion = \App\Models\Opcion::where('pregunta_id', $resp->pregunta_id)
                        ->where('id', $valor)
                        ->first();
                }
                    
                if ($opcion) {
                    $valor = $opcion->texto;
                }
            }
            
            $pregunta['respuestas'][] = [
                'tipo' => 'numerico',
                'valor' => $valor,
            ];
            $respuestasPorPregunta->put($resp->pregunta_id, $pregunta);
        }

        // Agregar respuestas de texto
        foreach ($respuestasTxt as $resp) {
            if (!$respuestasPorPregunta->has($resp->pregunta_id)) {
                $respuestasPorPregunta->put($resp->pregunta_id, [
                    'pregunta_id' => $resp->pregunta_id,
                    'pregunta_texto' => $resp->pregunta ? $resp->pregunta->texto : 'Pregunta eliminada',
                    'tipo' => $resp->pregunta && $resp->pregunta->tipo ? $resp->pregunta->tipo->descripcion : 'N/A',
                    'dimension' => $resp->pregunta && $resp->pregunta->dimension ? $resp->pregunta->dimension->nombre : 'Sin dimensión',
                    'dimension_orden' => $resp->pregunta && $resp->pregunta->dimension ? $resp->pregunta->dimension->orden : 9999,
                    'respuestas' => [],
                ]);
            }
            
            $pregunta = $respuestasPorPregunta->get($resp->pregunta_id);
            $pregunta['respuestas'][] = [
                'tipo' => 'texto',
                'valor' => $resp->respuesta,
            ];
            $respuestasPorPregunta->put($resp->pregunta_id, $pregunta);
        }

        // Ordenar por dimensión y convertir a array de valores
        $respuestasAgrupadas = $respuestasPorPregunta->sortBy('dimension_orden')->values()->toArray();

        return Inertia::render('modules/RespuestasAntiguasShow', [
            'bitacora' => [
                'id' => $bitacora->id,
                'encuesta' => [
                    'id' => $bitacora->encuesta_id,
                    'nombre' => $bitacora->encuesta ? $bitacora->encuesta->nombre : 'Encuesta eliminada',
                ],
                'ciclo' => [
                    'nombre' => $bitacora->ciclo ? $bitacora->ciclo->nombre : 'N/A',
                ],
                'fecha_inicio' => $bitacora->fecha_inicio?->format('d/m/Y H:i:s'),
                'fecha_fin' => $bitacora->fecha_fin?->format('d/m/Y H:i:s'),
                'completada' => $bitacora->completada,
            ],
            'respuestas' => $respuestasAgrupadas,
            'egresado' => [
                'nombre' => $egresado->nombre,
                'apellidos' => $egresado->apellidos,
            ],
        ]);
    }

    /**
     * Muestra estadísticas generales de respuestas antiguas (solo admin)
     */
    public function estadisticas()
    {
        // Verificar que sea administrador
        if (!Auth::user()->hasRole('Administrador general')) {
            abort(403, 'No autorizado');
        }

        $stats = [
            'total_bitacoras' => BitacoraEncuesta::count(),
            'bitacoras_completas' => BitacoraEncuesta::where('completada', true)->count(),
            'total_respuestas_int' => RespuestaInt::count(),
            'total_respuestas_txt' => RespuestaTxt::count(),
            'egresados_con_respuestas' => BitacoraEncuesta::distinct('egresado_id')->count(),
            'encuestas_con_respuestas' => BitacoraEncuesta::distinct('encuesta_id')->count(),
        ];

        // Top 5 encuestas con más respuestas
        $topEncuestas = BitacoraEncuesta::with('encuesta')
            ->select('encuesta_id', \DB::raw('COUNT(*) as total'))
            ->groupBy('encuesta_id')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'encuesta' => $item->encuesta ? $item->encuesta->nombre : 'Encuesta eliminada',
                    'total_respuestas' => $item->total,
                ];
            });

        return Inertia::render('modules/RespuestasAntiguas/Estadisticas', [
            'stats' => $stats,
            'topEncuestas' => $topEncuestas,
        ]);
    }
}
