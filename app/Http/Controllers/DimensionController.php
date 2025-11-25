<?php

namespace App\Http\Controllers;

use App\Models\Dimension;
use App\Models\Encuesta;
use App\Models\Pregunta;
use Illuminate\Http\Request;

class DimensionController extends Controller
{
    // Lista dimensiones de una encuesta
    public function index($encuestaId)
    {
        $encuesta = Encuesta::findOrFail($encuestaId);
        $dimensiones = Dimension::where('encuesta_id', $encuesta->id)
            ->orderBy('orden')
            ->withCount('preguntas')
            ->get();
        return response()->json([
            'encuesta' => [
                'id' => $encuesta->id,
                'nombre' => $encuesta->nombre,
            ],
            'dimensiones' => $dimensiones,
        ]);
    }

    // Crear dimensión en una encuesta
    public function store(Request $request, $encuestaId)
    {
        $encuesta = Encuesta::findOrFail($encuestaId);
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'orden' => 'nullable|integer|min:0',
        ]);
        $dimension = Dimension::create([
            'encuesta_id' => $encuesta->id,
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'orden' => $validated['orden'] ?? 0,
        ]);
        return response()->json($dimension, 201);
    }

    // Actualizar una dimensión
    public function update(Request $request, $id)
    {
        $dimension = Dimension::findOrFail($id);
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'orden' => 'nullable|integer|min:0',
        ]);
        $dimension->update([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'orden' => $validated['orden'] ?? $dimension->orden,
        ]);
        return response()->json($dimension);
    }

    // Eliminar dimensión con estrategia de reasignación / null
    public function destroy(Request $request, $id)
    {
        $dimension = Dimension::findOrFail($id);
        $reassignTo = $request->query('reassign_to'); // puede venir como query param

        // Reasignar o dejar en null las preguntas
        if ($reassignTo) {
            if ($reassignTo === 'null') {
                Pregunta::where('dimension_id', $dimension->id)->update(['dimension_id' => null]);
            } else {
                $target = Dimension::findOrFail($reassignTo);
                if ($target->id === $dimension->id) {
                    return response()->json(['error' => 'reassign_to no puede ser la misma dimensión'], 422);
                }
                if ($target->encuesta_id !== $dimension->encuesta_id) {
                    return response()->json(['error' => 'La dimensión destino pertenece a otra encuesta'], 422);
                }
                Pregunta::where('dimension_id', $dimension->id)->update(['dimension_id' => $target->id]);
            }
        } else {
            // Por omisión: dejar preguntas sin dimensión
            Pregunta::where('dimension_id', $dimension->id)->update(['dimension_id' => null]);
        }

        $dimension->delete();
        return response()->json(['ok' => true]);
    }
}
