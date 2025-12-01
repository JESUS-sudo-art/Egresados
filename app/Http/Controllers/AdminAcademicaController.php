<?php

namespace App\Http\Controllers;

use App\Models\Unidad;
use App\Models\Carrera;
use App\Models\Generacion;
use App\Models\NivelEstudio;
use App\Models\CicloEscolar;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminAcademicaController extends Controller
{
    public function index()
    {
        \Log::info('AdminAcademica@index: entrando');
        $unidades = Unidad::all();
        \Log::info('AdminAcademica@index: unidades='.count($unidades));
        $carreras = Carrera::all();
        \Log::info('AdminAcademica@index: carreras='.count($carreras));
        $generaciones = Generacion::all();
        \Log::info('AdminAcademica@index: generaciones='.count($generaciones));
        $nivelesEstudio = NivelEstudio::all();
        \Log::info('AdminAcademica@index: niveles='.count($nivelesEstudio));
        $ciclosEscolares = CicloEscolar::all();
        \Log::info('AdminAcademica@index: ciclos='.count($ciclosEscolares));

        \Log::info('AdminAcademica@index: render inertia');
        return Inertia::render('modules/AdminAcademica', [
            'unidades' => $unidades,
            'carreras' => $carreras,
            'generaciones' => $generaciones,
            'nivelesEstudio' => $nivelesEstudio,
            'ciclosEscolares' => $ciclosEscolares,
        ]);
    }

    // ===== UNIDADES =====
    
    public function storeUnidad(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'clave' => 'nullable|string|max:50',
            'domicilio' => 'nullable|string',
            'web' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:150',
            'estatus' => 'required|in:A,I',
        ]);

        Unidad::create($validated);

        return redirect()->back()->with('success', 'Unidad creada correctamente.');
    }

    public function updateUnidad(Request $request, $id)
    {
        $unidad = Unidad::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'clave' => 'nullable|string|max:50',
            'domicilio' => 'nullable|string',
            'web' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:150',
            'estatus' => 'required|in:A,I',
        ]);

        $unidad->update($validated);

        return redirect()->back()->with('success', 'Unidad actualizada correctamente.');
    }

    public function destroyUnidad($id)
    {
        $unidad = Unidad::findOrFail($id);
        $unidad->delete();

        return redirect()->back()->with('success', 'Unidad eliminada correctamente.');
    }

    public function asignarCarreras(Request $request, $id)
    {
        $unidad = Unidad::findOrFail($id);

        $validated = $request->validate([
            'carreras_ids' => 'nullable|array',
            'carreras_ids.*' => 'exists:carrera,id',
        ]);

        // Sincronizar carreras con la unidad (many-to-many)
        if (isset($validated['carreras_ids'])) {
            $unidad->carreras()->sync($validated['carreras_ids']);
        } else {
            $unidad->carreras()->detach();
        }

        return redirect()->back()->with('success', 'Carreras asignadas correctamente.');
    }

    // ===== CARRERAS =====
    
    public function storeCarrera(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'nivel' => 'nullable|string|max:100',
            'tipo_programa' => 'nullable|string|max:100',
            'estatus' => 'required|in:A,I',
        ]);

        Carrera::create($validated);

        return redirect()->back()->with('success', 'Carrera creada correctamente.');
    }

    public function updateCarrera(Request $request, $id)
    {
        $carrera = Carrera::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'nivel' => 'nullable|string|max:100',
            'tipo_programa' => 'nullable|string|max:100',
            'estatus' => 'required|in:A,I',
        ]);

        $carrera->update($validated);

        return redirect()->back()->with('success', 'Carrera actualizada correctamente.');
    }

    public function destroyCarrera($id)
    {
        $carrera = Carrera::findOrFail($id);
        $carrera->delete();

        return redirect()->back()->with('success', 'Carrera eliminada correctamente.');
    }

    // ===== GENERACIONES =====
    
    public function storeGeneracion(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'estatus' => 'required|in:A,I',
        ]);

        Generacion::create($validated);

        return redirect()->back()->with('success', 'Generación creada correctamente.');
    }

    public function updateGeneracion(Request $request, $id)
    {
        $generacion = Generacion::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'estatus' => 'required|in:A,I',
        ]);

        $generacion->update($validated);

        return redirect()->back()->with('success', 'Generación actualizada correctamente.');
    }

    public function destroyGeneracion($id)
    {
        $generacion = Generacion::findOrFail($id);
        $generacion->delete();

        return redirect()->back()->with('success', 'Generación eliminada correctamente.');
    }

    // ===== NIVELES DE ESTUDIO =====
    public function storeNivel(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'estatus' => 'required|in:A,I',
        ]);
        NivelEstudio::create($validated);
        return redirect()->back()->with('success', 'Nivel de estudio creado correctamente.');
    }

    public function updateNivel(Request $request, $id)
    {
        $nivel = NivelEstudio::findOrFail($id);
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'estatus' => 'required|in:A,I',
        ]);
        $nivel->update($validated);
        return redirect()->back()->with('success', 'Nivel de estudio actualizado correctamente.');
    }

    public function destroyNivel($id)
    {
        $nivel = NivelEstudio::findOrFail($id);
        $nivel->delete();
        return redirect()->back()->with('success', 'Nivel de estudio eliminado correctamente.');
    }

    // ===== CICLOS ESCOLARES =====
    public function storeCiclo(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'estatus' => 'required|in:A,I',
        ]);
        CicloEscolar::create($validated);
        return redirect()->back()->with('success', 'Ciclo escolar creado correctamente.');
    }

    public function updateCiclo(Request $request, $id)
    {
        $ciclo = CicloEscolar::findOrFail($id);
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'estatus' => 'required|in:A,I',
        ]);
        $ciclo->update($validated);
        return redirect()->back()->with('success', 'Ciclo escolar actualizado correctamente.');
    }

    public function destroyCiclo($id)
    {
        $ciclo = CicloEscolar::findOrFail($id);
        $ciclo->delete();
        return redirect()->back()->with('success', 'Ciclo escolar eliminado correctamente.');
    }
}
