<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ciclo extends Model
{
    use SoftDeletes;
    protected $table = 'ciclo';

    protected $fillable = [
        'nombre',
        'observaciones',
        'estatus',
    ];

    public function encuestas()
    {
        return $this->hasMany(Encuesta::class, 'ciclo_id');
    }

    public function encuestasAsignadas()
    {
        return $this->hasMany(EncuestaAsignada::class, 'ciclo_id');
    }
}
