<?php
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// Dashboard: público en local/DEBUG; protegido en otros entornos
$dashboardRoute = Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

if (!app()->environment('local') && !config('app.debug')) {
    $dashboardRoute->middleware(['auth', 'verified']);
}

// Rutas de módulos
$moduleRoutes = [
    'escritorio'          => 'modules/Escritorio',          // I-03
    'perfil-datos'        => 'modules/PerfilDatos',         // I-04
    'encuesta-preegreso'  => 'modules/EncuestaPreegreso',   // I-05
    'encuesta-egreso'     => 'modules/EncuestaEgreso',      // I-06
    'encuesta-laboral'    => 'modules/EncuestaLaboral',     // I-07
    'acuses-seguimiento'  => 'modules/AcusesSeguimiento',   // I-08
    'admin-general'       => 'modules/AdminGeneral',        // I-09
    'admin-academica'     => 'modules/AdminAcademica',      // I-10
    'admin-unidad'        => 'modules/AdminUnidad',         // I-11
    'reportes-informes'   => 'modules/ReportesInformes',    // I-12
];

foreach ($moduleRoutes as $uri => $component) {
    $r = Route::get($uri, fn () => Inertia::render($component))->name($uri);
    if (!app()->environment('local') && !config('app.debug')) {
        $r->middleware(['auth', 'verified']);
    }
}

require __DIR__.'/settings.php';
