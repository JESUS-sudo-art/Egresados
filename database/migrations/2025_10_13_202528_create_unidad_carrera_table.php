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
        Schema::create('unidad_carrera', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('unidad_id')->index('unidad_carrera_unidad_id_fk');
            $table->integer('carrera_id')->index('unidad_carrera_carrera_id_fk');
            $table->char('estatus', 1)->default('A');
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
        Schema::dropIfExists('unidad_carrera');
    }
};
