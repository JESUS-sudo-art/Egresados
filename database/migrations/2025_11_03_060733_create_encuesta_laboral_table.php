<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('encuesta_laboral', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('egresado_id')->index();
            $table->date('fecha_aplicacion');
            
            // Sección I: Datos Generales
            $table->string('nombre_completo', 255);
            $table->string('genero', 50);
            $table->integer('edad');
            $table->string('curp', 18)->nullable();
            $table->string('telefono', 20);
            $table->string('email', 150);
            $table->integer('estado_civil_id')->nullable()->index();
            $table->string('residencia_actual', 255);
            $table->boolean('pertenece_grupo_etnico')->default(false);
            $table->string('cual_grupo_etnico', 100)->nullable();
            $table->boolean('habla_lengua_originaria')->default(false);
            $table->string('cual_lengua_originaria', 100)->nullable();
            $table->string('comunidad_diversa', 255)->nullable();
            $table->boolean('tiene_hijos')->default(false);
            $table->integer('num_hijos')->nullable();
            $table->integer('dependientes_economicos')->nullable();
            
            // Sección II: Trayectoria Académica
            $table->string('programa_academico', 255);
            $table->date('fecha_ingreso');
            $table->date('fecha_egreso');
            $table->boolean('realizo_practicas')->default(false);
            $table->text('descripcion_practicas')->nullable();
            $table->boolean('tiene_titulo')->default(false);
            $table->date('fecha_titulacion')->nullable();
            $table->boolean('estudios_posgrado')->default(false);
            $table->string('nivel_posgrado', 100)->nullable();
            $table->string('area_posgrado', 255)->nullable();
            $table->string('institucion_posgrado', 255)->nullable();
            $table->string('status_posgrado', 100)->nullable();
            $table->boolean('participo_movilidad')->default(false);
            $table->string('tipo_movilidad', 100)->nullable();
            $table->string('pais_movilidad', 255)->nullable();
            $table->string('duracion_movilidad', 100)->nullable();
            
            // Sección III: Inserción Laboral
            $table->boolean('trabaja_actualmente')->default(false);
            $table->string('motivo_no_trabaja', 255)->nullable();
            $table->string('tiempo_primer_empleo', 100)->nullable();
            $table->string('rango_salario', 100)->nullable();
            $table->boolean('relacion_carrera')->nullable();
            $table->string('tipo_contrato', 100)->nullable();
            $table->string('jornada_laboral', 100)->nullable();
            $table->string('medio_obtencion_empleo', 255)->nullable();
            $table->integer('cambios_empleo')->nullable();
            $table->string('satisfaccion_laboral', 50)->nullable();
            
            // Sección IV: Datos del Empleador
            $table->string('nombre_empresa', 255)->nullable();
            $table->string('sector_empresa', 50)->nullable();
            $table->string('giro_empresa', 255)->nullable();
            $table->string('ubicacion_empresa', 255)->nullable();
            $table->string('puesto_actual', 255)->nullable();
            $table->string('area_departamento', 255)->nullable();
            $table->string('jefe_inmediato', 255)->nullable();
            $table->string('contacto_jefe', 255)->nullable();
            
            // Sección V: Evaluación de la Formación
            $table->string('promueve_pensamiento_critico', 50);
            $table->text('aspectos_valorados')->nullable();
            $table->text('sugerencias_plan_estudios')->nullable();
            $table->text('competencias_faltantes')->nullable();
            $table->integer('calificacion_formacion');
            $table->boolean('recomendaria_institucion');
            $table->text('razon_recomendacion')->nullable();
            $table->boolean('participacion_vinculacion');
            $table->string('tipo_vinculacion', 255)->nullable();
            $table->text('comentarios_adicionales')->nullable();
            
            $table->char('estatus', 1)->default('A');
            $table->string('token')->nullable();
            $table->dateTime('creado_en')->nullable()->useCurrent();
            $table->dateTime('actualizado_en')->useCurrentOnUpdate()->nullable();
            $table->dateTime('eliminado_en')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encuesta_laboral');
    }
};
