<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BitacoraEgresado extends Model
{
    protected $table = 'bitacora_egresado';

    protected $fillable = [
        'egresado_id',
        'fecha_inicio',
        'fecha_fin',
        'ip',
        'navegador',
        'estatus',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    /**
     * Relación con Egresado
     */
    public function egresado(): BelongsTo
    {
        return $this->belongsTo(Egresado::class);
    }
}
