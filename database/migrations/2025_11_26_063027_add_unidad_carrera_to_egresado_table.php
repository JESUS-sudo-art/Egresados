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
            $table->integer('unidad_id')->nullable()->after('estatus_id');
            $table->integer('carrera_id')->nullable()->after('unidad_id');
            
            $table->foreign('unidad_id')->references('id')->on('unidad')->onDelete('set null');
            $table->foreign('carrera_id')->references('id')->on('carrera')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('egresado', function (Blueprint $table) {
            $table->dropForeign(['unidad_id']);
            $table->dropForeign(['carrera_id']);
            $table->dropColumn(['unidad_id', 'carrera_id']);
        });
    }
};
