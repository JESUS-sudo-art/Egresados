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
        Schema::create('dimension', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->integer('orden')->nullable()->default(0);
            $table->integer('encuesta_id')->index('dimension_encuesta_id_fk');
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
        Schema::dropIfExists('dimension');
    }
};
