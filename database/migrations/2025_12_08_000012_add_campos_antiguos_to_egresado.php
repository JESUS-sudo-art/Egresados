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
            // Campos de la BD antigua que faltan
            $table->string('extension', 5)->nullable()->after('email');
            $table->char('activo', 1)->default('I')->after('estatus_id')->comment('A=Activo, I=Inactivo');
            $table->dateTime('fecha_ingreso')->nullable()->after('activo');
            $table->dateTime('ultimo_ingreso')->nullable()->after('fecha_ingreso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('egresado', function (Blueprint $table) {
            $table->dropColumn(['extension', 'activo', 'fecha_ingreso', 'ultimo_ingreso']);
        });
    }
};
