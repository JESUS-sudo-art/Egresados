<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Respuesta extends Model
{
    // No SoftDeletes: tabla no tiene columna eliminado_en / deleted_at
    protected $table = 'respuesta';

    protected $fillable = [
        'egresado_id',
        'encuesta_id',
        'pregunta_id',
        'opcion_id',
        'respuesta_texto',
        'respuesta_entero',
    ];

    public function egresado()
    {
        return $this->belongsTo(User::class, 'egresado_id');
    }

    public function encuesta()
    {
        return $this->belongsTo(Encuesta::class, 'encuesta_id');
    }

    public function pregunta()
    {
        return $this->belongsTo(Pregunta::class, 'pregunta_id');
    }

    public function opcion()
    {
        return $this->belongsTo(Opcion::class, 'opcion_id');
    }
}
