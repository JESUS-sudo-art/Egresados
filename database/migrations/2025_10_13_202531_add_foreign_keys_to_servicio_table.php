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
        Schema::table('servicio', function (Blueprint $table) {
            $table->foreign(['egresado_id'], 'servicio_egresado_id_fk')->references(['id'])->on('egresado')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servicio', function (Blueprint $table) {
            $table->dropForeign('servicio_egresado_id_fk');
        });
    }
};
