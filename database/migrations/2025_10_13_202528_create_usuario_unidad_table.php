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
        Schema::create('usuario_unidad', function (Blueprint $table) {
            $table->integer('id', true);
            $table->unsignedBigInteger('usuario_id')->index('usuario_unidad_usuario_id_fk');
            $table->integer('unidad_id')->index('usuario_unidad_unidad_id_fk');
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
        Schema::dropIfExists('usuario_unidad');
    }
};
