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
        Schema::create('cedula_preegreso', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('egresado_id')->index('cedula_preegreso_egresado_id_fk');
            $table->integer('encuesta_id')->nullable()->index('cedula_preegreso_encuesta_id_fk');
            $table->date('fecha_aplicacion')->nullable();
            $table->text('percepcion_academica')->nullable();
            $table->string('conocimiento_institucion')->nullable();
            $table->string('telefono_contacto', 20)->nullable();
            $table->decimal('promedio', 4)->nullable();
            $table->text('observaciones')->nullable();
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
        Schema::dropIfExists('cedula_preegreso');
    }
};
