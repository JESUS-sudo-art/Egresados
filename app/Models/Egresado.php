<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Egresado extends Model
{
    use SoftDeletes;
    protected $table = 'egresado';

    protected $fillable = [
        'matricula',
        'curp',
        'nombre',
        'apellidos',
        'genero_id',
        'fecha_nacimiento',
        'lugar_nacimiento',
        'estado_origen',
        'domicilio',
        'domicilio_actual',
        'email',
        'estado_civil_id',
        'tiene_hijos',
        'habla_lengua_indigena',
        'habla_segundo_idioma',
        'pertenece_grupo_etnico',
        'facebook_url',
        'tipo_estudiante',
        'validado_sice',
        'token',
        'estatus_id',
        'unidad_id',
        'carrera_id',
        'anio_egreso',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'tiene_hijos' => 'boolean',
        'habla_lengua_indigena' => 'boolean',
        'habla_segundo_idioma' => 'boolean',
        'pertenece_grupo_etnico' => 'boolean',
    ];

    public function genero()
    {
        return $this->belongsTo(CatGenero::class, 'genero_id');
    }

    public function estadoCivil()
    {
        return $this->belongsTo(CatEstadoCivil::class, 'estado_civil_id');
    }

    public function estatus()
    {
        return $this->belongsTo(CatEstatus::class, 'estatus_id');
    }

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'carrera_id');
    }

    public function carreras()
    {
        return $this->hasMany(EgresadoCarrera::class, 'egresado_id');
    }

    public function empleos()
    {
        return $this->hasMany(Laboral::class, 'egresado_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'email', 'email');
    }

    public function respuestas()
    {
        return $this->hasMany(Respuesta::class, 'egresado_id');
    }
}
