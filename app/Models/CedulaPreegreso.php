<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CedulaPreegreso extends Model
{
    protected $table = 'cedula_preegreso';
    
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';
    const DELETED_AT = 'eliminado_en';

    protected $fillable = [
        'egresado_id',
        'encuesta_id',
        'fecha_aplicacion',
        'percepcion_academica',
        'conocimiento_institucion',
        'telefono_contacto',
        'promedio',
        'observaciones',
        'estatus',
        'token',
    ];

    protected $casts = [
        'fecha_aplicacion' => 'date',
        'promedio' => 'decimal:2',
    ];

    public function egresado()
    {
        return $this->belongsTo(Egresado::class);
    }
}
