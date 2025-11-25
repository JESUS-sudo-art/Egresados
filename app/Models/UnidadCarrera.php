<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnidadCarrera extends Model
{
    use SoftDeletes;
    protected $table = 'unidad_carrera';

    protected $fillable = [
        'unidad_id',
        'carrera_id',
        'estatus',
    ];

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'carrera_id');
    }
}
