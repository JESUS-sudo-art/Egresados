<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoPregunta extends Model
{
    use SoftDeletes;
    protected $table = 'tipo_pregunta';

    // Columnas personalizadas
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';
    const DELETED_AT = 'eliminado_en';
    public $timestamps = true;

    protected $fillable = [
        'descripcion',
        'estatus',
    ];
}
