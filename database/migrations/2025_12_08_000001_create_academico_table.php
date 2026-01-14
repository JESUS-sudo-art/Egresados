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
        Schema::create('academico', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('egresado_id')->index('academico_egresado_id_fk');
            $table->integer('unidad_id')->index('academico_unidad_id_fk');
            $table->integer('carrera_id')->index('academico_carrera_id_fk');
            $table->integer('generacion_id')->index('academico_generacion_id_fk');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academico');
    }
};
