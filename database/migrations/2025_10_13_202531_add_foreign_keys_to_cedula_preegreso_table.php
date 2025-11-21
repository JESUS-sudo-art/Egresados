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
        Schema::table('cedula_preegreso', function (Blueprint $table) {
            $table->foreign(['egresado_id'], 'cedula_preegreso_egresado_id_fk')->references(['id'])->on('egresado')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['encuesta_id'], 'cedula_preegreso_encuesta_id_fk')->references(['id'])->on('encuesta')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cedula_preegreso', function (Blueprint $table) {
            $table->dropForeign('cedula_preegreso_egresado_id_fk');
            $table->dropForeign('cedula_preegreso_encuesta_id_fk');
        });
    }
};
