<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Opcion extends Model
{
    use SoftDeletes;
    protected $table = 'opcion';

    protected $fillable = [
        'pregunta_id',
        'texto',
        'valor',
        'orden',
    ];

    public function pregunta()
    {
        return $this->belongsTo(Pregunta::class, 'pregunta_id');
    }
}
