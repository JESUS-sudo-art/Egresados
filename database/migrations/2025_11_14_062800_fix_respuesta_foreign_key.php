<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Deshabilitar foreign keys
        DB::statement('PRAGMA foreign_keys = OFF');

        // Crear nueva tabla con FK corregido
        DB::statement('DROP TABLE IF EXISTS respuesta_new');
        
        DB::statement("
            CREATE TABLE respuesta_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                egresado_id INTEGER NOT NULL,
                encuesta_id INTEGER NOT NULL,
                pregunta_id INTEGER NOT NULL,
                opcion_id INTEGER,
                respuesta_texto TEXT,
                respuesta_entero INTEGER,
                creado_en datetime,
                FOREIGN KEY (pregunta_id) REFERENCES pregunta(id),
                FOREIGN KEY (opcion_id) REFERENCES opcion(id),
                FOREIGN KEY (encuesta_id) REFERENCES encuesta(id),
                FOREIGN KEY (egresado_id) REFERENCES users(id)
            )
        ");

        // Copiar datos existentes
        DB::statement('INSERT INTO respuesta_new SELECT * FROM respuesta');

        // Reemplazar tabla vieja
        DB::statement('DROP TABLE respuesta');
        DB::statement('ALTER TABLE respuesta_new RENAME TO respuesta');

        // Reactivar foreign keys
        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down()
    {
        // No se puede revertir fácilmente
    }
};
