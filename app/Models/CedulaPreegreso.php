<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CedulaPreegreso extends Model
{
    use SoftDeletes;
    protected $table = 'cedula_preegreso';

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
