<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsuarioUnidad extends Model
{
    use SoftDeletes;
    protected $table = 'usuario_unidad';

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
