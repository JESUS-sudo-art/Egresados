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
        Schema::create('encuesta_asignada', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('encuesta_id')->index('encuesta_asignada_encuesta_id_fk');
            $table->integer('unidad_id')->nullable()->index('encuesta_asignada_unidad_id_fk');
            $table->integer('carrera_id')->nullable()->index('encuesta_asignada_carrera_id_fk');
            $table->integer('generacion_id')->nullable()->index('encuesta_asignada_generacion_id_fk');
            $table->integer('ciclo_id')->nullable()->index('encuesta_asignada_ciclo_id_fk');
            $table->string('tipo_asignacion', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encuesta_asignada');
    }
};
