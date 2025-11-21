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
        Schema::create('encuesta', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('unidad_id')->nullable()->index('encuesta_unidad_id_fk');
            $table->integer('carrera_id')->nullable()->index('encuesta_carrera_id_fk');
            $table->integer('ciclo_id')->nullable()->index('encuesta_ciclo_id_fk');
            $table->string('nombre');
            $table->string('tipo_cuestionario', 100)->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->text('descripcion')->nullable();
            $table->text('instrucciones')->nullable();
            $table->char('estatus', 1)->default('A');
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
        Schema::dropIfExists('encuesta');
    }
};
