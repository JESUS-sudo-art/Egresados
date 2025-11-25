<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Servicio extends Model
{
    use SoftDeletes;
    protected $table = 'servicio';

    protected $fillable = [
        'egresado_id',
        'dependencia',
        'area',
        'anio_inicio',
        'anio_fin',
    ];

    public function egresado()
    {
        return $this->belongsTo(Egresado::class, 'egresado_id');
    }
}
