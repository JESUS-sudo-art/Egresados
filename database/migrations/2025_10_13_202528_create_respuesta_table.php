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
        Schema::create('respuesta', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('egresado_id')->index('respuesta_egresado_id_fk');
            $table->integer('encuesta_id')->index('respuesta_encuesta_id_fk');
            $table->integer('pregunta_id')->index('respuesta_pregunta_id_fk');
            $table->integer('opcion_id')->nullable()->index('respuesta_opcion_id_fk');
            $table->text('respuesta_texto')->nullable();
            $table->integer('respuesta_entero')->nullable();
            $table->dateTime('creado_en')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('respuesta');
    }
};
