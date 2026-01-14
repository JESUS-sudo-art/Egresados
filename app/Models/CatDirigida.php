<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatDirigida extends Model
{
    protected $table = 'cat_dirigida';

    protected $fillable = [
        'descripcion',
        'estatus',
    ];

    /**
     * Encuestas que usan este tipo de dirigida
     */
    public function encuestas()
    {
        return $this->hasMany(Encuesta::class, 'dirigida_id');
    }
}
