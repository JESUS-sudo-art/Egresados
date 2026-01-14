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
        Schema::table('academico', function (Blueprint $table) {
            $table->foreign(['egresado_id'], 'academico_egresado_id_fk')
                ->references(['id'])
                ->on('egresado')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            
            $table->foreign(['unidad_id'], 'academico_unidad_id_fk')
                ->references(['id'])
                ->on('unidad')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            
            $table->foreign(['carrera_id'], 'academico_carrera_id_fk')
                ->references(['id'])
                ->on('carrera')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            
            $table->foreign(['generacion_id'], 'academico_generacion_id_fk')
                ->references(['id'])
                ->on('generacion')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academico', function (Blueprint $table) {
            $table->dropForeign('academico_egresado_id_fk');
            $table->dropForeign('academico_unidad_id_fk');
            $table->dropForeign('academico_carrera_id_fk');
            $table->dropForeign('academico_generacion_id_fk');
        });
    }
};
