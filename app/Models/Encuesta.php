<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Encuesta extends Model
{
    protected $table = 'encuesta';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';
    const DELETED_AT = 'eliminado_en';

    protected $fillable = [
        'unidad_id',
        'carrera_id',
        'ciclo_id',
        'nombre',
        'tipo_cuestionario',
        'fecha_inicio',
        'fecha_fin',
        'descripcion',
        'instrucciones',
        'estatus',
    ];

    public function preguntas()
    {
        return $this->hasMany(Pregunta::class, 'encuesta_id');
    }

    public function asignaciones()
    {
        return $this->hasMany(EncuestaAsignada::class, 'encuesta_id');
    }
}
