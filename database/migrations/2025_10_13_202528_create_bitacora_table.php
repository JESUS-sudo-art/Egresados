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
        Schema::create('bitacora', function (Blueprint $table) {
            $table->integer('id', true);
            $table->unsignedBigInteger('usuario_id')->index('bitacora_usuario_id_fk');
            $table->string('accion');
            $table->text('detalle')->nullable();
            $table->dateTime('fecha')->nullable()->useCurrent();
            $table->string('ip', 45)->nullable();
            $table->text('navegador')->nullable();
            $table->dateTime('creado_en')->nullable()->useCurrent();

            $table->foreign(['usuario_id'], 'bitacora_usuario_id_fk')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacora');
    }
};
