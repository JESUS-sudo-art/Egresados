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
        Schema::table('pregunta', function (Blueprint $table) {
            // Campos de la BD antigua que faltan
            $table->integer('subdimension_id')->nullable()->after('dimension_id')->index('pregunta_subdimension_id_fk');
            $table->integer('pregunta_padre_id')->nullable()->after('orden')->index('pregunta_pregunta_padre_id_fk')->comment('ID de pregunta padre para preguntas anidadas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pregunta', function (Blueprint $table) {
            $table->dropColumn(['subdimension_id', 'pregunta_padre_id']);
        });
    }
};
