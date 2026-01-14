<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Academico extends Model
{
    protected $table = 'academico';

    protected $fillable = [
        'egresado_id',
        'unidad_id',
        'carrera_id',
        'generacion_id',
    ];

    /**
     * Relación con Egresado
     */
    public function egresado(): BelongsTo
    {
        return $this->belongsTo(Egresado::class);
    }

    /**
     * Relación con Unidad
     */
    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class);
    }

    /**
     * Relación con Carrera
     */
    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class);
    }

    /**
     * Relación con Generación
     */
    public function generacion(): BelongsTo
    {
        return $this->belongsTo(Generacion::class);
    }
}
