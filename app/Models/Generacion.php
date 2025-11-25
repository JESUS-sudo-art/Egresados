<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Generacion extends Model
{
    use SoftDeletes;
    protected $table = 'generacion';

    protected $fillable = [
        'nombre',
        'estatus',
    ];
}
