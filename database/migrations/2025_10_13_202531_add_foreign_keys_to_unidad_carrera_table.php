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
        Schema::table('unidad_carrera', function (Blueprint $table) {
            $table->foreign(['carrera_id'], 'unidad_carrera_carrera_id_fk')->references(['id'])->on('carrera')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['unidad_id'], 'unidad_carrera_unidad_id_fk')->references(['id'])->on('unidad')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unidad_carrera', function (Blueprint $table) {
            $table->dropForeign('unidad_carrera_carrera_id_fk');
            $table->dropForeign('unidad_carrera_unidad_id_fk');
        });
    }
};
