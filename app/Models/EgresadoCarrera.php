<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class EgresadoCarrera extends Model
{

    protected $table = 'egresado_carrera';
    
    public $timestamps = false;

    protected $fillable = [
        'egresado_id',
        'carrera_id',
        'generacion_id',
        'fecha_ingreso',
        'fecha_egreso',
        'tipo_egreso',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'fecha_egreso' => 'date',
    ];

    public function egresado()
    {
        return $this->belongsTo(Egresado::class, 'egresado_id');
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'carrera_id');
    }

    public function generacion()
    {
        return $this->belongsTo(Generacion::class, 'generacion_id');
    }
}
