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
        // Lista de tablas con columnas de timestamps personalizadas
        $tables = [
            'egresado',
            'carrera',
            'encuesta',
            'pregunta',
            'opcion',
            'dimension',
            'unidad',
            'generacion',
            'ciclo',
            'tipo_pregunta',
            'laboral',
            'usuario_unidad',
            'unidad_carrera',
            'encuesta_laboral',
            'cedula_preegreso',
            'servicio',
            'labels',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    if (Schema::hasColumn($table->getTable(), 'creado_en')) {
                        $table->renameColumn('creado_en', 'created_at');
                    }
                    if (Schema::hasColumn($table->getTable(), 'actualizado_en')) {
                        $table->renameColumn('actualizado_en', 'updated_at');
                    }
                    if (Schema::hasColumn($table->getTable(), 'eliminado_en')) {
                        $table->renameColumn('eliminado_en', 'deleted_at');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir los cambios (de Laravel defaults a español)
        $tables = [
            'egresado',
            'carrera',
            'encuesta',
            'pregunta',
            'opcion',
            'dimension',
            'unidad',
            'generacion',
            'ciclo',
            'tipo_pregunta',
            'laboral',
            'usuario_unidad',
            'unidad_carrera',
            'encuesta_laboral',
            'cedula_preegreso',
            'servicio',
            'labels',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    if (Schema::hasColumn($table->getTable(), 'created_at')) {
                        $table->renameColumn('created_at', 'creado_en');
                    }
                    if (Schema::hasColumn($table->getTable(), 'updated_at')) {
                        $table->renameColumn('updated_at', 'actualizado_en');
                    }
                    if (Schema::hasColumn($table->getTable(), 'deleted_at')) {
                        $table->renameColumn('deleted_at', 'eliminado_en');
                    }
                });
            }
        }
    }
};
