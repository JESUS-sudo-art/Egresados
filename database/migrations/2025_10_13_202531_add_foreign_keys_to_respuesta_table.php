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
        Schema::table('respuesta', function (Blueprint $table) {
            $table->foreign(['egresado_id'], 'respuesta_egresado_id_fk')->references(['id'])->on('egresado')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['encuesta_id'], 'respuesta_encuesta_id_fk')->references(['id'])->on('encuesta')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['opcion_id'], 'respuesta_opcion_id_fk')->references(['id'])->on('opcion')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['pregunta_id'], 'respuesta_pregunta_id_fk')->references(['id'])->on('pregunta')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('respuesta', function (Blueprint $table) {
            $table->dropForeign('respuesta_egresado_id_fk');
            $table->dropForeign('respuesta_encuesta_id_fk');
            $table->dropForeign('respuesta_opcion_id_fk');
            $table->dropForeign('respuesta_pregunta_id_fk');
        });
    }
};
