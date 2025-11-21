<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioUnidad extends Model
{
    protected $table = 'usuario_unidad';
    
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';
    const DELETED_AT = 'eliminado_en';

    protected $fillable = [
        'usuario_id',
        'unidad_id',
        'estatus',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }
}
