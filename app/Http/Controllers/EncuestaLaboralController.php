<?php

namespace App\Http\Controllers;

use App\Models\Egresado;
use App\Models\EncuestaLaboral;
use App\Models\CatEstadoCivil;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EncuestaLaboralController extends Controller
{
    public function index()
    {
        // TODO: En producción, obtener el egresado autenticado
        $egresado = Egresado::with(['estadoCivil', 'carreras.carrera'])->where('email', auth()->user()->email)->first();
        $estadosCiviles = CatEstadoCivil::all();
        
        $encuestaExistente = null;
        if ($egresado) {
            $encuestaExistente = EncuestaLaboral::where('egresado_id', $egresado->id)
                ->orderBy('fecha_aplicacion', 'desc')
                ->first();
        }

        return Inertia::render('modules/EncuestaLaboral', [
            'egresado' => $egresado,
            'estadosCiviles' => $estadosCiviles,
            'encuestaExistente' => $encuestaExistente,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Sección I: Datos Generales
            'nombre_completo' => 'required|string|max:255',
            'genero' => 'required|in:Mujer,Hombre,No binario,Prefiero no decirlo,Otro',
            'edad' => 'required|integer|min:18|max:100',
            'curp' => 'nullable|string|max:18',
            'telefono' => 'required|string|max:20',
            'email' => 'required|email|max:150',
            'estado_civil_id' => 'required|integer',
            'residencia_actual' => 'required|string|max:255',
            'pertenece_grupo_etnico' => 'required|boolean',
            'cual_grupo_etnico' => 'nullable|string|max:100',
            'habla_lengua_originaria' => 'required|boolean',
            'cual_lengua_originaria' => 'nullable|string|max:100',
            'comunidad_diversa' => 'nullable|string|max:255',
            'tiene_hijos' => 'nullable|boolean',
            'num_hijos' => 'nullable|integer|min:0',
            'dependientes_economicos' => 'nullable|integer|min:0',
            
            // Sección II: Trayectoria Académica
            'programa_academico' => 'required|string|max:255',
            'fecha_ingreso' => 'required|date',
            'fecha_egreso' => 'required|date',
            'realizo_practicas' => 'required|boolean',
            'descripcion_practicas' => 'nullable|string',
            'tiene_titulo' => 'required|boolean',
            'fecha_titulacion' => 'nullable|date',
            'estudios_posgrado' => 'required|boolean',
            'nivel_posgrado' => 'nullable|string|max:100',
            'area_posgrado' => 'nullable|string|max:255',
            'institucion_posgrado' => 'nullable|string|max:255',
            'status_posgrado' => 'nullable|string|max:100',
            'participo_movilidad' => 'required|boolean',
            'tipo_movilidad' => 'nullable|string|max:100',
            'pais_movilidad' => 'nullable|string|max:255',
            'duracion_movilidad' => 'nullable|string|max:100',
            
            // Sección III: Inserción Laboral
            'trabaja_actualmente' => 'required|boolean',
            'motivo_no_trabaja' => 'nullable|string|max:255',
            'tiempo_primer_empleo' => 'nullable|string|max:100',
            'rango_salario' => 'nullable|string|max:100',
            'relacion_carrera' => 'nullable|boolean',
            'tipo_contrato' => 'nullable|string|max:100',
            'jornada_laboral' => 'nullable|string|max:100',
            'medio_obtencion_empleo' => 'nullable|string|max:255',
            'cambios_empleo' => 'nullable|integer',
            'satisfaccion_laboral' => 'nullable|string|max:50',
            
            // Sección IV: Datos del Empleador
            'nombre_empresa' => 'nullable|string|max:255',
            'sector_empresa' => 'nullable|in:Público,Privado,Social,Otro',
            'giro_empresa' => 'nullable|string|max:255',
            'ubicacion_empresa' => 'nullable|string|max:255',
            'puesto_actual' => 'nullable|string|max:255',
            'area_departamento' => 'nullable|string|max:255',
            'jefe_inmediato' => 'nullable|string|max:255',
            'contacto_jefe' => 'nullable|string|max:255',
            
            // Sección V: Evaluación de la Formación
            'promueve_pensamiento_critico' => 'required|in:Sí,No,Parcialmente',
            'aspectos_valorados' => 'nullable|string',
            'sugerencias_plan_estudios' => 'nullable|string',
            'competencias_faltantes' => 'nullable|string',
            'calificacion_formacion' => 'required|integer|min:1|max:10',
            'recomendaria_institucion' => 'required|boolean',
            'razon_recomendacion' => 'nullable|string',
            'participacion_vinculacion' => 'required|boolean',
            'tipo_vinculacion' => 'nullable|string|max:255',
            'comentarios_adicionales' => 'nullable|string',
        ]);

        // TODO: En producción, obtener el egresado autenticado
        $egresado = Egresado::where('email', auth()->user()->email)->first();
        
        if (!$egresado) {
            return redirect()->back()->withErrors(['error' => 'No se encontró el egresado']);
        }

        // Crear encuesta laboral
        EncuestaLaboral::create([
            'egresado_id' => $egresado->id,
            'fecha_aplicacion' => now(),
            
            // Sección I
            'nombre_completo' => $validated['nombre_completo'],
            'genero' => $validated['genero'],
            'edad' => $validated['edad'],
            'curp' => $validated['curp'],
            'telefono' => $validated['telefono'],
            'email' => $validated['email'],
            'estado_civil_id' => $validated['estado_civil_id'],
            'residencia_actual' => $validated['residencia_actual'],
            'pertenece_grupo_etnico' => $validated['pertenece_grupo_etnico'],
            'cual_grupo_etnico' => $validated['cual_grupo_etnico'],
            'habla_lengua_originaria' => $validated['habla_lengua_originaria'],
            'cual_lengua_originaria' => $validated['cual_lengua_originaria'],
            'comunidad_diversa' => $validated['comunidad_diversa'],
            'tiene_hijos' => $validated['tiene_hijos'] ?? false,
            'num_hijos' => $validated['num_hijos'],
            'dependientes_economicos' => $validated['dependientes_economicos'],
            
            // Sección II
            'programa_academico' => $validated['programa_academico'],
            'fecha_ingreso' => $validated['fecha_ingreso'],
            'fecha_egreso' => $validated['fecha_egreso'],
            'realizo_practicas' => $validated['realizo_practicas'],
            'descripcion_practicas' => $validated['descripcion_practicas'],
            'tiene_titulo' => $validated['tiene_titulo'],
            'fecha_titulacion' => $validated['fecha_titulacion'],
            'estudios_posgrado' => $validated['estudios_posgrado'],
            'nivel_posgrado' => $validated['nivel_posgrado'],
            'area_posgrado' => $validated['area_posgrado'],
            'institucion_posgrado' => $validated['institucion_posgrado'],
            'status_posgrado' => $validated['status_posgrado'],
            'participo_movilidad' => $validated['participo_movilidad'],
            'tipo_movilidad' => $validated['tipo_movilidad'],
            'pais_movilidad' => $validated['pais_movilidad'],
            'duracion_movilidad' => $validated['duracion_movilidad'],
            
            // Sección III
            'trabaja_actualmente' => $validated['trabaja_actualmente'],
            'motivo_no_trabaja' => $validated['motivo_no_trabaja'],
            'tiempo_primer_empleo' => $validated['tiempo_primer_empleo'],
            'rango_salario' => $validated['rango_salario'],
            'relacion_carrera' => $validated['relacion_carrera'],
            'tipo_contrato' => $validated['tipo_contrato'],
            'jornada_laboral' => $validated['jornada_laboral'],
            'medio_obtencion_empleo' => $validated['medio_obtencion_empleo'],
            'cambios_empleo' => $validated['cambios_empleo'],
            'satisfaccion_laboral' => $validated['satisfaccion_laboral'],
            
            // Sección IV
            'nombre_empresa' => $validated['nombre_empresa'],
            'sector_empresa' => $validated['sector_empresa'],
            'giro_empresa' => $validated['giro_empresa'],
            'ubicacion_empresa' => $validated['ubicacion_empresa'],
            'puesto_actual' => $validated['puesto_actual'],
            'area_departamento' => $validated['area_departamento'],
            'jefe_inmediato' => $validated['jefe_inmediato'],
            'contacto_jefe' => $validated['contacto_jefe'],
            
            // Sección V
            'promueve_pensamiento_critico' => $validated['promueve_pensamiento_critico'],
            'aspectos_valorados' => $validated['aspectos_valorados'],
            'sugerencias_plan_estudios' => $validated['sugerencias_plan_estudios'],
            'competencias_faltantes' => $validated['competencias_faltantes'],
            'calificacion_formacion' => $validated['calificacion_formacion'],
            'recomendaria_institucion' => $validated['recomendaria_institucion'],
            'razon_recomendacion' => $validated['razon_recomendacion'],
            'participacion_vinculacion' => $validated['participacion_vinculacion'],
            'tipo_vinculacion' => $validated['tipo_vinculacion'],
            'comentarios_adicionales' => $validated['comentarios_adicionales'],
            
            'estatus' => 'A',
        ]);

        return redirect()->back()->with('success', 'Cuestionario de Seguimiento enviado correctamente.');
    }
}
