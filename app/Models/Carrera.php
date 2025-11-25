<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Carrera extends Model
{
    use SoftDeletes;
    protected $table = 'carrera';
    
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';
    const DELETED_AT = 'eliminado_en';

    protected $fillable = [
        'nombre',
        'nivel',
        'tipo_programa',
        'estatus',
    ];

    public function unidades()
    {
        return $this->belongsToMany(Unidad::class, 'unidad_carrera', 'carrera_id', 'unidad_id');
    }
}
