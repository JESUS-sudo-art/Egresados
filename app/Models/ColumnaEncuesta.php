<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ColumnaEncuesta extends Model
{
    protected $table = 'columna_encuesta';

    protected $fillable = [
        'encuesta_id',
        'valor',
        'orden',
        'campo',
        'columna',
    ];

    /**
     * Relación con Encuesta
     */
    public function encuesta(): BelongsTo
    {
        return $this->belongsTo(Encuesta::class);
    }
}
