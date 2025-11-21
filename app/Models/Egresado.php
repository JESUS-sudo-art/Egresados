<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Egresado extends Model
{
    protected $table = 'egresado';
    
    public $timestamps = false;

    protected $fillable = [
        'matricula',
        'curp',
        'nombre',
        'apellidos',
        'genero_id',
        'fecha_nacimiento',
        'lugar_nacimiento',
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
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'tiene_hijos' => 'boolean',
        'habla_lengua_indigena' => 'boolean',
        'habla_segundo_idioma' => 'boolean',
        'pertenece_grupo_etnico' => 'boolean',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
        'eliminado_en' => 'datetime',
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
}
