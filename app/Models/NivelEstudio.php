<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NivelEstudio extends Model
{
    use SoftDeletes;
    protected $table = 'nivel_estudio';

    protected $fillable = [
        'nombre',
        'estatus',
    ];
}
