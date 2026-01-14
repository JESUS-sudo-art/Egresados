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
        Schema::table('encuesta', function (Blueprint $table) {
            // Campos de la BD antigua que faltan
            $table->string('nombre_corto', 30)->nullable()->after('nombre');
            $table->integer('dirigida_id')->nullable()->after('ciclo_id')->index('encuesta_dirigida_id_fk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('encuesta', function (Blueprint $table) {
            $table->dropColumn(['nombre_corto', 'dirigida_id']);
        });
    }
};
