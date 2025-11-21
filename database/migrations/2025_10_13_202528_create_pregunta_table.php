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
        Schema::create('pregunta', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('encuesta_id')->index('pregunta_encuesta_id_fk');
            $table->text('texto');
            $table->integer('dimension_id')->nullable()->index('pregunta_dimension_id_fk');
            $table->integer('tipo_pregunta_id')->index('pregunta_tipo_pregunta_id_fk');
            $table->integer('orden')->nullable()->default(0);
            $table->integer('tamanio')->nullable();
            $table->string('presentacion', 50)->nullable();
            $table->string('orientacion', 50)->nullable();
            $table->text('tips')->nullable();
            $table->text('instruccion')->nullable();
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
        Schema::dropIfExists('pregunta');
    }
};
