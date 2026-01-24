<?php

namespace App\Http\Controllers;

use App\Models\Egresado;
use App\Models\CatGenero;
use App\Models\CatEstadoCivil;
use App\Models\CatEstatus;
use App\Models\Laboral;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Database\QueryException;
use PDOException;
use Illuminate\Support\Facades\DB;

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

        // Formatear fecha de nacimiento para inputs type="date"
        if ($egresado->fecha_nacimiento) {
            $egresado->fecha_nacimiento = $egresado->fecha_nacimiento->format('Y-m-d');
        }

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
        \Log::info('updateDatosPersonales: inicio', ['request' => $request->all()]);
        
        try {
            $validated = $request->validate([
                'id' => 'required|integer',
                'matricula' => 'nullable|string|max:50',
                'nombre' => 'required|string|max:150',
                'apellidos' => 'required|string|max:200',
                'curp' => 'nullable|string|max:18',
                'email' => 'required|email|max:150',
                'telefono' => 'nullable|string|max:20',
                'domicilio' => 'nullable|string|max:500',
                'domicilio_actual' => 'nullable|string|max:500',
                'facebook_url' => 'nullable|string|max:255',
                'fecha_nacimiento' => 'nullable|date',
                'estado_origen' => 'nullable|string|max:100',
                'genero_id' => 'nullable|integer',
                'estado_civil_id' => 'nullable|integer',
                'tiene_hijos' => 'nullable|boolean',
                'habla_lengua_indigena' => 'nullable|boolean',
                'habla_segundo_idioma' => 'nullable|boolean',
                'pertenece_grupo_etnico' => 'nullable|boolean',
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
            
            $validated['estatus_id'] = $estatusId;

            // Normalizar fecha de nacimiento a formato Y-m-d o dejarla en null
            if (!empty($validated['fecha_nacimiento'])) {
                $validated['fecha_nacimiento'] = Carbon::parse($validated['fecha_nacimiento'])->toDateString();
            } else {
                $validated['fecha_nacimiento'] = null;
            }

            // SOLUCIÓN: Usar raw SQL para evitar prepared statements problemáticos
            // Construir los campos a actualizar
            $updateFields = [];
            $params = [];
            
            foreach ($validated as $field => $value) {
                if ($field !== 'id') {
                    $updateFields[] = "`{$field}` = ?";
                    $params[] = $value;
                }
            }
            
            // Agregar timestamp usando función de MySQL
            $updateFields[] = "`updated_at` = NOW()";
            $params[] = $validated['id'];
            
            // Ejecutar query raw
            $sql = "UPDATE `egresado` SET " . implode(', ', $updateFields) . " WHERE `id` = ?";
            DB::statement($sql, $params);
            
            \Log::info('Datos guardados correctamente con raw SQL');

            // Sincronizar datos con cedula_preegreso (crear si no existe)
            try {
                $syncUpdates = [];
                $syncParams = [];
                $edadCalculada = null;

                // Sincronizar teléfono si viene en la solicitud
                if (array_key_exists('telefono', $validated) && !is_null($validated['telefono']) && $validated['telefono'] !== '') {
                    $syncUpdates[] = "`telefono_contacto` = ?";
                    $syncParams[] = $validated['telefono'];
                }

                // Calcular edad desde fecha_nacimiento y sincronizar
                if (!empty($validated['fecha_nacimiento'])) {
                    try {
                        $edad = Carbon::parse($validated['fecha_nacimiento'])->age;
                        if ($edad >= 10 && $edad <= 100) {
                            $syncUpdates[] = "`edad` = ?";
                            $syncParams[] = $edad;
                            $edadCalculada = $edad;
                        }
                    } catch (\Exception $exEdad) {
                        // Ignorar errores de parseo
                        \Log::warning('No se pudo calcular edad para sincronización', ['error' => $exEdad->getMessage()]);
                    }
                } else {
                    // Si la fecha de nacimiento se envió vacía, limpiar edad en encuesta
                    $syncUpdates[] = "`edad` = NULL";
                }

                // Verificar si ya existe cédula
                $cedula = DB::select('SELECT `id`, `observaciones` FROM `cedula_preegreso` WHERE `egresado_id` = ? LIMIT 1', [$validated['id']]);
                if (empty($cedula)) {
                    // Crear cédula inicial
                    $observaciones = [];
                    // Insertar la edad en observaciones si está calculada
                    $observaciones[] = 'Edad: ' . ($edadCalculada ?? 'No especificada');
                    $observacionesStr = implode(' | ', $observaciones);

                    $sqlInsert = "INSERT INTO `cedula_preegreso` (`egresado_id`, `fecha_aplicacion`, `telefono_contacto`, `promedio`, `observaciones`, `estatus`, `created_at`, `updated_at`) VALUES (?, NOW(), ?, NULL, ?, 'A', NOW(), NOW())";
                    DB::insert($sqlInsert, [
                        $validated['id'],
                        $validated['telefono'] ?? null,
                        $observacionesStr,
                    ]);
                    \Log::info('Cédula pre-egreso creada por sincronización', ['egresado_id' => $validated['id']]);
                } else {
                    // Actualizar campos y mantener observaciones consistentes
                    if (!empty($syncUpdates)) {
                        $syncParams[] = $validated['id'];
                        $sqlSync = "UPDATE `cedula_preegreso` SET " . implode(', ', $syncUpdates) . ", `updated_at` = NOW() WHERE `egresado_id` = ?";
                        DB::update($sqlSync, $syncParams);
                    }

                    // Actualizar texto en observaciones: reemplazar o agregar 'Edad: X'
                    $obs = $cedula[0]->observaciones ?? '';
                    if ($edadCalculada !== null) {
                        if ($obs && strpos($obs, 'Edad:') !== false) {
                            // Reemplazar la edad existente
                            $nuevoObs = preg_replace('/Edad: [^|]*/', 'Edad: ' . $edadCalculada, $obs);
                        } else {
                            // Agregar al final
                            $nuevoObs = trim($obs) !== '' ? ($obs . ' | Edad: ' . $edadCalculada) : ('Edad: ' . $edadCalculada);
                        }
                        DB::update('UPDATE `cedula_preegreso` SET `observaciones` = ?, `updated_at` = NOW() WHERE `egresado_id` = ?', [$nuevoObs, $validated['id']]);
                    }
                    \Log::info('Sincronización con cedula_preegreso realizada', ['egresado_id' => $validated['id'], 'updates' => $syncUpdates]);
                }
            } catch (\Exception $exSync) {
                \Log::error('Error sincronizando con cedula_preegreso', ['error' => $exSync->getMessage(), 'egresado_id' => $validated['id']]);
            }

            return back()->with('success', 'Datos personales actualizados correctamente');
        } catch (\Exception $e) {
            \Log::error('Error en updateDatosPersonales:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withErrors(['error' => 'Error al guardar los datos: ' . $e->getMessage()]);
        }
    }

    public function storeEmpleo(Request $request)
    {
        try {
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

            // Usar raw SQL para insert
            $validated['created_at'] = now();
            $validated['updated_at'] = now();
            
            $fields = implode(', ', array_map(fn($f) => "`$f`", array_keys($validated)));
            $placeholders = implode(', ', array_fill(0, count($validated), '?'));
            $sql = "INSERT INTO `laboral` ($fields) VALUES ($placeholders)";
            
            DB::insert($sql, array_values($validated));
            
            \Log::info('Empleo agregado correctamente');

            return back()->with('success', 'Empleo agregado correctamente');
        } catch (\Exception $e) {
            \Log::error('Error en storeEmpleo:', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Error al guardar el empleo: ' . $e->getMessage()]);
        }
    }

    public function updateEmpleo(Request $request, $id)
    {
        try {
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
            
            // Usar raw SQL para update
            $validated['updated_at'] = now();
            
            $updateFields = [];
            $params = [];
            
            foreach ($validated as $field => $value) {
                $updateFields[] = "`{$field}` = ?";
                $params[] = $value;
            }
            
            $params[] = $id;
            
            $sql = "UPDATE `laboral` SET " . implode(', ', $updateFields) . " WHERE `id` = ?";
            DB::update($sql, $params);
            
            \Log::info('Empleo actualizado correctamente');

            return back()->with('success', 'Empleo actualizado correctamente');
        } catch (\Exception $e) {
            \Log::error('Error en updateEmpleo:', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Error al actualizar el empleo: ' . $e->getMessage()]);
        }
    }

    public function deleteEmpleo($id)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                abort(403, 'Usuario no autenticado');
            }

            // Verificar que el empleo pertenece a un egresado del usuario autenticado
            $empleo = Laboral::whereHas('egresado', function($query) use ($user) {
                $query->where('email', $user->email);
            })->findOrFail($id);
            
            // Usar raw SQL para delete
            DB::delete('DELETE FROM `laboral` WHERE `id` = ?', [$id]);
            
            \Log::info('Empleo eliminado correctamente:', ['id' => $id]);

            return back()->with('success', 'Empleo eliminado correctamente');
        } catch (\Exception $e) {
            \Log::error('Error en deleteEmpleo:', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Error al eliminar el empleo: ' . $e->getMessage()]);
        }
    }
}
