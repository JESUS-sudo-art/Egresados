<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use App\Models\Pregunta;
use App\Models\Opcion;
use App\Models\EncuestaAsignada;
use App\Models\Carrera;
use App\Models\Generacion;
use App\Models\Unidad;
use App\Models\TipoPregunta;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminUnidadController extends Controller
{
    public function index()
    {
        $encuestas = Encuesta::orderByDesc('id')->get(['id','nombre','descripcion','fecha_inicio','fecha_fin','estatus']);
        $carreras = Carrera::orderBy('nombre')->get(['id','nombre']);
        $generaciones = Generacion::orderBy('nombre')->get(['id','nombre']);
        $unidades = Unidad::orderBy('nombre')->get(['id','nombre']);

        $asignaciones = EncuestaAsignada::with(['encuesta:id,nombre,fecha_inicio,fecha_fin','carrera:id,nombre','generacion:id,nombre','unidad:id,nombre'])
            ->orderByDesc('id')
            ->get();

        $tipos = TipoPregunta::where('estatus','A')->orderBy('id')->get(['id','descripcion']);

        return Inertia::render('modules/AdminUnidad', [
            'encuestas' => $encuestas,
            'carreras' => $carreras,
            'generaciones' => $generaciones,
            'unidades' => $unidades,
            'asignaciones' => $asignaciones,
            'tiposPregunta' => $tipos,
        ]);
    }

    // ===== ENCUESTAS =====
    public function storeEncuesta(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        Encuesta::create($validated + ['estatus' => 'A']);
        return back()->with('success', 'Encuesta creada');
    }

    public function updateEncuesta(Request $request, $id)
    {
        $encuesta = Encuesta::findOrFail($id);
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);
        $encuesta->update($validated);
        return back()->with('success', 'Encuesta actualizada');
    }

    public function destroyEncuesta($id)
    {
        $encuesta = Encuesta::findOrFail($id);

        // El error de FOREIGN KEY al eliminar la encuesta ocurre porque existen
        // registros dependientes (preguntas, opciones, asignaciones) que apuntan
        // a la encuesta y la base de datos (SQLite) no tiene definidas las
        // claves foráneas con onDelete('cascade'). Aquí eliminamos manualmente
        // todas las dependencias antes de eliminar la encuesta.

        // Eliminar asignaciones relacionadas
        EncuestaAsignada::where('encuesta_id', $encuesta->id)->delete();

        // Cargar preguntas con sus opciones y eliminarlas en cascada manual
        $encuesta->load(['preguntas.opciones']);
        foreach ($encuesta->preguntas as $pregunta) {
            // Eliminar opciones de la pregunta
            Opcion::where('pregunta_id', $pregunta->id)->delete();
            // Eliminar la pregunta
            $pregunta->delete();
        }

        // Finalmente eliminar la encuesta
        $encuesta->delete();
        return back()->with('success', 'Encuesta eliminada');
    }

    // ===== PREGUNTAS =====
    public function listPreguntas($encuestaId)
    {
        $preguntas = Pregunta::with(['opciones', 'tipo'])
            ->where('encuesta_id', $encuestaId)
            ->orderBy('orden')
            ->get();
        return response()->json($preguntas);
    }

    public function storePregunta(Request $request, $encuestaId)
    {
        $validated = $request->validate([
            'texto' => 'required|string',
            'tipo' => 'required|string|in:Abierta,Opción Múltiple,Casillas de Verificación,Escala Likert,Sí/No,Numérica,Fecha',
            'orden' => 'nullable|integer|min:0',
        ]);

        $tipo = TipoPregunta::firstOrCreate(['descripcion' => $validated['tipo']], ['estatus' => 'A']);
        $pregunta = Pregunta::create([
            'encuesta_id' => $encuestaId,
            'texto' => $validated['texto'],
            'tipo_pregunta_id' => $tipo->id,
            'orden' => $validated['orden'] ?? 0,
        ]);
        // Si la petición espera JSON (ej. llamada manual con XHR) devolver JSON
        if ($request->expectsJson()) {
            return response()->json($pregunta->load(['opciones','tipo']), 201);
        }
        // Inertia (formularios con router.post) espera un redirect/back
        return back()->with('success', 'Pregunta creada');
    }

    public function updatePregunta(Request $request, $id)
    {
        $pregunta = Pregunta::findOrFail($id);
        $validated = $request->validate([
            'texto' => 'required|string',
            'tipo' => 'required|string|in:Abierta,Opción Múltiple,Casillas de Verificación,Escala Likert,Sí/No,Numérica,Fecha',
            'orden' => 'nullable|integer|min:0',
        ]);
        $tipo = TipoPregunta::firstOrCreate(['descripcion' => $validated['tipo']], ['estatus' => 'A']);
        $pregunta->update([
            'texto' => $validated['texto'],
            'tipo_pregunta_id' => $tipo->id,
            'orden' => $validated['orden'] ?? 0,
        ]);
        if ($request->expectsJson()) {
            return response()->json($pregunta->load(['opciones','tipo']));
        }
        return back()->with('success', 'Pregunta actualizada');
    }

    public function destroyPregunta($id)
    {
        $pregunta = Pregunta::findOrFail($id);
        $pregunta->delete();
        return response()->json(['ok' => true]);
    }

    // ===== OPCIONES =====
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

    public function destroyOpcion($id)
    {
        $opcion = Opcion::findOrFail($id);
        $opcion->delete();
        return back()->with('success', 'Opción eliminada');
    }

    // ===== ASIGNACIONES =====
    public function storeAsignacion(Request $request)
    {
        $validated = $request->validate([
            'encuesta_id' => 'required|integer|exists:encuesta,id',
            'tipo_asignacion' => 'required|string|in:todos,unidad,generacion,carrera_generacion',
            'unidad_id' => 'nullable|integer|exists:unidad,id',
            'carrera_id' => 'nullable|integer|exists:carrera,id',
            'generacion_id' => 'nullable|integer|exists:generacion,id',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        // Validar que se proporcionen los campos necesarios según el tipo
        if ($validated['tipo_asignacion'] === 'unidad' && !$validated['unidad_id']) {
            return back()->withErrors(['unidad_id' => 'Debe seleccionar una unidad']);
        }
        if ($validated['tipo_asignacion'] === 'generacion' && !$validated['generacion_id']) {
            return back()->withErrors(['generacion_id' => 'Debe seleccionar una generación']);
        }
        if ($validated['tipo_asignacion'] === 'carrera_generacion' && (!$validated['carrera_id'] || !$validated['generacion_id'])) {
            return back()->withErrors(['carrera_id' => 'Debe seleccionar carrera y generación']);
        }

        // Guardamos fechas en la encuesta (alcance global)
        $encuesta = Encuesta::findOrFail($validated['encuesta_id']);
        $encuesta->update([
            'fecha_inicio' => $validated['fecha_inicio'] ?? $encuesta->fecha_inicio,
            'fecha_fin' => $validated['fecha_fin'] ?? $encuesta->fecha_fin,
        ]);

        // Crear asignación según el tipo
        $data = [
            'encuesta_id' => $validated['encuesta_id'],
            'tipo_asignacion' => $validated['tipo_asignacion'],
        ];

        if ($validated['tipo_asignacion'] === 'unidad') {
            $data['unidad_id'] = $validated['unidad_id'];
        } elseif ($validated['tipo_asignacion'] === 'generacion') {
            $data['generacion_id'] = $validated['generacion_id'];
        } elseif ($validated['tipo_asignacion'] === 'carrera_generacion') {
            $data['carrera_id'] = $validated['carrera_id'];
            $data['generacion_id'] = $validated['generacion_id'];
        }
        // Para 'todos' no se necesita agregar campos adicionales

        EncuestaAsignada::create($data);

        return back()->with('success', 'Encuesta asignada');
    }

    public function destroyAsignacion($id)
    {
        $asig = EncuestaAsignada::findOrFail($id);
        $asig->delete();
        return back()->with('success', 'Asignación eliminada');
    }
}
