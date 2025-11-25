<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dimension extends Model
{
    use SoftDeletes;
    protected $table = 'dimension';

    protected $fillable = [
        'nombre',
        'descripcion',
        'orden',
        'encuesta_id',
    ];

    public function encuesta()
    {
        return $this->belongsTo(Encuesta::class, 'encuesta_id');
    }

    public function preguntas()
    {
        return $this->hasMany(Pregunta::class, 'dimension_id');
    }
}
