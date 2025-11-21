<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatGenero extends Model
{
    protected $table = 'cat_genero';
    
    public $timestamps = false;

    protected $fillable = ['nombre'];
}
