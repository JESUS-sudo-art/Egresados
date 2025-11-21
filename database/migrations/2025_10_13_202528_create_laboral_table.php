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
        Schema::create('laboral', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('egresado_id')->index('laboral_egresado_id_fk');
            $table->string('empresa');
            $table->string('puesto')->nullable();
            $table->string('sector', 100)->nullable();
            $table->boolean('actualmente_activo')->nullable()->default(false);
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
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
        Schema::dropIfExists('laboral');
    }
};
