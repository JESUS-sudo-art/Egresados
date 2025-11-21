<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Generacion extends Model
{
    protected $table = 'generacion';
    
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';
    const DELETED_AT = 'eliminado_en';

    protected $fillable = [
        'nombre',
        'estatus',
    ];
}
