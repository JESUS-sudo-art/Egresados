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
        Schema::table('dimension', function (Blueprint $table) {
            $table->foreign(['encuesta_id'], 'dimension_encuesta_id_fk')->references(['id'])->on('encuesta')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dimension', function (Blueprint $table) {
            $table->dropForeign('dimension_encuesta_id_fk');
        });
    }
};
