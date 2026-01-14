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
        Schema::create('bitacora_encuesta', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('egresado_id')->index('bitacora_encuesta_egresado_id_fk');
            $table->unsignedBigInteger('ciclo_id')->nullable()->index('bitacora_encuesta_ciclo_id_fk');
            $table->integer('encuesta_id')->index('bitacora_encuesta_encuesta_id_fk');
            $table->dateTime('fecha_inicio')->nullable();
            $table->dateTime('fecha_fin')->nullable();
            $table->char('completada', 1)->default('N');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacora_encuesta');
    }
};
