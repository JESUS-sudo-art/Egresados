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
$dashboardRoute = Route::get('dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

if (!app()->environment('local') && !config('app.debug')) {
    $dashboardRoute->middleware(['auth', 'verified']);
}

// Rutas de módulos con protección por rol
// Encuesta de Egreso - Solo Egresados y Admin General
Route::get('encuesta-egreso', fn () => Inertia::render('modules/EncuestaEgreso'))
    ->name('encuesta-egreso')
    ->middleware(['auth', 'verified', 'role:Egresados,Administrador general']);

// Ruta de Admin Unidad con controlador - Admin Unidad y Admin General
$adminUnidadRoute = Route::get('admin-unidad', [App\Http\Controllers\AdminUnidadController::class, 'index'])
    ->name('admin-unidad')
    ->middleware(['auth', 'verified', 'role:Administrador de unidad,Administrador general']);

// Respaldo de base de datos - Admin Unidad, Admin Académico y Admin General
Route::get('admin-unidad/backup', [App\Http\Controllers\BackupController::class, 'download'])
    ->name('admin-unidad.backup')
    ->middleware(['auth', 'verified', 'permission:respaldos.descargar']);

// Rutas para contestar encuestas - permitir también Administrador general
Route::get('encuesta/{encuestaId}', [App\Http\Controllers\EncuestaController::class, 'show'])
    ->name('encuesta.show')
    ->middleware(['auth', 'verified', 'role:Administrador general,Estudiantes,Egresados']);
Route::post('encuesta/{encuestaId}/responder', [App\Http\Controllers\EncuestaController::class, 'store'])
    ->name('encuesta.responder')
    ->middleware(['auth', 'verified', 'can:respuestas.crear']);
Route::get('encuesta/{encuestaId}/mis-respuestas', [App\Http\Controllers\EncuestaController::class, 'misRespuestas'])
    ->name('encuesta.respuestas')
    ->middleware(['auth', 'verified', 'can:respuestas.ver']);

// Ver respuestas de un egresado específico (solo administración)
Route::get('encuesta/{encuestaId}/egresado/{egresadoId}/respuestas', [App\Http\Controllers\EncuestaController::class, 'respuestasDeEgresado'])
    ->name('encuesta.respuestas.egresado')
    ->middleware(['auth', 'verified', 'permission:egresados.ver_perfil']);

// Ruta de Admin Académica con controlador - Admin Académico y Admin General
$adminAcademicaRoute = Route::get('admin-academica', [App\Http\Controllers\AdminAcademicaController::class, 'index'])
    ->name('admin-academica')
    ->middleware(['auth', 'verified', 'role:Administrador academico,Administrador general']);

// Ruta de Reportes e Informes con controlador - Solo administradores autenticados
$reportesRoute = Route::get('reportes-informes', [App\Http\Controllers\ReportesInformesController::class, 'index'])
    ->name('reportes-informes')
    ->middleware(['auth', 'verified', 'role:Administrador general,Administrador de unidad,Administrador academico']);

// Vista pública de seguimiento para comunidad (sin cuenta)
Route::get('seguimiento-egresados', [App\Http\Controllers\ReportesInformesController::class, 'publico'])
    ->name('seguimiento-egresados');

// Ruta de Admin General con controlador - Solo Admin General
$adminGeneralRoute = Route::get('admin-general', [App\Http\Controllers\AdminGeneralController::class, 'index'])
    ->name('admin-general')
    ->middleware(['auth', 'verified', 'role:Administrador general']);

// Catálogo de Egresados - Solo Admin General
Route::get('catalogo-egresados', [App\Http\Controllers\EgresadoController::class, 'catalogo'])
    ->name('catalogo-egresados')
    ->middleware(['auth', 'verified', 'role:Administrador general']);

// Ver/Editar perfil de egresado - Solo Admin General
Route::get('egresados/{id}', [App\Http\Controllers\EgresadoController::class, 'show'])
    ->name('egresados.show')
    ->middleware(['auth', 'verified', 'permission:egresados.ver_perfil']);
Route::put('egresados/{id}', [App\Http\Controllers\EgresadoController::class, 'update'])
    ->name('egresados.update')
    ->middleware(['auth', 'verified', 'permission:egresados.editar']);
Route::put('egresados/{id}/password', [App\Http\Controllers\EgresadoController::class, 'updatePassword'])
    ->name('egresados.update-password')
    ->middleware(['auth', 'verified', 'permission:usuarios.cambiar_password']);
Route::post('egresados/{id}/carreras', [App\Http\Controllers\EgresadoController::class, 'updateCarrera'])
    ->name('egresados.add-carrera')
    ->middleware(['auth', 'verified', 'permission:egresados.asignar_carreras']);
Route::delete('egresados/{egresadoId}/carreras/{carreraId}', [App\Http\Controllers\EgresadoController::class, 'deleteCarrera'])
    ->name('egresados.delete-carrera')
    ->middleware(['auth', 'verified', 'permission:egresados.asignar_carreras']);

// Ruta de Acuses de Seguimiento con controlador - Estudiantes, Egresados y Admin General
$acusesRoute = Route::get('acuses-seguimiento', [App\Http\Controllers\AcusesSeguimientoController::class, 'index'])
    ->name('acuses-seguimiento')
    ->middleware(['auth', 'verified', 'role:Estudiantes,Egresados,Administrador general']);

// Ruta de Encuesta Pre-Egreso con controlador - Estudiantes, Egresados y Admin General
$preegresoRoute = Route::get('encuesta-preegreso', [App\Http\Controllers\CedulaPreegresoController::class, 'index'])
    ->name('encuesta-preegreso')
    ->middleware(['auth', 'verified', 'role:Estudiantes,Egresados,Administrador general']);

// Ruta de Encuesta Laboral con controlador - Solo Egresados y Admin General
$laboralRoute = Route::get('encuesta-laboral', [App\Http\Controllers\EncuestaLaboralController::class, 'index'])
    ->name('encuesta-laboral')
    ->middleware(['auth', 'verified', 'role:Egresados,Administrador general']);

// Ruta de Perfil y Datos con controlador - Solo Estudiantes y Egresados
$perfilRoute = Route::get('perfil-datos', [App\Http\Controllers\PerfilController::class, 'index'])
    ->name('perfil-datos')
    ->middleware(['auth', 'verified', 'role:Estudiantes,Egresados']);

// Rutas API para Perfil
Route::post('perfil/datos-personales', [App\Http\Controllers\PerfilController::class, 'updateDatosPersonales'])
    ->name('perfil.update-datos')
    ->middleware(['auth', 'verified', 'role:Estudiantes,Egresados']);
Route::post('perfil/empleos', [App\Http\Controllers\PerfilController::class, 'storeEmpleo'])
    ->name('perfil.store-empleo')
    ->middleware(['auth', 'verified', 'role:Estudiantes,Egresados']);
Route::put('perfil/empleos/{id}', [App\Http\Controllers\PerfilController::class, 'updateEmpleo'])
    ->name('perfil.update-empleo')
    ->middleware(['auth', 'verified', 'role:Estudiantes,Egresados']);
Route::delete('perfil/empleos/{id}', [App\Http\Controllers\PerfilController::class, 'deleteEmpleo'])
    ->name('perfil.delete-empleo')
    ->middleware(['auth', 'verified', 'role:Estudiantes,Egresados']);

// Rutas API para Cédula Pre-Egreso
Route::post('encuesta-preegreso/store', [App\Http\Controllers\CedulaPreegresoController::class, 'store'])->name('cedula-preegreso.store');

// Rutas API para Encuesta Laboral
Route::post('encuesta-laboral/store', [App\Http\Controllers\EncuestaLaboralController::class, 'store'])->name('encuesta-laboral.store');

// Rutas API para Acuses de Seguimiento
Route::get('acuses-seguimiento/descargar/{tipo}/{id}', [App\Http\Controllers\AcusesSeguimientoController::class, 'descargarAcuse'])
    ->middleware(['auth', 'verified', 'role:Estudiantes,Egresados,Administrador general'])
    ->name('acuses.descargar');

// Rutas API para Admin General
Route::post('admin-general', [App\Http\Controllers\AdminGeneralController::class, 'store'])->middleware(['auth', 'verified', 'permission:usuarios.crear'])->name('admin-general.store');
Route::put('admin-general/{id}', [App\Http\Controllers\AdminGeneralController::class, 'update'])->middleware(['auth', 'verified', 'permission:usuarios.editar'])->name('admin-general.update');
Route::delete('admin-general/{id}', [App\Http\Controllers\AdminGeneralController::class, 'destroy'])->middleware(['auth', 'verified', 'permission:usuarios.eliminar'])->name('admin-general.destroy');

// Rutas API para Admin Académica - Unidades
Route::post('admin-academica/unidades', [App\Http\Controllers\AdminAcademicaController::class, 'storeUnidad'])->middleware(['auth', 'verified', 'permission:unidades.crear'])->name('admin-academica.unidades.store');
Route::put('admin-academica/unidades/{id}', [App\Http\Controllers\AdminAcademicaController::class, 'updateUnidad'])->middleware(['auth', 'verified', 'permission:unidades.editar'])->name('admin-academica.unidades.update');
Route::delete('admin-academica/unidades/{id}', [App\Http\Controllers\AdminAcademicaController::class, 'destroyUnidad'])->middleware(['auth', 'verified', 'permission:unidades.eliminar'])->name('admin-academica.unidades.destroy');
Route::post('admin-academica/unidades/{id}/asignar-carreras', [App\Http\Controllers\AdminAcademicaController::class, 'asignarCarreras'])->middleware(['auth', 'verified', 'permission:unidades.asignar_carreras'])->name('admin-academica.unidades.asignar-carreras');

// Rutas API para Admin Académica - Carreras
Route::post('admin-academica/carreras', [App\Http\Controllers\AdminAcademicaController::class, 'storeCarrera'])->middleware(['auth', 'verified', 'permission:carreras.crear'])->name('admin-academica.carreras.store');
Route::put('admin-academica/carreras/{id}', [App\Http\Controllers\AdminAcademicaController::class, 'updateCarrera'])->middleware(['auth', 'verified', 'permission:carreras.editar'])->name('admin-academica.carreras.update');
Route::delete('admin-academica/carreras/{id}', [App\Http\Controllers\AdminAcademicaController::class, 'destroyCarrera'])->middleware(['auth', 'verified', 'permission:carreras.eliminar'])->name('admin-academica.carreras.destroy');

// Rutas API para Admin Académica - Generaciones
Route::post('admin-academica/generaciones', [App\Http\Controllers\AdminAcademicaController::class, 'storeGeneracion'])->middleware(['auth', 'verified', 'permission:generaciones.crear'])->name('admin-academica.generaciones.store');
Route::put('admin-academica/generaciones/{id}', [App\Http\Controllers\AdminAcademicaController::class, 'updateGeneracion'])->middleware(['auth', 'verified', 'permission:generaciones.editar'])->name('admin-academica.generaciones.update');
Route::delete('admin-academica/generaciones/{id}', [App\Http\Controllers\AdminAcademicaController::class, 'destroyGeneracion'])->middleware(['auth', 'verified', 'permission:generaciones.eliminar'])->name('admin-academica.generaciones.destroy');

// Niveles de Estudio
Route::post('admin-academica/niveles', [App\Http\Controllers\AdminAcademicaController::class, 'storeNivel'])->middleware(['auth', 'verified', 'permission:niveles.crear'])->name('admin-academica.niveles.store');
Route::put('admin-academica/niveles/{id}', [App\Http\Controllers\AdminAcademicaController::class, 'updateNivel'])->middleware(['auth', 'verified', 'permission:niveles.editar'])->name('admin-academica.niveles.update');
Route::delete('admin-academica/niveles/{id}', [App\Http\Controllers\AdminAcademicaController::class, 'destroyNivel'])->middleware(['auth', 'verified', 'permission:niveles.eliminar'])->name('admin-academica.niveles.destroy');

// Ciclos Escolares
Route::post('admin-academica/ciclos', [App\Http\Controllers\AdminAcademicaController::class, 'storeCiclo'])->middleware(['auth', 'verified', 'permission:ciclos.crear'])->name('admin-academica.ciclos.store');
Route::put('admin-academica/ciclos/{id}', [App\Http\Controllers\AdminAcademicaController::class, 'updateCiclo'])->middleware(['auth', 'verified', 'permission:ciclos.editar'])->name('admin-academica.ciclos.update');
Route::delete('admin-academica/ciclos/{id}', [App\Http\Controllers\AdminAcademicaController::class, 'destroyCiclo'])->middleware(['auth', 'verified', 'permission:ciclos.eliminar'])->name('admin-academica.ciclos.destroy');

// Rutas API para Admin Unidad - Encuestas
Route::post('admin-unidad/encuestas', [App\Http\Controllers\AdminUnidadController::class, 'storeEncuesta'])->middleware(['auth', 'verified', 'permission:encuestas.crear'])->name('admin-unidad.encuestas.store');
Route::put('admin-unidad/encuestas/{id}', [App\Http\Controllers\AdminUnidadController::class, 'updateEncuesta'])->middleware(['auth', 'verified', 'permission:encuestas.editar'])->name('admin-unidad.encuestas.update');
Route::delete('admin-unidad/encuestas/{id}', [App\Http\Controllers\AdminUnidadController::class, 'destroyEncuesta'])->middleware(['auth', 'verified', 'permission:encuestas.eliminar'])->name('admin-unidad.encuestas.destroy');

// Rutas API para Admin Unidad - Preguntas y Opciones
Route::get('admin-unidad/encuestas/{encuestaId}/preguntas', [App\Http\Controllers\PreguntaController::class, 'index'])->middleware(['auth', 'verified', 'permission:preguntas.ver'])->name('admin-unidad.preguntas.index');
Route::post('admin-unidad/encuestas/{encuestaId}/preguntas', [App\Http\Controllers\PreguntaController::class, 'store'])->middleware(['auth', 'verified', 'permission:preguntas.crear'])->name('admin-unidad.preguntas.store');
Route::put('admin-unidad/preguntas/{id}', [App\Http\Controllers\PreguntaController::class, 'update'])->middleware(['auth', 'verified', 'permission:preguntas.editar'])->name('admin-unidad.preguntas.update');
Route::delete('admin-unidad/preguntas/{id}', [App\Http\Controllers\PreguntaController::class, 'destroy'])->middleware(['auth', 'verified', 'permission:preguntas.eliminar'])->name('admin-unidad.preguntas.destroy');

Route::post('admin-unidad/preguntas/{preguntaId}/opciones', [App\Http\Controllers\PreguntaController::class, 'storeOpcion'])->middleware(['auth', 'verified', 'permission:preguntas.crear'])->name('admin-unidad.opciones.store');
Route::put('admin-unidad/opciones/{id}', [App\Http\Controllers\PreguntaController::class, 'updateOpcion'])->middleware(['auth', 'verified', 'permission:preguntas.editar'])->name('admin-unidad.opciones.update');
Route::delete('admin-unidad/opciones/{id}', [App\Http\Controllers\PreguntaController::class, 'destroyOpcion'])->middleware(['auth', 'verified', 'permission:preguntas.eliminar'])->name('admin-unidad.opciones.destroy');

// Rutas API para Admin Unidad - Dimensiones
Route::get('admin-unidad/encuestas/{encuestaId}/dimensiones', [App\Http\Controllers\DimensionController::class, 'index'])->middleware(['auth', 'verified', 'permission:dimensiones.ver'])->name('admin-unidad.dimensiones.index');
Route::post('admin-unidad/encuestas/{encuestaId}/dimensiones', [App\Http\Controllers\DimensionController::class, 'store'])->middleware(['auth', 'verified', 'permission:dimensiones.crear'])->name('admin-unidad.dimensiones.store');
Route::put('admin-unidad/dimensiones/{id}', [App\Http\Controllers\DimensionController::class, 'update'])->middleware(['auth', 'verified', 'permission:dimensiones.editar'])->name('admin-unidad.dimensiones.update');
Route::delete('admin-unidad/dimensiones/{id}', [App\Http\Controllers\DimensionController::class, 'destroy'])->middleware(['auth', 'verified', 'permission:dimensiones.eliminar'])->name('admin-unidad.dimensiones.destroy');

// Rutas API para Admin Unidad - Asignaciones
Route::post('admin-unidad/asignaciones', [App\Http\Controllers\AdminUnidadController::class, 'storeAsignacion'])->middleware(['auth', 'verified', 'permission:encuestas.asignar'])->name('admin-unidad.asignaciones.store');
Route::delete('admin-unidad/asignaciones/{id}', [App\Http\Controllers\AdminUnidadController::class, 'destroyAsignacion'])->middleware(['auth', 'verified', 'permission:encuestas.asignar'])->name('admin-unidad.asignaciones.destroy');

// Rutas API para Reportes e Informes
Route::get('reportes/datos', [App\Http\Controllers\ReportesInformesController::class, 'datos'])->middleware(['auth', 'verified', 'permission:reportes.ver'])->name('reportes.datos');
Route::get('reportes/exportar', [App\Http\Controllers\ReportesInformesController::class, 'exportar'])
    ->middleware(['auth', 'verified', 'permission:reportes.exportar'])
    ->name('reportes.exportar');

// Rutas de Gestión de Permisos y Roles (solo Admin General)
Route::middleware(['auth', 'verified', 'role:Administrador general'])->group(function () {
    Route::get('permisos', [App\Http\Controllers\PermissionController::class, 'index'])->name('permisos.index');
    Route::post('permisos/roles/{role}', [App\Http\Controllers\PermissionController::class, 'updateRolePermissions'])->name('permisos.update');
    Route::get('permisos/api/roles', [App\Http\Controllers\PermissionController::class, 'getRolesWithPermissions'])->name('permisos.api.roles');
    Route::post('permisos/asignar-rol', [App\Http\Controllers\PermissionController::class, 'assignRoleToUser'])->name('permisos.asignar-rol');
    
    // Gestión de Roles por Usuario
    Route::get('usuarios/roles', [App\Http\Controllers\UserRoleController::class, 'index'])->name('usuarios.roles.index');
    Route::post('usuarios/{user}/asignar-roles', [App\Http\Controllers\UserRoleController::class, 'assignRole'])->name('usuarios.asignar-roles');
    Route::post('usuarios/{user}/remover-rol', [App\Http\Controllers\UserRoleController::class, 'removeRole'])->name('usuarios.remover-rol');
    
    // Gestión de Roles (crear solo roles)
    Route::get('/roles', function() { return 'ROLES ROUTE WORKS'; })->name('roles.index.test');
});

require __DIR__.'/settings.php';

// Invitaciones Admin: solo Admin General
Route::middleware(['auth','verified','role:Administrador general'])->group(function(){
    Route::get('admin/invitations', [App\Http\Controllers\AdminInvitationController::class, 'index'])->name('admin.invitations.index');
    Route::post('admin/invitations', [App\Http\Controllers\AdminInvitationController::class, 'store'])->name('admin.invitations.store');
    Route::post('admin/invitations/{invitation}/resend', [App\Http\Controllers\AdminInvitationController::class, 'resend'])->name('admin.invitations.resend');
    Route::delete('admin/invitations/{invitation}', [App\Http\Controllers\AdminInvitationController::class, 'destroy'])->name('admin.invitations.destroy');
});

// Aceptar invitación pública (token)
Route::get('invitation/accept/{token}', [App\Http\Controllers\AcceptInvitationController::class, 'show'])->name('invitation.accept.show');
Route::post('invitation/accept/{token}', [App\Http\Controllers\AcceptInvitationController::class, 'store'])->name('invitation.accept.store');
