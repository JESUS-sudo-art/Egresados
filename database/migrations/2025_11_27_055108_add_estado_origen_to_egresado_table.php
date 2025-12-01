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
            $table->string('estado_origen', 100)->nullable()->after('lugar_nacimiento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('egresado', function (Blueprint $table) {
            $table->dropColumn('estado_origen');
        });
    }
};
