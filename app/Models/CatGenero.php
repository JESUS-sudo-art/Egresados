<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatGenero extends Model
{
    // Tabla sin columnas de soft delete ni timestamps
    protected $table = 'cat_genero';
    public $timestamps = false;
    protected $fillable = ['nombre'];
}
