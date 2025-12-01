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
        'unidad_id',
        'estatus',
    ];

    // Relación directa con una unidad (nueva)
    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }

    // Relación many-to-many con unidades (legacy, mantener por compatibilidad)
    public function unidades()
    {
        return $this->belongsToMany(Unidad::class, 'unidad_carrera', 'carrera_id', 'unidad_id');
    }
}
