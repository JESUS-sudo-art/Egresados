<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unidad extends Model
{
    use SoftDeletes;
    protected $table = 'unidad';

    // Columnas personalizadas de timestamps y soft delete
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';
    const DELETED_AT = 'eliminado_en';
    public $timestamps = true;

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
