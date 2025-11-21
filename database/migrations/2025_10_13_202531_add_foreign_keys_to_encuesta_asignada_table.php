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
        Schema::table('encuesta_asignada', function (Blueprint $table) {
            $table->foreign(['carrera_id'], 'encuesta_asignada_carrera_id_fk')->references(['id'])->on('carrera')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['ciclo_id'], 'encuesta_asignada_ciclo_id_fk')->references(['id'])->on('ciclo')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['encuesta_id'], 'encuesta_asignada_encuesta_id_fk')->references(['id'])->on('encuesta')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['generacion_id'], 'encuesta_asignada_generacion_id_fk')->references(['id'])->on('generacion')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['unidad_id'], 'encuesta_asignada_unidad_id_fk')->references(['id'])->on('unidad')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('encuesta_asignada', function (Blueprint $table) {
            $table->dropForeign('encuesta_asignada_carrera_id_fk');
            $table->dropForeign('encuesta_asignada_ciclo_id_fk');
            $table->dropForeign('encuesta_asignada_encuesta_id_fk');
            $table->dropForeign('encuesta_asignada_generacion_id_fk');
            $table->dropForeign('encuesta_asignada_unidad_id_fk');
        });
    }
};
