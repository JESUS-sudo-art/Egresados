<?php

namespace App\Http\Controllers;

use App\Models\Unidad;
use App\Models\Carrera;
use App\Models\Generacion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminAcademicaController extends Controller
{
    public function index()
    {
        $unidades = Unidad::all();
        $carreras = Carrera::all();
        $generaciones = Generacion::all();

        return Inertia::render('modules/AdminAcademica', [
            'unidades' => $unidades,
            'carreras' => $carreras,
            'generaciones' => $generaciones,
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
}
