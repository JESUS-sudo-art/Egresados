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
        Schema::create('respuesta_txt', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('bitacora_encuesta_id')->index('respuesta_txt_bitacora_encuesta_id_fk');
            $table->integer('pregunta_id')->index('respuesta_txt_pregunta_id_fk');
            $table->text('respuesta')->nullable();
            $table->timestamps();
            
            // Índice compuesto para búsquedas rápidas
            $table->index(['bitacora_encuesta_id', 'pregunta_id'], 'respuesta_txt_bitacora_pregunta_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('respuesta_txt');
    }
};
