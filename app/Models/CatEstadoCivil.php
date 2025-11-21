<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatEstadoCivil extends Model
{
    protected $table = 'cat_estado_civil';
    
    public $timestamps = false;

    protected $fillable = ['nombre'];
}
