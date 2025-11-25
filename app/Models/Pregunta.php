<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pregunta extends Model
{
    use SoftDeletes;
    protected $table = 'pregunta';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';
    const DELETED_AT = 'eliminado_en';
    public $timestamps = true;

    protected $fillable = [
        'encuesta_id',
        'texto',
        'dimension_id',
        'tipo_pregunta_id',
        'orden',
        'tamanio',
        'presentacion',
        'orientacion',
        'tips',
        'instruccion',
    ];

    public function encuesta()
    {
        return $this->belongsTo(Encuesta::class, 'encuesta_id');
    }

    public function opciones()
    {
        return $this->hasMany(Opcion::class, 'pregunta_id');
    }

    public function tipo()
    {
        return $this->belongsTo(TipoPregunta::class, 'tipo_pregunta_id');
    }

    public function dimension()
    {
        return $this->belongsTo(Dimension::class, 'dimension_id');
    }
}
