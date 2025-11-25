<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Carrera extends Model
{
    use SoftDeletes;
    protected $table = 'carrera';

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
