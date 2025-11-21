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
        Schema::table('laboral', function (Blueprint $table) {
            $table->foreign(['egresado_id'], 'laboral_egresado_id_fk')->references(['id'])->on('egresado')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laboral', function (Blueprint $table) {
            $table->dropForeign('laboral_egresado_id_fk');
        });
    }
};
