<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\File;

class ScriptsController extends Controller
{
    private array $scripts = [
        'assign_dimensions_encuesta_6.php' => 'Crea dimensiones base y asigna preguntas sin dimensión para encuesta 6',
        'edit_dimension.php' => 'Editar una dimensión existente (nombre, descripcion, orden)',
        'reassign_question_dimension.php' => 'Mover una pregunta a otra dimensión',
        'bulk_reassign_dimensions.php' => 'Redistribuir preguntas (auto) o aplicar CSV',
        'create_dimensions.php' => 'Crear una o varias dimensiones en una encuesta',
        'delete_dimension_move.php' => 'Eliminar una dimensión moviendo sus preguntas a otra o dejándolas sin dimensión',
    ];

    public function index(Request $request)
    {
        $basePath = base_path();
        $data = [];
        foreach ($this->scripts as $file => $desc) {
            $full = $basePath . DIRECTORY_SEPARATOR . $file;
            $data[] = [
                'name' => $file,
                'description' => $desc,
                'exists' => File::exists($full),
                'size' => File::exists($full) ? File::size($full) : 0,
                'content' => File::exists($full) ? File::get($full) : null,
                'examples' => $this->examplesFor($file),
            ];
        }

        return Inertia::render('modules/ScriptsTools', [
            'scripts' => $data,
        ]);
    }

    private function examplesFor(string $file): array
    {
        return match ($file) {
            'assign_dimensions_encuesta_6.php' => [
                'php assign_dimensions_encuesta_6.php'
            ],
            'edit_dimension.php' => [
                'php edit_dimension.php 2 nombre="Nuevo título" descripcion="Texto" orden=5'
            ],
            'reassign_question_dimension.php' => [
                'php reassign_question_dimension.php 12 3'
            ],
            'bulk_reassign_dimensions.php' => [
                'php bulk_reassign_dimensions.php 6 auto',
                'php bulk_reassign_dimensions.php 6 csv=mapa.csv'
            ],
            'create_dimensions.php' => [
                'php create_dimensions.php 6 nombre="Competencias" descripcion="Habilidades" orden=9',
                'php create_dimensions.php 6 multi="Datos|Info|10;Opinión||11"'
            ],
            'delete_dimension_move.php' => [
                'php delete_dimension_move.php 4 2',
                'php delete_dimension_move.php 3 null'
            ],
            default => [],
        };
    }
}
