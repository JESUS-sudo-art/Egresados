<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BitacoraEncuesta extends Model
{
    protected $table = 'bitacora_encuesta';

    protected $fillable = [
        'egresado_id',
        'ciclo_id',
        'encuesta_id',
        'fecha_inicio',
        'fecha_fin',
        'completada',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    /**
     * Relación con Egresado
     */
    public function egresado(): BelongsTo
    {
        return $this->belongsTo(Egresado::class);
    }

    /**
     * Relación con Ciclo
     */
    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(Ciclo::class);
    }

    /**
     * Relación con Encuesta
     */
    public function encuesta(): BelongsTo
    {
        return $this->belongsTo(Encuesta::class);
    }

    /**
     * Relación con Respuestas Int
     */
    public function respuestasInt(): HasMany
    {
        return $this->hasMany(RespuestaInt::class);
    }

    /**
     * Relación con Respuestas Txt
     */
    public function respuestasTxt(): HasMany
    {
        return $this->hasMany(RespuestaTxt::class);
    }
}
