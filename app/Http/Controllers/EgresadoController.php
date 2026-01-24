<?php

namespace App\Http\Controllers;

use App\Models\Egresado;
use App\Models\Carrera;
use App\Models\Generacion;
use App\Models\CatEstadoCivil;
use App\Models\CatEstatus;
use App\Models\CatGenero;
use App\Models\Encuesta;
use App\Models\CedulaPreegreso;
use App\Models\EncuestaLaboral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EgresadoController extends Controller
{
    public function catalogo(Request $request)
    {
        // Filtros básicos (se pueden ampliar luego)
        $search = trim($request->get('search', ''));
        $estatus = trim($request->get('estatus', ''));

        $query = Egresado::with(['estatus', 'carreras.carrera']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%$search%")
                  ->orWhere('apellidos', 'like', "%$search%")
                  ->orWhere('matricula', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        if ($estatus !== '') {
            $query->whereHas('estatus', fn($q) => $q->where('nombre', $estatus));
        }

        $egresados = $query->limit(500)->get()->map(function ($e) {
            // Obtener encuestas únicas contestadas por el egresado (dinámicas)
            // Incluir tanto respuestas nuevas como antiguas
            $encuestasIds = DB::table('respuesta')
                ->where('egresado_id', $e->id)
                ->distinct()
                ->pluck('encuesta_id');
            
            // Encuestas nuevas
            $encuestas = Encuesta::whereIn('id', $encuestasIds)
                ->get(['id', 'nombre'])
                ->map(fn($enc) => [
                    'id' => $enc->id,
                    'nombre' => $enc->nombre,
                    'tipo' => 'nueva',
                ]);
            
            // Agregar encuestas antiguas de bitacora_encuesta
            $bitacorasAntiguas = DB::table('bitacora_encuesta')
                ->where('egresado_id', $e->id)
                ->distinct()
                ->pluck('encuesta_id');
            
            $encuestasAntiguasData = Encuesta::whereIn('id', $bitacorasAntiguas)
                ->get(['id', 'nombre'])
                ->map(fn($enc) => [
                    'id' => 'antigua_' . $enc->id,
                    'nombre' => $enc->nombre,
                    'tipo' => 'antigua',
                ]);
            
            $encuestas = $encuestas->concat($encuestasAntiguasData);

            // Agregar Cédula de Pre-Egreso si existe
            $cedulaPreegreso = CedulaPreegreso::where('egresado_id', $e->id)
                ->whereNull('deleted_at')
                ->exists();
            
            if ($cedulaPreegreso) {
                $encuestas->push([
                    'id' => 'preegreso',
                    'nombre' => 'Cédula de Pre-Egreso',
                    'tipo' => 'especial',
                ]);
            }

            // Agregar Encuesta Laboral si existe
            $encuestaLaboral = EncuestaLaboral::where('egresado_id', $e->id)
                ->whereNull('deleted_at')
                ->exists();
            
            if ($encuestaLaboral) {
                $encuestas->push([
                    'id' => 'laboral',
                    'nombre' => 'Encuesta Laboral',
                    'tipo' => 'especial',
                ]);
            }

            return [
                'id' => $e->id,
                'matricula' => $e->matricula,
                'nombre' => $e->nombre,
                'apellidos' => $e->apellidos,
                'email' => $e->email,
                'estatus' => $e->estatus?->nombre ?? 'N/D',
                'carreras' => $e->carreras->map(fn($c) => $c->carrera?->nombre)->filter()->values(),
                'encuestas_contestadas' => $encuestas,
                'num_encuestas' => $encuestas->count(),
            ];
        });

        return Inertia::render('modules/CatalogoEgresados', [
            'egresados' => $egresados,
            'filters' => [
                'search' => $search,
                'estatus' => $estatus,
            ],
        ]);
    }

    public function show($id)
    {
        $egresado = Egresado::with([
            'genero',
            'estadoCivil',
            'estatus',
            'unidad',
            'carrera',
            'academicos.unidad',
            'academicos.carrera',
            'academicos.generacion',
            'carreras.carrera.unidades',
            'carreras.generacion',
            'empleos' => function($query) {
                $query->orderBy('fecha_inicio', 'desc');
            },
            'user.roles',
            'bitacoras'
        ])->findOrFail($id);

        // Obtener encuestas contestadas por este egresado
        $encuestasContestadas = DB::table('respuesta')
            ->select('encuesta_id', DB::raw('MIN(creado_en) as fecha_contestada'))
            ->where('egresado_id', $id)
            ->groupBy('encuesta_id')
            ->get()
            ->map(function($r) {
                $encuesta = Encuesta::find($r->encuesta_id);
                return [
                    'id' => $r->encuesta_id,
                    'nombre' => $encuesta?->nombre ?? 'Encuesta eliminada',
                    'fecha_contestada' => $r->fecha_contestada,
                ];
            });

        // Agregar Cédula de Pre-Egreso si existe
        $cedulaPreegreso = CedulaPreegreso::where('egresado_id', $id)
            ->whereNull('deleted_at')
            ->first();
        
        if ($cedulaPreegreso) {
            $encuestasContestadas->push([
                'id' => 'preegreso',
                'nombre' => 'Cédula de Pre-Egreso',
                'fecha_contestada' => $cedulaPreegreso->created_at->format('Y-m-d H:i:s'),
            ]);
        }

        // Agregar Encuesta Laboral si existe
        $encuestaLaboral = EncuestaLaboral::where('egresado_id', $id)
            ->whereNull('deleted_at')
            ->first();
        
        if ($encuestaLaboral) {
            $encuestasContestadas->push([
                'id' => 'laboral',
                'nombre' => 'Encuesta Laboral',
                'fecha_contestada' => $encuestaLaboral->created_at->format('Y-m-d H:i:s'),
            ]);
        }

        // Ordenar por fecha de contestación (más reciente primero)
        $encuestasContestadas = $encuestasContestadas->sortByDesc('fecha_contestada')->values();
        
        // Agregar encuestas antiguas (bitácoras) con nombres reales
        $bitacorasAntiguas = $egresado->bitacoras->map(function($bitacora) {
            $encuesta = Encuesta::find($bitacora->encuesta_id);
            return [
                'id' => 'antigua_' . $bitacora->id,
                'nombre' => $encuesta?->nombre ?? 'Encuesta Antigua (ID: ' . $bitacora->encuesta_id . ')',
                'fecha_contestada' => $bitacora->fecha_inicio,
                'tipo' => 'antigua',
            ];
        });
        
        $encuestasContestadas = $encuestasContestadas->concat($bitacorasAntiguas)->sortByDesc('fecha_contestada')->values();

        // Catálogos para edición
        $catalogos = [
            'carreras' => Carrera::where('estatus', 'A')->get(['id', 'nombre']),
            'generaciones' => Generacion::where('estatus', 'A')->get(['id', 'nombre']),
            'estadosCiviles' => CatEstadoCivil::all(['id', 'nombre']),
            'estatuses' => CatEstatus::all(['id', 'nombre']),
            'generos' => CatGenero::all(['id', 'nombre']),
        ];

        return Inertia::render('modules/PerfilEgresado', [
            'egresado' => [
                'id' => $egresado->id,
                'matricula' => $egresado->matricula,
                'nombre' => $egresado->nombre,
                'apellidos' => $egresado->apellidos,
                'curp' => $egresado->curp,
                'email' => $egresado->email,
                'fecha_nacimiento' => $egresado->fecha_nacimiento?->format('Y-m-d'),
                'lugar_nacimiento' => $egresado->lugar_nacimiento,
                'domicilio' => $egresado->domicilio,
                'domicilio_actual' => $egresado->domicilio_actual,
                'genero_id' => $egresado->genero_id,
                'genero' => $egresado->genero?->nombre,
                'estado_civil_id' => $egresado->estado_civil_id,
                'estado_civil' => $egresado->estadoCivil?->nombre,
                'estatus_id' => $egresado->estatus_id,
                'estatus' => $egresado->estatus?->nombre,
                'unidad_id' => $egresado->unidad_id,
                'unidad' => $egresado->unidad?->nombre,
                'carrera_id' => $egresado->carrera_id,
                'carrera' => $egresado->carrera?->nombre,
                'anio_egreso' => $egresado->anio_egreso,
                'tiene_hijos' => $egresado->tiene_hijos,
                'habla_lengua_indigena' => $egresado->habla_lengua_indigena,
                'habla_segundo_idioma' => $egresado->habla_segundo_idioma,
                'pertenece_grupo_etnico' => $egresado->pertenece_grupo_etnico,
                'facebook_url' => $egresado->facebook_url,
                'tipo_estudiante' => $egresado->tipo_estudiante,
                'validado_sice' => $egresado->validado_sice,
                'carreras' => $egresado->carreras->map(function($ec) {
                    return [
                        'id' => $ec->id,
                        'carrera_id' => $ec->carrera_id,
                        'carrera_nombre' => $ec->carrera?->nombre,
                        'generacion_id' => $ec->generacion_id,
                        'generacion_nombre' => $ec->generacion?->nombre,
                        'fecha_ingreso' => $ec->fecha_ingreso,
                        'fecha_egreso' => $ec->fecha_egreso,
                        'tipo_egreso' => $ec->tipo_egreso,
                    ];
                }),
                'academicos' => $egresado->academicos->map(function($ac) {
                    return [
                        'id' => $ac->id,
                        'unidad_id' => $ac->unidad_id,
                        'unidad_nombre' => $ac->unidad?->nombre,
                        'carrera_id' => $ac->carrera_id,
                        'carrera_nombre' => $ac->carrera?->nombre,
                        'generacion_id' => $ac->generacion_id,
                        'generacion_nombre' => $ac->generacion?->nombre,
                    ];
                }),
                'empleos' => $egresado->empleos->map(function($empleo) {
                    return [
                        'id' => $empleo->id,
                        'puesto' => $empleo->puesto,
                        'empresa' => $empleo->empresa,
                        'fecha_inicio' => $empleo->fecha_inicio,
                        'fecha_fin' => $empleo->fecha_fin,
                        'sueldo' => $empleo->sueldo,
                        'ciudad' => $empleo->ciudad,
                    ];
                }),
                'tiene_usuario' => $egresado->user !== null,
                'roles_usuario' => $egresado->user?->roles->pluck('name')->toArray() ?? [],
                'encuestas_contestadas' => $encuestasContestadas,
            ],
            'catalogos' => $catalogos,
        ]);
    }

    public function update(Request $request, $id)
    {
        $egresado = Egresado::findOrFail($id);

        $validated = $request->validate([
            'matricula' => 'nullable|string|max:50',
            'nombre' => 'required|string|max:150',
            'apellidos' => 'required|string|max:150',
            'curp' => 'nullable|string|max:18',
            'email' => 'required|email|max:150',
            'fecha_nacimiento' => 'nullable|date',
            'lugar_nacimiento' => 'nullable|string|max:255',
            'domicilio' => 'nullable|string',
            'domicilio_actual' => 'nullable|string',
            'genero_id' => 'nullable|integer|exists:cat_genero,id',
            'estado_civil_id' => 'nullable|integer|exists:cat_estado_civil,id',
            'estatus_id' => 'required|integer|exists:cat_estatus,id',
            'tiene_hijos' => 'nullable|boolean',
            'habla_lengua_indigena' => 'nullable|boolean',
            'habla_segundo_idioma' => 'nullable|boolean',
            'pertenece_grupo_etnico' => 'nullable|boolean',
            'facebook_url' => 'nullable|string|max:255',
            'tipo_estudiante' => 'nullable|string|max:50',
        ]);

        // Asegurar valores booleanos por defecto si no vienen en el request
        $validated['tiene_hijos'] = $validated['tiene_hijos'] ?? false;
        $validated['habla_lengua_indigena'] = $validated['habla_lengua_indigena'] ?? false;
        $validated['habla_segundo_idioma'] = $validated['habla_segundo_idioma'] ?? false;
        $validated['pertenece_grupo_etnico'] = $validated['pertenece_grupo_etnico'] ?? false;

        $egresado->update($validated);

        return redirect()->back()->with('success', 'Perfil actualizado correctamente');
    }

    public function updatePassword(Request $request, $id)
    {
        $egresado = Egresado::with('user')->findOrFail($id);
        
        if (!$egresado->user) {
            return redirect()->back()->withErrors(['error' => 'Este egresado no tiene usuario en el sistema']);
        }

        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $egresado->user->update([
            'password' => bcrypt($validated['password']),
        ]);

        return redirect()->back()->with('success', 'Contraseña actualizada correctamente');
    }

    public function updateCarrera(Request $request, $id)
    {
        $validated = $request->validate([
            'carrera_id' => 'required|integer|exists:carrera,id',
            'generacion_id' => 'required|integer|exists:generacion,id',
            'fecha_ingreso' => 'nullable|date',
            'fecha_egreso' => 'nullable|date',
        ]);

        // Verificar si ya existe esta combinación
        $existe = DB::table('egresado_carrera')
            ->where('egresado_id', $id)
            ->where('carrera_id', $validated['carrera_id'])
            ->where('generacion_id', $validated['generacion_id'])
            ->exists();

        if ($existe) {
            return redirect()->back()->withErrors(['error' => 'Esta carrera y generación ya está asignada']);
        }

        DB::table('egresado_carrera')->insert([
            'egresado_id' => $id,
            'carrera_id' => $validated['carrera_id'],
            'generacion_id' => $validated['generacion_id'],
            'fecha_ingreso' => $validated['fecha_ingreso'],
            'fecha_egreso' => $validated['fecha_egreso'],
        ]);

        return redirect()->back()->with('success', 'Carrera agregada correctamente');
    }

    public function deleteCarrera($egresadoId, $carreraId)
    {
        DB::table('egresado_carrera')
            ->where('id', $carreraId)
            ->where('egresado_id', $egresadoId)
            ->delete();

        return redirect()->back()->with('success', 'Carrera eliminada correctamente');
    }
}
