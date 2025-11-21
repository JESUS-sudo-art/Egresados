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
        Schema::table('usuario_unidad', function (Blueprint $table) {
            $table->foreign(['unidad_id'], 'usuario_unidad_unidad_id_fk')->references(['id'])->on('unidad')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['usuario_id'], 'usuario_unidad_usuario_id_fk')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuario_unidad', function (Blueprint $table) {
            $table->dropForeign('usuario_unidad_unidad_id_fk');
            $table->dropForeign('usuario_unidad_usuario_id_fk');
        });
    }
};
