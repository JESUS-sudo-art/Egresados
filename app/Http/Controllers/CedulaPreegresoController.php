<?php

namespace App\Http\Controllers;

use App\Models\Egresado;
use App\Models\CedulaPreegreso;
use App\Models\CatEstadoCivil;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CedulaPreegresoController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user && ($user->hasRole('Administrador general') || $user->hasRole('Administrador de unidad') || $user->hasRole('Administrador academico'));
        $isEgresado = $user && $user->hasRole('Egresados');
        
        // Si es admin y viene de un perfil específico, cargar ese egresado
        $egresadoId = $request->query('egresado_id');
        if ($isAdmin && $egresadoId) {
            $egresado = Egresado::with(['estadoCivil'])->find($egresadoId);
        } else {
            // Cargar egresado del usuario autenticado
            $egresado = Egresado::with(['estadoCivil'])->where('email', $user->email)->first();
        }
        
        $estadosCiviles = CatEstadoCivil::all();
        
        $cedulaExistente = null;
        if ($egresado) {
            $cedulaExistente = CedulaPreegreso::where('egresado_id', $egresado->id)->first();
        }
        
        // Solo modo lectura si:
        // 1. Es admin viendo la encuesta de otro usuario (viene con egresado_id)
        // 2. Es egresado Y ya tiene cédula contestada
        $soloLectura = ($isAdmin && $egresadoId) || ($isEgresado && $cedulaExistente !== null);

        return Inertia::render('modules/EncuestaPreegreso', [
            'egresado' => $egresado,
            'estadosCiviles' => $estadosCiviles,
            'cedulaExistente' => $cedulaExistente,
            'soloLectura' => $soloLectura,
            'isEgresado' => $isEgresado,
        ]);
    }

    public function store(Request $request)
    {
        // Verificar si el usuario es egresado y ya tiene cédula contestada
        $user = auth()->user();
        $isEgresado = $user && $user->hasRole('Egresados');
        
        // TODO: En producción, obtener el egresado autenticado
        $egresado = Egresado::where('email', $user->email)->first();
        
        if ($isEgresado && $egresado) {
            $cedulaExistente = CedulaPreegreso::where('egresado_id', $egresado->id)->first();
            if ($cedulaExistente) {
                return redirect()->back()->withErrors([
                    'error' => 'Como egresado, no puedes modificar la cédula de pre-egreso. Esta encuesta se contestó cuando eras estudiante.'
                ]);
            }
        }
        
        $validated = $request->validate([
            // Datos personales del egresado
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'required|string|max:100',
            'nombres' => 'required|string|max:150',
            'sexo' => 'required|in:Hombre,Mujer,Otro',
            'curp' => 'nullable|string|max:18',
            'edad' => 'nullable|integer|min:15|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'required|email|max:150',
            
            // Idiomas y lenguas
            'habla_segundo_idioma' => 'required|boolean',
            'cual_idioma' => 'nullable|string|max:100',
            'habla_lengua_indigena' => 'required|boolean',
            'cual_lengua_indigena' => 'nullable|string|max:100',
            'pertenece_grupo_etnico' => 'required|boolean',
            'cual_grupo_etnico' => 'nullable|string|max:100',
            
            // Datos familiares y domicilio
            'estado_civil_id' => 'required|integer',
            'tiene_hijos' => 'required|boolean',
            'es_alumno_foraneo' => 'required|boolean',
            'domicilio_origen' => 'required|string',
            'distrito_origen' => 'nullable|string|max:100',
            'domicilio_actual' => 'required|string',
            
            // Datos académicos
            'anio_ingreso' => 'required|integer|min:1990|max:2030',
            'promedio' => 'required|numeric|min:0|max:100',
            'beneficiado_beca' => 'required|boolean',
            'nombre_beca' => 'nullable|string|max:200',
            'vigencia_beca' => 'nullable|string|max:100',
            'semestres_beca' => 'nullable|array',
            'semestres_beca.*' => 'integer|min:1|max:10',
            
            // Redes sociales
            'facebook' => 'nullable|string|max:255',
        ]);
        
        if (!$egresado) {
            return redirect()->back()->withErrors(['error' => 'No se encontró el egresado']);
        }

        // Mapear sexo a genero_id (1=Masculino, 2=Femenino, 3=No binario)
        $generoId = null;
        if ($validated['sexo'] === 'Hombre') {
            $generoId = 1;
        } elseif ($validated['sexo'] === 'Mujer') {
            $generoId = 2;
        } elseif ($validated['sexo'] === 'Otro') {
            $generoId = 3;
        }

        // Actualizar datos del egresado
        $egresado->update([
            'nombre' => $validated['nombres'],
            'apellidos' => $validated['apellido_paterno'] . ' ' . $validated['apellido_materno'],
            'curp' => $validated['curp'],
            'email' => $validated['email'],
            'genero_id' => $generoId,
            'estado_civil_id' => $validated['estado_civil_id'],
            'tiene_hijos' => $validated['tiene_hijos'],
            'habla_segundo_idioma' => $validated['habla_segundo_idioma'],
            'habla_lengua_indigena' => $validated['habla_lengua_indigena'],
            'pertenece_grupo_etnico' => $validated['pertenece_grupo_etnico'],
            'domicilio' => $validated['domicilio_origen'],
            'domicilio_actual' => $validated['domicilio_actual'],
            'facebook_url' => $validated['facebook'],
        ]);

        // Crear o actualizar cédula de pre-egreso
        $observaciones = [];
        if ($validated['habla_segundo_idioma'] && !empty($validated['cual_idioma'])) {
            $observaciones[] = "Idioma: " . $validated['cual_idioma'];
        }
        if ($validated['habla_lengua_indigena'] && !empty($validated['cual_lengua_indigena'])) {
            $observaciones[] = "Lengua indígena: " . $validated['cual_lengua_indigena'];
        }
        if ($validated['pertenece_grupo_etnico'] && !empty($validated['cual_grupo_etnico'])) {
            $observaciones[] = "Grupo étnico: " . $validated['cual_grupo_etnico'];
        }
        if ($validated['beneficiado_beca']) {
            $becaInfo = "Beca: " . ($validated['nombre_beca'] ?? 'No especificada');
            if (!empty($validated['vigencia_beca'])) {
                $becaInfo .= " (Vigencia: " . $validated['vigencia_beca'] . ")";
            }
            if (!empty($validated['semestres_beca'])) {
                $becaInfo .= " - Semestres: " . implode(', ', $validated['semestres_beca']);
            }
            $observaciones[] = $becaInfo;
        }
        $observaciones[] = "Año de ingreso: " . $validated['anio_ingreso'];
        $observaciones[] = "Alumno foráneo: " . ($validated['es_alumno_foraneo'] ? 'Sí' : 'No');
        if (!empty($validated['distrito_origen'])) {
            $observaciones[] = "Distrito de origen: " . $validated['distrito_origen'];
        }
        $observaciones[] = "Edad: " . ($validated['edad'] ?? 'No especificada');
        $observaciones[] = "Sexo: " . $validated['sexo'];
        
        CedulaPreegreso::updateOrCreate(
            ['egresado_id' => $egresado->id],
            [
                'fecha_aplicacion' => now(),
                'telefono_contacto' => $validated['telefono'],
                'promedio' => $validated['promedio'],
                'observaciones' => implode(" | ", $observaciones),
                'estatus' => 'A',
            ]
        );

        return redirect()->back()->with('success', 'Cédula de Pre-Egreso enviada correctamente. Tus datos de perfil han sido actualizados.');
    }
}
