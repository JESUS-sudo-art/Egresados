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
            $table->foreign(['dirigida_id'], 'encuesta_dirigida_id_fk')
                ->references(['id'])
                ->on('cat_dirigida')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
        
        Schema::table('pregunta', function (Blueprint $table) {
            $table->foreign(['subdimension_id'], 'pregunta_subdimension_id_fk')
                ->references(['id'])
                ->on('subdimension')
                ->onUpdate('cascade')
                ->onDelete('set null');
            
            $table->foreign(['pregunta_padre_id'], 'pregunta_pregunta_padre_id_fk')
                ->references(['id'])
                ->on('pregunta')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('encuesta', function (Blueprint $table) {
            $table->dropForeign('encuesta_dirigida_id_fk');
        });
        
        Schema::table('pregunta', function (Blueprint $table) {
            $table->dropForeign('pregunta_subdimension_id_fk');
            $table->dropForeign('pregunta_pregunta_padre_id_fk');
        });
    }
};
