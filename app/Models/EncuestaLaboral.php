<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EncuestaLaboral extends Model
{
    use SoftDeletes;
    protected $table = 'encuesta_laboral';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';
    const DELETED_AT = 'eliminado_en';
    public $timestamps = true;

    protected $fillable = [
        'egresado_id',
        'fecha_aplicacion',
        
        // Sección I: Datos Generales
        'nombre_completo',
        'genero',
        'edad',
        'curp',
        'telefono',
        'email',
        'estado_civil_id',
        'residencia_actual',
        'pertenece_grupo_etnico',
        'cual_grupo_etnico',
        'habla_lengua_originaria',
        'cual_lengua_originaria',
        'comunidad_diversa',
        'tiene_hijos',
        'num_hijos',
        'dependientes_economicos',
        
        // Sección II: Trayectoria Académica
        'programa_academico',
        'fecha_ingreso',
        'fecha_egreso',
        'realizo_practicas',
        'descripcion_practicas',
        'tiene_titulo',
        'fecha_titulacion',
        'estudios_posgrado',
        'nivel_posgrado',
        'area_posgrado',
        'institucion_posgrado',
        'status_posgrado',
        'participo_movilidad',
        'tipo_movilidad',
        'pais_movilidad',
        'duracion_movilidad',
        
        // Sección III: Inserción Laboral
        'trabaja_actualmente',
        'motivo_no_trabaja',
        'tiempo_primer_empleo',
        'rango_salario',
        'relacion_carrera',
        'tipo_contrato',
        'jornada_laboral',
        'medio_obtencion_empleo',
        'cambios_empleo',
        'satisfaccion_laboral',
        
        // Sección IV: Datos del Empleador
        'nombre_empresa',
        'sector_empresa',
        'giro_empresa',
        'ubicacion_empresa',
        'puesto_actual',
        'area_departamento',
        'jefe_inmediato',
        'contacto_jefe',
        
        // Sección V: Evaluación de la Formación
        'promueve_pensamiento_critico',
        'aspectos_valorados',
        'sugerencias_plan_estudios',
        'competencias_faltantes',
        'calificacion_formacion',
        'recomendaria_institucion',
        'razon_recomendacion',
        'participacion_vinculacion',
        'tipo_vinculacion',
        'comentarios_adicionales',
        
        'estatus',
        'token',
    ];

    protected $casts = [
        'fecha_aplicacion' => 'date',
        'fecha_ingreso' => 'date',
        'fecha_egreso' => 'date',
        'fecha_titulacion' => 'date',
        'edad' => 'integer',
        'cambios_empleo' => 'integer',
        'calificacion_formacion' => 'integer',
        'pertenece_grupo_etnico' => 'boolean',
        'habla_lengua_originaria' => 'boolean',
        'tiene_hijos' => 'boolean',
        'num_hijos' => 'integer',
        'dependientes_economicos' => 'integer',
        'realizo_practicas' => 'boolean',
        'tiene_titulo' => 'boolean',
        'estudios_posgrado' => 'boolean',
        'participo_movilidad' => 'boolean',
        'trabaja_actualmente' => 'boolean',
        'relacion_carrera' => 'boolean',
        'recomendaria_institucion' => 'boolean',
        'participacion_vinculacion' => 'boolean',
    ];

    public function egresado()
    {
        return $this->belongsTo(Egresado::class);
    }
    
    public function estadoCivil()
    {
        return $this->belongsTo(CatEstadoCivil::class, 'estado_civil_id');
    }
}
