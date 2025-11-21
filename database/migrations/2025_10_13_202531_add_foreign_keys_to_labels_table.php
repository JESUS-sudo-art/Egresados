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
        Schema::table('labels', function (Blueprint $table) {
            $table->foreign(['actualizado_por_id'], 'labels_actualizado_por_id_fk')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['creado_por_id'], 'labels_creado_por_id_fk')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['eliminado_por_id'], 'labels_eliminado_por_id_fk')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('labels', function (Blueprint $table) {
            $table->dropForeign('labels_actualizado_por_id_fk');
            $table->dropForeign('labels_creado_por_id_fk');
            $table->dropForeign('labels_eliminado_por_id_fk');
        });
    }
};
