<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unidad extends Model
{
    protected $table = 'unidad';
    
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';
    const DELETED_AT = 'eliminado_en';

    protected $fillable = [
        'nombre',
        'clave',
        'domicilio',
        'web',
        'email',
        'estatus',
    ];

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'usuario_unidad', 'unidad_id', 'usuario_id')
            ->withPivot('estatus')
            ->withTimestamps('creado_en', 'actualizado_en');
    }
}
