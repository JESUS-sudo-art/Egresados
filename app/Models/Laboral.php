<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laboral extends Model
{
    protected $table = 'laboral';
    
    public $timestamps = false;

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
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
        'eliminado_en' => 'datetime',
    ];

    public function egresado()
    {
        return $this->belongsTo(Egresado::class, 'egresado_id');
    }
}
