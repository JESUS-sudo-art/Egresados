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
            $table->integer('anio_egreso')->nullable()->after('carrera_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('egresado', function (Blueprint $table) {
            $table->dropColumn('anio_egreso');
        });
    }
};
