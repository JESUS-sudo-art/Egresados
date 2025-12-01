<?php

namespace App\Http\Controllers;

use App\Models\Egresado;
use App\Models\CatGenero;
use App\Models\CatEstadoCivil;
use App\Models\CatEstatus;
use App\Models\Laboral;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PerfilController extends Controller
{
    public function index()
    {
        // Obtener el egresado asociado al email del usuario autenticado
        $user = auth()->user();
        
        if (!$user) {
            abort(403, 'Usuario no autenticado');
        }
        
        $egresado = Egresado::with(['genero', 'estadoCivil', 'estatus', 'carreras.carrera', 'carreras.generacion', 'unidad', 'carrera'])
            ->where('email', $user->email)
            ->first();

        // Si no existe un egresado, crear uno básico con los datos del usuario
        if (!$egresado) {
            // Determinar el estatus según el rol del usuario
            $estatusId = 1; // Por defecto
            if ($user->hasRole('Estudiantes')) {
                $estatusId = CatEstatus::where('nombre', 'Estudiante')->value('id') ?? 1;
            } elseif ($user->hasRole('Egresados')) {
                $estatusId = CatEstatus::where('nombre', 'Egresado')->value('id') ?? 2;
            }
            
            $egresado = Egresado::create([
                'email' => $user->email,
                'nombre' => $user->name ?? '',
                'apellidos' => '',
                'estatus_id' => $estatusId,
            ]);
            
            // Recargar con relaciones
            $egresado = Egresado::with(['genero', 'estadoCivil', 'estatus', 'carreras.carrera', 'carreras.generacion', 'unidad', 'carrera'])
                ->find($egresado->id);
        } else {
            // Actualizar el estatus si no está configurado correctamente
            $estatusId = null;
            if ($user->hasRole('Estudiantes')) {
                $estatusId = CatEstatus::where('nombre', 'Estudiante')->value('id');
            } elseif ($user->hasRole('Egresados')) {
                $estatusId = CatEstatus::where('nombre', 'Egresado')->value('id');
            }
            
            if ($estatusId && $egresado->estatus_id !== $estatusId) {
                $egresado->update(['estatus_id' => $estatusId]);
                $egresado->refresh();
            }
        }

        $generos = CatGenero::all();
        $estadosCiviles = CatEstadoCivil::all();
        $estatuses = CatEstatus::all();
        
        $empleos = Laboral::where('egresado_id', $egresado->id)
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        return Inertia::render('modules/PerfilDatos', [
            'egresado' => $egresado,
            'generos' => $generos,
            'estadosCiviles' => $estadosCiviles,
            'estatuses' => $estatuses,
            'empleos' => $empleos,
        ]);
    }

    public function updateDatosPersonales(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer',
            'matricula' => 'nullable|string|max:50',
            'nombre' => 'required|string|max:150',
            'apellidos' => 'required|string|max:200',
            'curp' => 'nullable|string|max:18',
            'email' => 'required|email|max:150',
            'domicilio' => 'nullable|string|max:500',
            'fecha_nacimiento' => 'nullable|date',
            'estado_origen' => 'nullable|string|max:100',
            'genero_id' => 'nullable|integer',
            'estado_civil_id' => 'nullable|integer',
        ]);

        $user = auth()->user();
        if (!$user) {
            abort(403, 'Usuario no autenticado');
        }

        // Verificar que el egresado a actualizar pertenece al usuario autenticado
        $egresado = Egresado::where('id', $validated['id'])
            ->where('email', $user->email)
            ->firstOrFail();
        
        // Determinar el estatus según el rol del usuario
        $estatusId = null;
        if ($user->hasRole('Estudiantes')) {
            $estatusId = CatEstatus::where('nombre', 'Estudiante')->value('id') ?? 1;
        } elseif ($user->hasRole('Egresados')) {
            $estatusId = CatEstatus::where('nombre', 'Egresado')->value('id') ?? 2;
        }
        
        // Agregar el estatus calculado
        $validated['estatus_id'] = $estatusId;
        
        $egresado->update($validated);

        return redirect()->back()->with('success', 'Datos personales actualizados correctamente');
    }

    public function storeEmpleo(Request $request)
    {
        $validated = $request->validate([
            'egresado_id' => 'required|integer',
            'empresa' => 'required|string|max:255',
            'puesto' => 'nullable|string|max:255',
            'sector' => 'nullable|string|max:100',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'actualmente_activo' => 'nullable|boolean',
        ]);

        $user = auth()->user();
        if (!$user) {
            abort(403, 'Usuario no autenticado');
        }

        // Verificar que el egresado_id pertenece al usuario autenticado
        $egresado = Egresado::where('id', $validated['egresado_id'])
            ->where('email', $user->email)
            ->firstOrFail();

        Laboral::create($validated);

        return redirect()->back()->with('success', 'Empleo agregado correctamente');
    }

    public function updateEmpleo(Request $request, $id)
    {
        $validated = $request->validate([
            'empresa' => 'required|string|max:255',
            'puesto' => 'nullable|string|max:255',
            'sector' => 'nullable|string|max:100',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'actualmente_activo' => 'nullable|boolean',
        ]);

        $user = auth()->user();
        if (!$user) {
            abort(403, 'Usuario no autenticado');
        }

        // Verificar que el empleo pertenece a un egresado del usuario autenticado
        $empleo = Laboral::whereHas('egresado', function($query) use ($user) {
            $query->where('email', $user->email);
        })->findOrFail($id);
        
        $empleo->update($validated);

        return redirect()->back()->with('success', 'Empleo actualizado correctamente');
    }

    public function deleteEmpleo($id)
    {
        $user = auth()->user();
        if (!$user) {
            abort(403, 'Usuario no autenticado');
        }

        // Verificar que el empleo pertenece a un egresado del usuario autenticado
        $empleo = Laboral::whereHas('egresado', function($query) use ($user) {
            $query->where('email', $user->email);
        })->findOrFail($id);
        
        $empleo->delete();

        return redirect()->back()->with('success', 'Empleo eliminado correctamente');
    }
}
