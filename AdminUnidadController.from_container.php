<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use App\Models\EncuestaAsignada;
use App\Models\Carrera;
use App\Models\Generacion;
use App\Models\Unidad;
use App\Models\TipoPregunta;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;


class AdminUnidadController extends Controller 
{
    //use AuthorizesRequests;
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }
    
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
        $this->authorize('create', Encuesta::class);
        
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        Encuesta::create($validated + ['estatus' => 'A']);
        return back()->with('success', 'Encuesta creada');
    }

    public function updateEncuesta(Request $request, Encuesta $encuesta)
    {
        $encuesta = Encuesta::findOrFail($encuesta);
        $this->authorize('update', $encuesta);
        
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
        $this->authorize('delete', $encuesta);

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
