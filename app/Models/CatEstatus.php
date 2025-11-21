<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatEstatus extends Model
{
    protected $table = 'cat_estatus';
    
    public $timestamps = false;

    protected $fillable = ['nombre'];
}
