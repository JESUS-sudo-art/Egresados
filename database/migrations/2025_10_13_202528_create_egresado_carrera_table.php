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
        Schema::create('egresado_carrera', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('egresado_id')->index('egresado_carrera_egresado_id_fk');
            $table->integer('carrera_id')->index('egresado_carrera_carrera_id_fk');
            $table->integer('generacion_id')->nullable()->index('egresado_carrera_generacion_id_fk');
            $table->date('fecha_ingreso')->nullable();
            $table->date('fecha_egreso')->nullable();
            $table->char('tipo_egreso', 1)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egresado_carrera');
    }
};
