<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Crear tabla estatus_estudiante si no existe
        if (!Schema::hasTable('estatus_estudiante')) {
            Schema::create('estatus_estudiante', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 50);
                $table->string('descripcion', 200)->nullable();
                $table->char('activo', 1)->default('I');
                $table->timestamps();
            });
            
            // Insertar catálogo de estatus
            DB::table('estatus_estudiante')->insert([
                ['id' => 1, 'nombre' => 'Activo', 'descripcion' => 'Estudiante activo en el sistema', 'activo' => 'I', 'created_at' => now()],
                ['id' => 2, 'nombre' => 'Inactivo', 'descripcion' => 'Estudiante inactivo', 'activo' => 'I', 'created_at' => now()],
                ['id' => 3, 'nombre' => 'Egresado', 'descripcion' => 'Estudiante egresado', 'activo' => 'I', 'created_at' => now()],
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('estatus_estudiante');
    }
};
