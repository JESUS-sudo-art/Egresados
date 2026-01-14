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
        // bitacora_egresado_egresado_id_fk ya existe, no se intenta crear nuevamente
        
        Schema::table('bitacora_encuesta', function (Blueprint $table) {
            // bitacora_encuesta_egresado_id_fk ya existe, solo agregamos los faltantes
            
            $table->foreign(['ciclo_id'], 'bitacora_encuesta_ciclo_id_fk')
                ->references(['id'])
                ->on('ciclo_escolar')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            
            $table->foreign(['encuesta_id'], 'bitacora_encuesta_encuesta_id_fk')
                ->references(['id'])
                ->on('encuesta')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
        
        Schema::table('respuesta_int', function (Blueprint $table) {
            $table->foreign(['bitacora_encuesta_id'], 'respuesta_int_bitacora_encuesta_id_fk')
                ->references(['id'])
                ->on('bitacora_encuesta')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            
            $table->foreign(['pregunta_id'], 'respuesta_int_pregunta_id_fk')
                ->references(['id'])
                ->on('pregunta')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
        
        Schema::table('respuesta_txt', function (Blueprint $table) {
            $table->foreign(['bitacora_encuesta_id'], 'respuesta_txt_bitacora_encuesta_id_fk')
                ->references(['id'])
                ->on('bitacora_encuesta')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            
            $table->foreign(['pregunta_id'], 'respuesta_txt_pregunta_id_fk')
                ->references(['id'])
                ->on('pregunta')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
        
        Schema::table('subdimension', function (Blueprint $table) {
            $table->foreign(['dimension_id'], 'subdimension_dimension_id_fk')
                ->references(['id'])
                ->on('dimension')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
        
        Schema::table('columna_encuesta', function (Blueprint $table) {
            $table->foreign(['encuesta_id'], 'columna_encuesta_encuesta_id_fk')
                ->references(['id'])
                ->on('encuesta')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No se intenta eliminar bitacora_egresado_egresado_id_fk porque se creó en otra migración
        
        Schema::table('bitacora_encuesta', function (Blueprint $table) {
            // No eliminamos bitacora_encuesta_egresado_id_fk porque se creó en otra migración
            $table->dropForeign('bitacora_encuesta_ciclo_id_fk');
            $table->dropForeign('bitacora_encuesta_encuesta_id_fk');
        });
        
        Schema::table('respuesta_int', function (Blueprint $table) {
            $table->dropForeign('respuesta_int_bitacora_encuesta_id_fk');
            $table->dropForeign('respuesta_int_pregunta_id_fk');
        });
        
        Schema::table('respuesta_txt', function (Blueprint $table) {
            $table->dropForeign('respuesta_txt_bitacora_encuesta_id_fk');
            $table->dropForeign('respuesta_txt_pregunta_id_fk');
        });
        
        Schema::table('subdimension', function (Blueprint $table) {
            $table->dropForeign('subdimension_dimension_id_fk');
        });
        
        Schema::table('columna_encuesta', function (Blueprint $table) {
            $table->dropForeign('columna_encuesta_encuesta_id_fk');
        });
    }
};
