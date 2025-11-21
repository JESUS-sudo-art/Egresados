<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EncuestaAsignada extends Model
{
    protected $table = 'encuesta_asignada';

    public $timestamps = false; // table has no timestamp columns

    protected $fillable = [
        'encuesta_id',
        'unidad_id',
        'carrera_id',
        'generacion_id',
        'ciclo_id',
        'tipo_asignacion',
    ];

    public function encuesta()
    {
        return $this->belongsTo(Encuesta::class, 'encuesta_id');
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'carrera_id');
    }

    public function generacion()
    {
        return $this->belongsTo(Generacion::class, 'generacion_id');
    }

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }
}
