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
        Schema::create('egresado', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('matricula', 50)->nullable();
            $table->string('curp', 18)->nullable()->unique('curp_egresado_unique');
            $table->string('nombre', 150);
            $table->string('apellidos', 200);
            $table->integer('genero_id')->nullable()->index('egresado_genero_id_fk');
            $table->date('fecha_nacimiento')->nullable();
            $table->string('lugar_nacimiento')->nullable();
            $table->text('domicilio')->nullable()->comment('Domicilio de Origen');
            $table->text('domicilio_actual')->nullable();
            $table->string('email', 150)->unique('email_egresado_unique');
            $table->integer('estado_civil_id')->nullable()->index('egresado_estado_civil_id_fk');
            $table->boolean('tiene_hijos')->nullable();
            $table->boolean('habla_lengua_indigena')->nullable();
            $table->boolean('habla_segundo_idioma')->nullable();
            $table->boolean('pertenece_grupo_etnico')->nullable();
            $table->string('facebook_url')->nullable();
            $table->char('tipo_estudiante', 1)->nullable();
            $table->char('validado_sice', 1)->nullable()->default('N');
            $table->string('token')->nullable();
            $table->integer('estatus_id')->nullable()->index('egresado_estatus_id_fk');
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
        Schema::dropIfExists('egresado');
    }
};
