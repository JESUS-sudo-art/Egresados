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
        Schema::table('opcion', function (Blueprint $table) {
            $table->foreign(['pregunta_id'], 'opcion_pregunta_id_fk')->references(['id'])->on('pregunta')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opcion', function (Blueprint $table) {
            $table->dropForeign('opcion_pregunta_id_fk');
        });
    }
};
