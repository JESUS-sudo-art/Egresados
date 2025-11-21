<?php

namespace App\Http\Controllers;

use App\Models\Egresado;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EgresadoController extends Controller
{
    public function catalogo(Request $request)
    {
        // Filtros básicos (se pueden ampliar luego)
        $search = trim($request->get('search', ''));
        $estatus = trim($request->get('estatus', ''));

        $query = Egresado::with(['estatus', 'carreras.carrera']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%$search%")
                  ->orWhere('apellidos', 'like', "%$search%")
                  ->orWhere('matricula', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        if ($estatus !== '') {
            $query->whereHas('estatus', fn($q) => $q->where('nombre', $estatus));
        }

        $egresados = $query->limit(500)->get()->map(function ($e) {
            return [
                'id' => $e->id,
                'matricula' => $e->matricula,
                'nombre' => $e->nombre,
                'apellidos' => $e->apellidos,
                'email' => $e->email,
                'estatus' => $e->estatus?->nombre ?? 'N/D',
                'carreras' => $e->carreras->map(fn($c) => $c->carrera?->nombre)->filter()->values(),
            ];
        });

        return Inertia::render('modules/CatalogoEgresados', [
            'egresados' => $egresados,
            'filters' => [
                'search' => $search,
                'estatus' => $estatus,
            ],
        ]);
    }
}
