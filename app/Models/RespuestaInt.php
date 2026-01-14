<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RespuestaInt extends Model
{
    protected $table = 'respuesta_int';

    protected $fillable = [
        'bitacora_encuesta_id',
        'pregunta_id',
        'respuesta',
    ];

    /**
     * Relación con BitacoraEncuesta
     */
    public function bitacoraEncuesta(): BelongsTo
    {
        return $this->belongsTo(BitacoraEncuesta::class);
    }

    /**
     * Relación con Pregunta
     */
    public function pregunta(): BelongsTo
    {
        return $this->belongsTo(Pregunta::class);
    }
}
