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
        Schema::table('egresado', function (Blueprint $table) {
            $table->foreign(['estado_civil_id'], 'egresado_estado_civil_id_fk')->references(['id'])->on('cat_estado_civil')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['estatus_id'], 'egresado_estatus_id_fk')->references(['id'])->on('cat_estatus')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['genero_id'], 'egresado_genero_id_fk')->references(['id'])->on('cat_genero')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('egresado', function (Blueprint $table) {
            $table->dropForeign('egresado_estado_civil_id_fk');
            $table->dropForeign('egresado_estatus_id_fk');
            $table->dropForeign('egresado_genero_id_fk');
        });
    }
};
