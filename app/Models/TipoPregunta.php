<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoPregunta extends Model
{
    use SoftDeletes;
    protected $table = 'tipo_pregunta';

    protected $fillable = [
        'descripcion',
        'estatus',
    ];
}
