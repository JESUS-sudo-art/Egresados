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
        Schema::table('cedula_preegreso', function (Blueprint $table) {
            // Edad del egresado al momento de aplicar la cédula
            // Usamos smallInteger sin signo para rangos 0-255 y permitimos NULL
            if (!Schema::hasColumn('cedula_preegreso', 'edad')) {
                $table->unsignedSmallInteger('edad')->nullable()->after('telefono_contacto');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cedula_preegreso', function (Blueprint $table) {
            if (Schema::hasColumn('cedula_preegreso', 'edad')) {
                $table->dropColumn('edad');
            }
        });
    }
};
