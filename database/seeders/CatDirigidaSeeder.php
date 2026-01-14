<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatDirigidaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dirigidas = [
            ['id' => 1, 'descripcion' => 'Todos', 'estatus' => 'A'],
            ['id' => 2, 'descripcion' => 'Escuelas', 'estatus' => 'A'],
            ['id' => 3, 'descripcion' => 'Carrera', 'estatus' => 'A'],
            ['id' => 4, 'descripcion' => 'Nivel de Estudios', 'estatus' => 'A'],
            ['id' => 5, 'descripcion' => 'Generación', 'estatus' => 'A'],
            ['id' => 6, 'descripcion' => 'Específica', 'estatus' => 'A'],
        ];

        foreach ($dirigidas as $dirigida) {
            DB::table('cat_dirigida')->updateOrInsert(
                ['id' => $dirigida['id']],
                $dirigida
            );
        }
    }
}
