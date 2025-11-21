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
        Schema::table('pregunta', function (Blueprint $table) {
            $table->foreign(['dimension_id'], 'pregunta_dimension_id_fk')->references(['id'])->on('dimension')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['encuesta_id'], 'pregunta_encuesta_id_fk')->references(['id'])->on('encuesta')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['tipo_pregunta_id'], 'pregunta_tipo_pregunta_id_fk')->references(['id'])->on('tipo_pregunta')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pregunta', function (Blueprint $table) {
            $table->dropForeign('pregunta_dimension_id_fk');
            $table->dropForeign('pregunta_encuesta_id_fk');
            $table->dropForeign('pregunta_tipo_pregunta_id_fk');
        });
    }
};
