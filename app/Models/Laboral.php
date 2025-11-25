<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Laboral extends Model
{
    use SoftDeletes;
    protected $table = 'laboral';

    protected $fillable = [
        'egresado_id',
        'empresa',
        'puesto',
        'sector',
        'actualmente_activo',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'actualmente_activo' => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function egresado()
    {
        return $this->belongsTo(Egresado::class, 'egresado_id');
    }
}
