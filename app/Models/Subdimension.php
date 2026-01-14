<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subdimension extends Model
{
    protected $table = 'subdimension';

    protected $fillable = [
        'nombre',
        'descripcion',
        'orden',
        'dimension_id',
    ];

    /**
     * Relación con Dimensión
     */
    public function dimension(): BelongsTo
    {
        return $this->belongsTo(Dimension::class);
    }

    /**
     * Relación con Preguntas
     */
    public function preguntas(): HasMany
    {
        return $this->hasMany(Pregunta::class);
    }
}
