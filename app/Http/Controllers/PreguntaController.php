<?php

namespace App\Http\Controllers;

use App\Models\Pregunta;
use App\Models\Opcion;
use App\Models\TipoPregunta;
use App\Models\Dimension;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PreguntaController extends Controller
{
    use AuthorizesRequests;

    /**
     * Listar preguntas de una encuesta
     */
    public function index($encuestaId)
    {
        $preguntas = Pregunta::with(['opciones', 'tipo'])
            ->where('encuesta_id', $encuestaId)
            ->orderBy('orden')
            ->get();
        return response()->json($preguntas);
    }

    /**
     * Crear una nueva pregunta
     */
    public function store(Request $request, $encuestaId)
    {
        $validated = $request->validate([
            'texto' => 'required|string',
            'tipo' => 'required|string|in:Abierta,Opción Múltiple,Casillas de Verificación,Escala Likert,Sí/No,Numérica,Fecha',
            'orden' => 'nullable|integer|min:0',
            'dimension_id' => 'nullable|integer|exists:dimension,id',
        ]);

        // Validar que la dimensión (si viene) pertenezca a la misma encuesta
        if (!empty($validated['dimension_id'])) {
            $belongs = Dimension::where('id', $validated['dimension_id'])
                ->where('encuesta_id', $encuestaId)
                ->exists();
            if (!$belongs) {
                return response()->json(['error' => 'La dimensión no pertenece a la encuesta'], 422);
            }
        }

        $tipo = TipoPregunta::firstOrCreate(
            ['descripcion' => $validated['tipo']], 
            ['estatus' => 'A']
        );
        
        $pregunta = Pregunta::create([
            'encuesta_id' => $encuestaId,
            'texto' => $validated['texto'],
            'tipo_pregunta_id' => $tipo->id,
            'orden' => $validated['orden'] ?? 0,
            'dimension_id' => $validated['dimension_id'] ?? null,
        ]);

        if ($request->expectsJson()) {
            return response()->json($pregunta->load(['opciones', 'tipo']), 201);
        }
        
        return back()->with('success', 'Pregunta creada');
    }

    /**
     * Actualizar una pregunta
     */
    public function update(Request $request, $id)
    {
        $pregunta = Pregunta::findOrFail($id);
        
        $validated = $request->validate([
            'texto' => 'required|string',
            'tipo' => 'required|string|in:Abierta,Opción Múltiple,Casillas de Verificación,Escala Likert,Sí/No,Numérica,Fecha',
            'orden' => 'nullable|integer|min:0',
            'dimension_id' => 'nullable|integer|exists:dimension,id',
        ]);

        // Validar que la dimensión pertenezca a la encuesta de la pregunta
        if (!empty($validated['dimension_id'])) {
            $belongs = Dimension::where('id', $validated['dimension_id'])
                ->where('encuesta_id', $pregunta->encuesta_id)
                ->exists();
            if (!$belongs) {
                return response()->json(['error' => 'La dimensión no pertenece a la encuesta de la pregunta'], 422);
            }
        }

        $tipo = TipoPregunta::firstOrCreate(
            ['descripcion' => $validated['tipo']], 
            ['estatus' => 'A']
        );
        
        $pregunta->update([
            'texto' => $validated['texto'],
            'tipo_pregunta_id' => $tipo->id,
            'orden' => $validated['orden'] ?? 0,
            'dimension_id' => $validated['dimension_id'] ?? $pregunta->dimension_id,
        ]);

        if ($request->expectsJson()) {
            return response()->json($pregunta->load(['opciones', 'tipo']));
        }
        
        return back()->with('success', 'Pregunta actualizada');
    }

    /**
     * Eliminar una pregunta
     */
    public function destroy($id)
    {
        $pregunta = Pregunta::findOrFail($id);
        $pregunta->delete();
        
        return response()->json(['ok' => true]);
    }

    // ===== OPCIONES =====

    /**
     * Crear una opción para una pregunta
     */
    public function storeOpcion(Request $request, $preguntaId)
    {
        $validated = $request->validate([
            'texto' => 'nullable|string',
            'valor' => 'nullable|integer',
            'orden' => 'nullable|integer|min:0',
        ]);

        Opcion::create([
            'pregunta_id' => $preguntaId,
            'texto' => $validated['texto'] ?? 'Nueva opción',
            'valor' => $validated['valor'] ?? null,
            'orden' => $validated['orden'] ?? 0,
        ]);

        return back()->with('success', 'Opción agregada');
    }

    /**
     * Actualizar una opción
     */
    public function updateOpcion(Request $request, $id)
    {
        $opcion = Opcion::findOrFail($id);
        
        $validated = $request->validate([
            'texto' => 'nullable|string',
            'valor' => 'nullable|integer',
            'orden' => 'nullable|integer|min:0',
        ]);

        // Si el texto está vacío, usar un valor por defecto
        if (isset($validated['texto'])) {
            $validated['texto'] = $validated['texto'] ?: 'Opción sin texto';
        }

        $opcion->update($validated);
        
        return response()->json($opcion);
    }

    /**
     * Eliminar una opción
     */
    public function destroyOpcion($id)
    {
        $opcion = Opcion::findOrFail($id);
        $opcion->delete();
        
        return back()->with('success', 'Opción eliminada');
    }
}
