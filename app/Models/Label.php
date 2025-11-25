<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Label extends Model
{
    use SoftDeletes;
    protected $table = 'labels';

    protected $fillable = [
        'codigo',
        'descripcion',
        'orden',
        'status',
        'creado_por_id',
        'actualizado_por_id',
        'eliminado_por_id',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por_id');
    }

    public function actualizadoPor()
    {
        return $this->belongsTo(User::class, 'actualizado_por_id');
    }

    public function eliminadoPor()
    {
        return $this->belongsTo(User::class, 'eliminado_por_id');
    }
}
