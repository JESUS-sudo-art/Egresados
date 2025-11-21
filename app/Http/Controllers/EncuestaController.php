<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use App\Models\Pregunta;
use App\Models\Respuesta;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EncuestaController extends Controller
{
    public function show($encuestaId)
    {
        $user = Auth::user();
        
        $encuesta = Encuesta::with(['preguntas' => function($query) {
            $query->with(['opciones', 'tipo'])->orderBy('orden');
        }])->findOrFail($encuestaId);

        // Verificar que la encuesta esté activa
        if ($encuesta->estatus !== 'A') {
            return redirect()->route('dashboard')->with('error', 'Esta encuesta no está disponible');
        }

        // Verificar si el usuario ya respondió esta encuesta
        $yaRespondio = Respuesta::where('encuesta_id', $encuestaId)
            ->where('egresado_id', $user->id)
            ->exists();

        if ($yaRespondio) {
            return redirect()->route('encuesta.respuestas', $encuestaId)
                ->with('info', 'Ya has respondido esta encuesta. Aquí están tus respuestas.');
        }

        // Log para debug
        \Log::info('Cargando encuesta para estudiante', [
            'encuesta_id' => $encuesta->id,
            'preguntas_count' => $encuesta->preguntas->count(),
            'primera_pregunta_opciones' => $encuesta->preguntas->first() ? $encuesta->preguntas->first()->opciones->toArray() : []
        ]);

        return Inertia::render('modules/ContestarEncuesta', [
            'encuesta' => [
                'id' => $encuesta->id,
                'nombre' => $encuesta->nombre,
                'descripcion' => $encuesta->descripcion,
                'preguntas' => $encuesta->preguntas->map(function($pregunta) {
                    return [
                        'id' => $pregunta->id,
                        'texto' => $pregunta->texto,
                        'tipo' => $pregunta->tipo->descripcion,
                        'orden' => $pregunta->orden,
                        'opciones' => $pregunta->opciones->map(function($opcion) {
                            return [
                                'id' => $opcion->id,
                                'texto' => $opcion->texto,
                                'valor' => $opcion->valor,
                            ];
                        }),
                    ];
                }),
            ],
        ]);
    }

    public function store(Request $request, $encuestaId)
    {
        $validated = $request->validate([
            'respuestas' => 'required|array',
            'respuestas.*.pregunta_id' => 'required|integer|exists:pregunta,id',
            'respuestas.*.respuesta' => 'nullable',
            'respuestas.*.opciones_seleccionadas' => 'nullable|array',
        ]);

        $user = Auth::user();

        // Log para debug
        \Log::info('Respuestas de encuesta recibidas', [
            'user_id' => $user->id,
            'encuesta_id' => $encuestaId,
            'respuestas' => $validated['respuestas']
        ]);

        // Verificar que no haya respondido ya
        $yaRespondio = Respuesta::where('encuesta_id', $encuestaId)
            ->where('egresado_id', $user->id)
            ->exists();

        if ($yaRespondio) {
            return redirect()->route('dashboard')
                ->with('error', 'Ya has respondido esta encuesta anteriormente.');
        }

        // Guardar respuestas en transacción
        DB::beginTransaction();
        try {
            foreach ($validated['respuestas'] as $respuesta) {
                $preguntaId = $respuesta['pregunta_id'];
                
                // Si es una pregunta con opciones seleccionadas (checkbox, radio, likert, si/no)
                if (isset($respuesta['opciones_seleccionadas']) && is_array($respuesta['opciones_seleccionadas']) && !empty($respuesta['opciones_seleccionadas'])) {
                    foreach ($respuesta['opciones_seleccionadas'] as $opcionId) {
                        Respuesta::create([
                            'egresado_id' => $user->id,
                            'encuesta_id' => $encuestaId,
                            'pregunta_id' => $preguntaId,
                            'opcion_id' => $opcionId,
                            'respuesta_texto' => null,
                            'respuesta_entero' => null,
                        ]);
                    }
                }
                // Si es respuesta numérica
                elseif (isset($respuesta['respuesta']) && is_numeric($respuesta['respuesta']) && $respuesta['respuesta'] !== null && $respuesta['respuesta'] !== '') {
                    Respuesta::create([
                        'egresado_id' => $user->id,
                        'encuesta_id' => $encuestaId,
                        'pregunta_id' => $preguntaId,
                        'opcion_id' => null,
                        'respuesta_texto' => null,
                        'respuesta_entero' => (int)$respuesta['respuesta'],
                    ]);
                }
                // Si es respuesta de texto abierto
                elseif (isset($respuesta['respuesta']) && $respuesta['respuesta'] !== null && $respuesta['respuesta'] !== '') {
                    Respuesta::create([
                        'egresado_id' => $user->id,
                        'encuesta_id' => $encuestaId,
                        'pregunta_id' => $preguntaId,
                        'opcion_id' => null,
                        'respuesta_texto' => $respuesta['respuesta'],
                        'respuesta_entero' => null,
                    ]);
                }
                // Si no hay respuesta, lo saltamos (pregunta sin responder)
            }
            
            DB::commit();
            
            return redirect()->route('dashboard')
                ->with('success', '¡Encuesta completada exitosamente!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error guardando respuestas de encuesta', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'encuesta_id' => $encuestaId,
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'Hubo un error al guardar tus respuestas. Por favor intenta de nuevo.');
        }
    }

    public function misRespuestas($encuestaId)
    {
        $user = Auth::user();
        
        $encuesta = Encuesta::with(['preguntas' => function($query) {
            $query->with(['opciones', 'tipo'])->orderBy('orden');
        }])->findOrFail($encuestaId);

        // Obtener las respuestas del usuario para esta encuesta
        $respuestas = Respuesta::with(['pregunta', 'opcion'])
            ->where('encuesta_id', $encuestaId)
            ->where('egresado_id', $user->id)
            ->get();

        // Organizar respuestas por pregunta
        $respuestasPorPregunta = $respuestas->groupBy('pregunta_id');

        return Inertia::render('modules/VerRespuestas', [
            'encuesta' => [
                'id' => $encuesta->id,
                'nombre' => $encuesta->nombre,
                'descripcion' => $encuesta->descripcion,
                'preguntas' => $encuesta->preguntas->map(function($pregunta) use ($respuestasPorPregunta) {
                    $respuestasPregunta = $respuestasPorPregunta->get($pregunta->id, collect());
                    
                    return [
                        'id' => $pregunta->id,
                        'texto' => $pregunta->texto,
                        'tipo' => $pregunta->tipo->descripcion,
                        'orden' => $pregunta->orden,
                        'opciones' => $pregunta->opciones->map(function($opcion) {
                            return [
                                'id' => $opcion->id,
                                'texto' => $opcion->texto,
                                'valor' => $opcion->valor,
                            ];
                        }),
                        'respuestas' => $respuestasPregunta->map(function($resp) {
                            return [
                                'opcion_id' => $resp->opcion_id,
                                'opcion_texto' => $resp->opcion ? $resp->opcion->texto : null,
                                'respuesta_texto' => $resp->respuesta_texto,
                                'respuesta_entero' => $resp->respuesta_entero,
                            ];
                        })->values()->all(),
                    ];
                }),
            ],
        ]);
    }
}
