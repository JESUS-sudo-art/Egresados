<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresa';

    protected $fillable = [
        'nombre',
        'descripcion',
        'logo',
        'direccion',
        'web',
        'email',
        'telefonos',
        'estatus',
    ];
}
