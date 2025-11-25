<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Laboral extends Model
{
    use SoftDeletes;
    protected $table = 'laboral';

    // Columnas personalizadas
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';
    const DELETED_AT = 'eliminado_en';
    public $timestamps = true;

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
