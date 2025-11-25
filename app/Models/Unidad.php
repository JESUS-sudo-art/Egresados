<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unidad extends Model
{
    use SoftDeletes;
    protected $table = 'unidad';

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
            ->withTimestamps();
    }
}
