<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Respuesta extends Model
{
    protected $table = 'respuesta';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null; // No tiene actualizado_en
    const DELETED_AT = null;

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
