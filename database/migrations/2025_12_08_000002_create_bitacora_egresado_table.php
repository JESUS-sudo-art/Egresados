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
        Schema::create('bitacora_egresado', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('egresado_id')->index('bitacora_egresado_egresado_id_fk');
            $table->dateTime('fecha_inicio')->nullable();
            $table->dateTime('fecha_fin')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('navegador', 150)->nullable();
            $table->char('estatus', 1)->default('A');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacora_egresado');
    }
};
