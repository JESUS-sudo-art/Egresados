<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatEstatus extends Model
{
    // Tabla sin columnas de soft delete ni timestamps personalizados
    protected $table = 'cat_estatus';
    public $timestamps = false;

    protected $fillable = ['nombre'];
}
